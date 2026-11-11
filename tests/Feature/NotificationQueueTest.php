<?php

namespace Tests\Feature;

use App\DTOs\Notification\NotificationEventData;
use App\Enums\NotificationDeliveryStatus as Status;
use App\Enums\NotificationType;
use App\Jobs\DeliverNotificationJob;
use App\Models\NotificationDelivery;
use App\Repositories\Eloquent\NotificationDeliveryRepository;
use App\Repositories\Eloquent\NotificationRecipientRepository;
use App\Repositories\Interfaces\NotificationDeliveryRepositoryInterface;
use App\Repositories\Interfaces\NotificationRecipientRepositoryInterface;
use App\Services\Shared\Notification\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/** Real commits on the pre-migrated isolated test database; intentionally no wrapping test transaction. */
class NotificationQueueTest extends TestCase
{
    private string $prefix;

    private string $queueName;

    private bool $safeDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('licuti_cms_test', DB::connection()->getDatabaseName());
        $this->assertSame(0, DB::transactionLevel());
        $this->prefix = 'notification-test:'.Str::uuid().':';
        $this->queueName = 'notification-test-'.Str::uuid();
        $this->safeDatabase = true;
        $this->app->bind(NotificationDeliveryRepositoryInterface::class, NotificationDeliveryRepository::class);
        $this->app->bind(NotificationRecipientRepositoryInterface::class, NotificationRecipientRepository::class);
        config([
            'queue.default' => 'sync', 'mail.default' => 'array',
            'notification.connection' => null, 'notification.queue' => $this->queueName,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->safeDatabase) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            DB::table('jobs')->where('queue', $this->queueName)->delete();
            DB::table('notification_deliveries')->where('event_key', 'like', $this->prefix.'%')->delete();
        }
        parent::tearDown();
    }

    public function test_outer_rollback_discards_intent_and_never_enqueues_or_sends(): void
    {
        $mailCalls = 0;
        Event::listen(MessageSending::class, function () use (&$mailCalls) {
            $mailCalls++;
        });
        DB::beginTransaction();
        DB::beginTransaction();
        app(NotificationService::class)->record($this->event());
        DB::commit();
        $this->assertSame(1, $this->deliveries()->count());
        $this->assertSame(0, $this->queuedCount());
        DB::rollBack();
        $this->assertSame(0, $this->deliveries()->count());
        $this->assertSame(0, $this->queuedCount());
        $this->assertSame(0, $mailCalls);
    }

    public function test_enqueue_waits_for_outermost_commit_and_sync_default_cannot_leak_smtp(): void
    {
        $mailCalls = 0;
        Event::listen(MessageSending::class, function () use (&$mailCalls) {
            $mailCalls++;
        });
        DB::beginTransaction();
        DB::beginTransaction();
        app(NotificationService::class)->record($this->event());
        DB::commit();
        $this->assertSame(0, $this->queuedCount());
        DB::commit();
        $this->assertSame(1, $this->queuedCount());
        $this->assertSame(0, $mailCalls);
        $this->assertSame(Status::PENDING, $this->deliveries()->sole()->status);
        $payload = json_decode(DB::table('jobs')->where('queue', $this->queueName)->value('payload'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(DeliverNotificationJob::class, $payload['displayName']);
        $this->assertSame(30, $payload['timeout']);
        $this->assertSame('sync', config('queue.default'));
    }

    public function test_enqueue_failure_keeps_committed_intent_and_recovers_after_cooldown(): void
    {
        $dispatcher = Bus::getFacadeRoot();
        Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('Queue temporarily unavailable'));
        try {
            app(NotificationService::class)->record($this->event());
        } finally {
            Bus::swap($dispatcher);
        }
        $delivery = $this->deliveries()->sole();
        $this->assertSame(Status::PENDING, $delivery->status);
        $this->assertNotNull($delivery->last_enqueued_at);
        $this->assertSame(0, $this->queuedCount());
        $this->assertSame(0, app(NotificationService::class)->recover());
        $this->travel(301)->seconds();
        $this->assertSame(1, app(NotificationService::class)->recover());
        $this->assertSame(1, $this->queuedCount());
        $this->assertSame(0, app(NotificationService::class)->recover());
    }

    public function test_committed_intent_without_callback_is_recovered_with_bounded_enqueues(): void
    {
        config(['notification.recovery_batch' => 2]);
        for ($index = 0; $index < 5; $index++) {
            $this->insertUnqueuedIntent();
        }
        $this->assertSame(2, app(NotificationService::class)->recover(1000));
        $this->assertSame(2, $this->queuedCount());
        $this->assertSame(2, app(NotificationService::class)->recover());
        $this->assertSame(1, app(NotificationService::class)->recover());
        $this->assertSame(0, app(NotificationService::class)->recover());
        $this->assertSame(5, $this->queuedCount());
    }

    public function test_explicit_sync_connection_is_rejected_without_inline_sending(): void
    {
        config(['notification.connection' => 'sync']);
        app(NotificationService::class)->record($this->event());
        $this->assertSame(Status::PENDING, $this->deliveries()->sole()->status);
        $this->assertSame(0, $this->queuedCount());
        $this->assertSame(0, $this->deliveries()->sole()->attempts);
    }

    public function test_failed_and_skipped_intents_are_never_reenqueued(): void
    {
        foreach ([Status::FAILED, Status::SKIPPED, Status::SENT] as $status) {
            $delivery = $this->insertUnqueuedIntent();
            $delivery->update(['status' => $status, 'finished_at' => now()->subDay()]);
        }
        $this->assertSame(0, app(NotificationService::class)->recover());
        $this->assertSame(0, $this->queuedCount());
    }

    public function test_commands_expose_bounded_recovery_and_retention(): void
    {
        $this->insertUnqueuedIntent();
        $this->artisan('notifications:recover', ['--limit' => 1])->assertSuccessful();
        $this->assertSame(1, $this->queuedCount());
        $this->artisan('notifications:recover', ['--limit' => 'bad'])->assertExitCode(2);
        // Purge functionality is tested transactionally in NotificationDeliveryTest;
        // avoid deleting unrelated fixtures from this real-commit test class.
    }

    private function deliveries(): Builder
    {
        return NotificationDelivery::query()->where('event_key', 'like', $this->prefix.'%');
    }

    private function queuedCount(): int
    {
        return DB::table('jobs')->where('queue', $this->queueName)->count();
    }

    private function event(): NotificationEventData
    {
        $uuid = (string) Str::uuid();

        return new NotificationEventData(
            NotificationType::ORDER_PLACED, $this->prefix.$uuid, now()->toISOString(),
            mailPayload: ['subject' => 'Đơn hàng đã tạo', 'lines' => ['Ghi nhận sự kiện tạo đơn.']],
            mailRecipient: 'guest@example.test', mailRecipientKey: 'order-customer:'.$uuid,
        );
    }

    private function insertUnqueuedIntent(): NotificationDelivery
    {
        $event = $this->event();

        return app(NotificationDeliveryRepositoryInterface::class)->insert([
            'id' => (string) Str::uuid(), 'event_key' => $event->eventKey,
            'recipient_key' => $event->mailRecipientKey, 'event_type' => $event->type,
            'channel' => 'mail', 'status' => Status::PENDING, 'available_at' => now(),
            'payload' => ['email' => $event->mailRecipient, 'subject' => 'Đơn hàng', 'lines' => [], 'occurred_at' => $event->occurredAt],
        ]);
    }
}
