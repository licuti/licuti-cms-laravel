<?php

namespace Tests\Feature;

use App\Jobs\DeliverNotificationJob;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class NotificationConcurrencyTest extends TestCase
{
    private ?User $recipient = null;

    private string $eventKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('licuti_cms_test', DB::connection()->getDatabaseName());
        $this->eventKey = 'order.placed:'.Str::uuid();
    }

    protected function tearDown(): void
    {
        $deliveryIds = DB::table('notification_deliveries')->where('event_key', $this->eventKey)->pluck('id');
        foreach ($deliveryIds as $id) {
            DB::table('jobs')->where('payload', 'like', '%'.$id.'%')->delete();
        }
        DB::table('notifications')->whereIn('id', $deliveryIds)->delete();
        DB::table('notification_deliveries')->where('event_key', $this->eventKey)->delete();
        $this->recipient?->forceDelete();
        parent::tearDown();
    }

    public function test_concurrent_recording_keeps_one_intent_and_concurrent_workers_keep_one_inbox_entry(): void
    {
        $this->recipient = User::factory()->admin()->create();
        $uuid = Str::after($this->eventKey, 'order.placed:');
        $record = 'app(\\App\\Services\\Shared\\Notification\\NotificationService::class)->record('
            .'new \\App\\DTOs\\Notification\\NotificationEventData('
            .'type: \\App\\Enums\\NotificationType::ORDER_PLACED, '
            .'eventKey: '.var_export($this->eventKey, true).', occurredAt: now()->toISOString(), '
            .'adminPayload: ["title" => "Concurrent order", "message" => "A committed event", '
            .'"target_type" => "order", "target_uuid" => '.var_export($uuid, true).']));';

        $this->runTogether($record);
        $delivery = DB::table('notification_deliveries')
            ->where('event_key', $this->eventKey)->where('recipient_user_id', $this->recipient->id)->first();
        $this->assertNotNull($delivery);
        $this->assertSame(1, DB::table('notification_deliveries')->where('event_key', $this->eventKey)
            ->where('recipient_user_id', $this->recipient->id)->where('channel', 'database')->count());

        $this->runTogether('\\'.DeliverNotificationJob::class.'::dispatchSync('.var_export($delivery->id, true).');');
        $this->assertSame(1, DB::table('notifications')->where('id', $delivery->id)->count());
        $this->assertSame('sent', DB::table('notification_deliveries')->where('id', $delivery->id)->value('status'));
        $this->assertSame(1, DB::table('notification_deliveries')->where('id', $delivery->id)->value('attempts'));
    }

    private function runTogether(string $code): void
    {
        $bootstrap = 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; '
            .'$app->make(\\Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); '
            .'if (!app()->environment("testing") || \\Illuminate\\Support\\Facades\\DB::connection()->getDatabaseName() !== "licuti_cms_test") '
            .'{ throw new \\RuntimeException("Isolated test database required"); } ';
        $processes = [
            new Process([PHP_BINARY, '-r', $bootstrap.$code], base_path(), $this->processEnvironment()),
            new Process([PHP_BINARY, '-r', $bootstrap.$code], base_path(), $this->processEnvironment()),
        ];
        foreach ($processes as $process) {
            $process->start();
        }
        foreach ($processes as $process) {
            $process->wait();
            $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
        }
    }

    private function processEnvironment(): array
    {
        $database = config('database.connections.mysql');

        return [
            'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_URL' => '',
            'DB_HOST' => $database['host'], 'DB_PORT' => (string) $database['port'],
            'DB_DATABASE' => 'licuti_cms_test', 'DB_USERNAME' => $database['username'],
            'DB_PASSWORD' => $database['password'], 'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'database', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
        ];
    }
}
