<?php

namespace Tests\Feature;

use App\Core\Enums\UserStatus;
use App\DTOs\Notification\NotificationEventData;
use App\Enums\NotificationDeliveryStatus as Status;
use App\Enums\NotificationType;
use App\Jobs\DeliverNotificationJob;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Notifications\SnapshotNotification;
use App\Repositories\Eloquent\NotificationDeliveryRepository;
use App\Repositories\Eloquent\NotificationRecipientRepository;
use App\Repositories\Interfaces\NotificationDeliveryRepositoryInterface;
use App\Repositories\Interfaces\NotificationRecipientRepositoryInterface;
use App\Services\Shared\Notification\NotificationAccessService;
use App\Services\Shared\Notification\NotificationDeliveryService;
use App\Services\Shared\Notification\NotificationMailManager;
use App\Services\Shared\Notification\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('licuti_cms_test', DB::connection()->getDatabaseName());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->bind(NotificationDeliveryRepositoryInterface::class, NotificationDeliveryRepository::class);
        $this->app->bind(NotificationRecipientRepositoryInterface::class, NotificationRecipientRepository::class);
        config(['mail.default' => 'array', 'notification.mailer' => null]);
        Bus::fake([DeliverNotificationJob::class]);
    }

    public function test_replay_is_unique_per_recipient_and_channel_and_snapshots_are_separate(): void
    {
        $admin = User::factory()->admin()->create();
        $event = $this->event();
        app(NotificationService::class)->record($event);
        app(NotificationService::class)->record($event);
        $this->assertSame(2, NotificationDelivery::query()->count());
        $database = NotificationDelivery::query()->where('channel', 'database')->firstOrFail();
        $mail = NotificationDelivery::query()->where('channel', 'mail')->firstOrFail();
        $this->assertSame($admin->id, $database->recipient_user_id);
        $this->assertSame($event->type->value, $database->payload['type']);
        $this->assertSame($event->occurredAt, $database->payload['occurred_at']);
        $this->assertArrayNotHasKey('internal_note', $database->payload);
        $this->assertArrayNotHasKey('target_uuid', $mail->payload);
        $this->assertArrayNotHasKey('internal_note', $mail->payload);
        $this->assertSame('order-customer:'.Str::after($event->eventKey, 'order.placed:'), $mail->recipient_key);
    }

    public function test_invalid_email_is_a_terminal_deduplicated_intent(): void
    {
        $event = $this->event('invalid');
        app(NotificationService::class)->record($event);
        $delivery = NotificationDelivery::query()->sole();
        $this->assertSame(Status::SKIPPED, $delivery->status);
        $this->assertSame('invalid_email', $delivery->last_error_code);
        $this->assertNotNull($delivery->finished_at);
        $corrected = new NotificationEventData($event->type, $event->eventKey, $event->occurredAt,
            mailPayload: $event->mailPayload, mailRecipient: 'valid@example.test', mailRecipientKey: $event->mailRecipientKey);
        app(NotificationService::class)->record($corrected);
        $this->assertSame(1, NotificationDelivery::query()->count());
        $this->assertSame(Status::SKIPPED, $delivery->fresh()->status);
        Bus::assertNotDispatched(DeliverNotificationJob::class);
    }

    public function test_insert_does_not_swallow_foreign_key_or_other_query_errors(): void
    {
        $this->expectException(QueryException::class);
        app(NotificationDeliveryRepositoryInterface::class)->insert([
            'id' => (string) Str::uuid(), 'event_key' => 'test:'.Str::uuid(), 'recipient_key' => 'user:missing',
            'recipient_user_id' => PHP_INT_MAX, 'channel' => 'database', 'event_type' => NotificationType::ORDER_PLACED,
        ]);
    }

    public function test_access_refreshes_direct_role_and_active_state_without_spatie_cache(): void
    {
        $adminAccess = Permission::findOrCreate('admin.access', 'web');
        $orders = Permission::findOrCreate('orders.view', 'web');
        $role = Role::findOrCreate('notification-test-role', 'web');
        $role->givePermissionTo([$adminAccess, $orders]);
        $user = User::factory()->active()->create();
        $user->assignRole($role);
        $user->load(['roles.permissions', 'permissions']);
        $access = app(NotificationAccessService::class);
        $this->assertTrue($access->canReceive($user, NotificationType::ORDER_PLACED));
        // Bypass Spatie mutation APIs deliberately: stale process caches must not grant access.
        DB::table('role_has_permissions')->where('role_id', $role->id)->where('permission_id', $orders->id)->delete();
        $this->assertFalse($access->canReceive($user, NotificationType::ORDER_PLACED));
        $user->givePermissionTo($orders);
        $this->assertTrue($access->canReceive($user, NotificationType::ORDER_PLACED));
        DB::table('model_has_permissions')->where('model_id', $user->id)->where('permission_id', $orders->id)->delete();
        $this->assertFalse($access->canReceive($user, NotificationType::ORDER_PLACED));
        User::query()->whereKey($user->id)->update(['is_admin' => true]);
        $this->assertTrue($access->canReceive($user, NotificationType::PAYMENT_SUCCEEDED));
        User::query()->whereKey($user->id)->update(['status' => UserStatus::BANNED]);
        $this->assertSame([], $access->allowedTypes($user));
    }

    public function test_eligible_iteration_eager_loads_permissions_without_per_user_queries(): void
    {
        $role = Role::findOrCreate('notification-batch-role', 'web');
        $role->givePermissionTo([
            Permission::findOrCreate('admin.access', 'web'),
            Permission::findOrCreate('orders.view', 'web'),
        ]);
        foreach (User::factory()->active()->count(12)->create() as $user) {
            $user->assignRole($role);
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            $recipients = iterator_to_array(app(NotificationAccessService::class)->eligibleRecipients(NotificationType::ORDER_PLACED));
            $queries = DB::getQueryLog();
        } finally {
            DB::disableQueryLog();
        }
        $this->assertCount(12, $recipients);
        $this->assertLessThanOrEqual(5, count($queries));
        $this->assertSame([], iterator_to_array(app(NotificationAccessService::class)->eligibleRecipients(NotificationType::REVIEW_APPROVED)));
    }

    public function test_role_permission_revocation_before_worker_skips_admin_delivery(): void
    {
        $user = User::factory()->admin()->create();
        app(NotificationService::class)->record($this->event());
        $delivery = NotificationDelivery::query()->where('channel', 'database')->sole();
        User::query()->whereKey($user->id)->update(['is_admin' => false]);
        $this->runDelivery($delivery);
        $this->assertSame(Status::SKIPPED, $delivery->fresh()->status);
        $this->assertSame(0, DatabaseNotification::query()->count());
    }

    public function test_active_superadmin_does_not_bypass_recipient_identity(): void
    {
        $owner = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        app(NotificationService::class)->record($this->event());
        $delivery = NotificationDelivery::query()->where('recipient_user_id', $owner->id)->where('channel', 'database')->sole();
        $delivery->update(['recipient_user_id' => $other->id]);
        $this->runDelivery($delivery);
        $this->assertSame(Status::SKIPPED, $delivery->fresh()->status);
    }

    public function test_database_delivery_uses_fixed_uuid_and_repeated_jobs_do_not_duplicate(): void
    {
        $owner = User::factory()->admin()->create();
        app(NotificationService::class)->record($this->event());
        $delivery = NotificationDelivery::query()->where('channel', 'database')->sole();
        $this->runDelivery($delivery);
        $this->runDelivery($delivery);
        $this->assertSame(Status::SENT, $delivery->fresh()->status);
        $this->assertSame(1, $delivery->fresh()->attempts);
        $this->assertSame(1, $owner->notifications()->count());
        $this->assertDatabaseHas('notifications', ['id' => $delivery->id, 'type' => 'order.placed', 'notifiable_id' => $owner->id]);
    }

    public function test_database_insert_and_sent_transition_roll_back_together(): void
    {
        $owner = User::factory()->admin()->create();
        app(NotificationService::class)->record($this->event());
        $delivery = NotificationDelivery::query()->where('channel', 'database')->sole();
        $repository = app(NotificationDeliveryRepositoryInterface::class);
        $claimed = $repository->claim($delivery->id);
        try {
            $repository->deliverDatabase($claimed, function () use ($owner, $claimed) {
                Notification::sendNow($owner, new SnapshotNotification($claimed), ['database']);
                throw new RuntimeException('Simulate failure before ledger completion');
            });
            $this->fail('Expected rollback');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulate failure before ledger completion', $exception->getMessage());
        }
        $this->assertDatabaseMissing('notifications', ['id' => $delivery->id]);
        $this->assertSame(Status::PROCESSING, $delivery->fresh()->status);
    }

    public function test_claim_is_atomic_and_stale_lease_cannot_finish(): void
    {
        app(NotificationService::class)->record($this->event());
        $delivery = NotificationDelivery::query()->sole();
        $repository = app(NotificationDeliveryRepositoryInterface::class);
        $first = $repository->claim($delivery->id);
        $this->assertNotNull($first);
        $this->assertNull($repository->claim($delivery->id));
        $this->travel(61)->seconds();
        $second = $repository->claim($delivery->id);
        $this->assertNotSame($first->lease_token, $second->lease_token);
        $this->assertSame(2, $second->attempts);
        $finish = ['status' => Status::SENT->value, 'finished_at' => now()];
        $this->assertFalse($repository->finish($first, $finish));
        $this->assertTrue($repository->finish($second, $finish));
    }

    public function test_mail_uses_array_transport_escaped_snapshot_and_no_open_transaction(): void
    {
        app(NotificationService::class)->record($this->event());
        $delivery = NotificationDelivery::query()->sole();
        $level = DB::transactionLevel();
        $sent = [];
        Event::listen(MessageSent::class, function (MessageSent $event) use (&$sent, $level) {
            $this->assertSame($level, DB::transactionLevel());
            $sent[] = $event->message->getHtmlBody();
        });
        $this->runDelivery($delivery);
        $this->runDelivery($delivery);
        $this->assertCount(1, $sent);
        $this->assertStringContainsString('&lt;script&gt;', $sent[0]);
        $this->assertStringNotContainsString('<script>', $sent[0]);
        $this->assertStringNotContainsString('internal_note', $sent[0]);
        $this->assertStringNotContainsString('/admin/', $sent[0]);
        $this->assertSame(Status::SENT, $delivery->fresh()->status);
        $this->assertSame('array', config('mail.default'));
    }

    public function test_mail_manager_bounds_smtp_without_mutating_global_settings(): void
    {
        config(['mail.mailers.smtp.timeout' => 120]);
        $before = config('mail');
        $manager = new NotificationMailManager(app());
        $transport = $manager->mailer('smtp')->getSymfonyTransport();
        $this->assertSame(15.0, (float) $transport->getStream()->getTimeout());
        $this->assertSame($before, config('mail'));
    }

    public function test_mail_failures_back_off_60_then_300_and_stop_after_three_attempts(): void
    {
        $this->travelTo(now()->startOfSecond());
        app(NotificationService::class)->record($this->event());
        $delivery = NotificationDelivery::query()->sole();
        Event::listen(MessageSending::class, fn () => throw new RuntimeException('smtp-secret-must-not-be-persisted'));
        $this->runDelivery($delivery);
        $this->assertSame(Status::PENDING, $delivery->fresh()->status);
        $this->assertTrue($delivery->fresh()->available_at->equalTo(now()->addSeconds(60)));
        $this->runDelivery($delivery);
        $this->assertSame(1, $delivery->fresh()->attempts);
        $this->travel(60)->seconds();
        $this->runDelivery($delivery);
        $this->assertTrue($delivery->fresh()->available_at->equalTo(now()->addSeconds(300)));
        $this->travel(300)->seconds();
        $this->runDelivery($delivery);
        $this->assertSame(Status::FAILED, $delivery->fresh()->status);
        $this->assertSame(3, $delivery->fresh()->attempts);
        $this->assertSame('delivery_attempt_failed', $delivery->fresh()->last_error_code);
        $this->travel(1)->days();
        $this->assertSame(0, app(NotificationService::class)->recover());
        $this->runDelivery($delivery);
        $this->assertSame(3, $delivery->fresh()->attempts);
    }

    public function test_deleted_review_author_is_skipped_and_never_creates_admin_notification(): void
    {
        $author = User::factory()->active()->create();
        User::factory()->admin()->create();
        $uuid = (string) Str::uuid();
        app(NotificationService::class)->record(new NotificationEventData(
            NotificationType::REVIEW_APPROVED, 'review.approved:'.$uuid, now()->toISOString(),
            mailPayload: ['subject' => 'Đánh giá được duyệt', 'lines' => ['Cảm ơn quý khách.']],
            mailRecipient: $author->email, mailRecipientKey: 'review-author:'.$uuid, mailUserId: $author->id,
        ));
        $delivery = NotificationDelivery::query()->sole();
        $author->delete();
        $this->runDelivery($delivery);
        $this->assertSame(Status::SKIPPED, $delivery->fresh()->status);
        $this->assertSame('recipient_missing', $delivery->fresh()->last_error_code);
    }

    public function test_recovery_closes_exhausted_expired_leases_without_resurrecting_them(): void
    {
        app(NotificationService::class)->record($this->event());
        $delivery = NotificationDelivery::query()->sole();
        $repository = app(NotificationDeliveryRepositoryInterface::class);
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->assertNotNull($repository->claim($delivery->id));
            $this->travel(61)->seconds();
        }
        $this->assertSame(0, app(NotificationService::class)->recover());
        $this->assertSame(Status::FAILED, $delivery->fresh()->status);
        $this->assertNotNull($delivery->fresh()->finished_at);
        $this->assertNull($repository->claim($delivery->id));
    }

    public function test_purge_preserves_unread_pending_and_365_day_dedup_window(): void
    {
        $owner = User::factory()->admin()->create();
        $oldRead = $owner->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'order.placed', 'data' => [], 'read_at' => now()->subDays(91)]);
        $unread = $owner->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'order.placed', 'data' => []]);
        app(NotificationService::class)->record($this->event());
        $mail = NotificationDelivery::query()->where('channel', 'mail')->sole();
        $pending = NotificationDelivery::query()->where('channel', 'database')->sole();
        $mail->update(['status' => Status::SENT, 'finished_at' => now()->subDays(91)]);
        app(NotificationService::class)->purge();
        $this->assertNull($oldRead->fresh());
        $this->assertNotNull($unread->fresh());
        $this->assertNull($mail->fresh()->payload);
        $this->assertNotNull($pending->fresh()->payload);
        $this->assertSame(Status::PENDING, $pending->fresh()->status);
        $mail->update(['finished_at' => now()->subDays(366)]);
        app(NotificationService::class)->purge();
        $this->assertNull($mail->fresh());
    }

    private function event(string $email = 'guest@example.test'): NotificationEventData
    {
        $uuid = (string) Str::uuid();

        return new NotificationEventData(
            NotificationType::ORDER_PLACED, 'order.placed:'.$uuid, now()->toISOString(),
            ['title' => 'Đơn hàng mới', 'message' => 'Một sự kiện', 'target_type' => 'order', 'target_uuid' => $uuid, 'internal_note' => 'not persisted'],
            ['subject' => 'Đơn hàng đã tạo', 'lines' => ['Nội dung <script>alert(1)</script>'], 'internal_note' => '/admin/secret'],
            $email, 'order-customer:'.$uuid,
        );
    }

    private function runDelivery(NotificationDelivery $delivery): void
    {
        (new DeliverNotificationJob($delivery->id))->handle(app(NotificationDeliveryService::class));
    }
}
