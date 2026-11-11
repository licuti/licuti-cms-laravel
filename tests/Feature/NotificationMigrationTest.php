<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('licuti_cms_test', DB::connection()->getDatabaseName());
    }

    public function test_notifications_use_laravel_schema_with_numeric_user_keys(): void
    {
        $this->assertSame([
            'id', 'type', 'notifiable_type', 'notifiable_id', 'data',
            'read_at', 'created_at', 'updated_at',
        ], Schema::getColumnListing('notifications'));
        $this->assertSame('char', Schema::getColumnType('notifications', 'id'));
        $this->assertSame('bigint', Schema::getColumnType('notifications', 'notifiable_id'));
        $this->assertSame('text', Schema::getColumnType('notifications', 'data'));
        $this->assertTrue(Schema::hasIndex('notifications', ['id'], 'primary'));
        $this->assertTrue(Schema::hasIndex('notifications', ['notifiable_type', 'notifiable_id', 'created_at']));
        $this->assertTrue(Schema::hasIndex('notifications', ['notifiable_type', 'notifiable_id', 'read_at', 'created_at']));
    }

    public function test_user_notification_relationship_supports_unicode_and_read_state(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'order.placed',
            'data' => ['title' => 'Đơn hàng mới', 'order_number' => 'ORD-TEST'],
        ]);

        $this->assertInstanceOf(DatabaseNotification::class, $notification);
        $this->assertSame($owner->id, (int) $notification->notifiable_id);
        $this->assertSame('Đơn hàng mới', $notification->fresh()->data['title']);
        $this->assertSame(1, $owner->unreadNotifications()->count());
        $this->assertSame(0, $other->notifications()->count());

        $notification->markAsRead();
        $this->assertSame(0, $owner->unreadNotifications()->count());
        $this->assertSame(1, $owner->readNotifications()->count());

        $notification->markAsUnread();
        $this->assertSame(1, $owner->unreadNotifications()->count());
    }

    public function test_deliveries_have_retry_lease_and_retention_columns(): void
    {
        $this->assertSame([
            'id', 'event_key', 'recipient_key', 'channel', 'event_type', 'recipient_user_id',
            'payload', 'status', 'attempts', 'available_at', 'lease_token', 'lease_expires_at',
            'last_enqueued_at', 'sent_at', 'finished_at', 'last_error_code', 'created_at', 'updated_at',
        ], Schema::getColumnListing('notification_deliveries'));
        $this->assertSame('json', Schema::getColumnType('notification_deliveries', 'payload'));
        $this->assertTrue(Schema::hasIndex('notification_deliveries', ['event_key', 'recipient_key', 'channel'], 'unique'));
        $this->assertTrue(Schema::hasIndex('notification_deliveries', ['status', 'available_at']));
        $this->assertTrue(Schema::hasIndex('notification_deliveries', ['status', 'lease_expires_at']));
        $this->assertTrue(Schema::hasIndex('notification_deliveries', ['status', 'finished_at']));
    }

    public function test_guest_delivery_defaults_to_pending_with_no_attempts(): void
    {
        $delivery = $this->deliveryRow();
        DB::table('notification_deliveries')->insert($delivery);
        $saved = DB::table('notification_deliveries')->where('id', $delivery['id'])->first();

        $this->assertSame('pending', $saved->status);
        $this->assertSame(0, (int) $saved->attempts);
        $this->assertNull($saved->recipient_user_id);
        $this->assertNull($saved->lease_token);
        $this->assertNull($saved->lease_expires_at);
        $this->assertNull($saved->sent_at);
        $this->assertNull($saved->finished_at);
        $this->assertSame('Đơn hàng mới', json_decode($saved->payload, true, 512, JSON_THROW_ON_ERROR)['title']);
    }

    public function test_database_rejects_duplicate_event_recipient_and_channel(): void
    {
        $delivery = $this->deliveryRow();
        DB::table('notification_deliveries')->insert($delivery);

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('notification_deliveries')->insert(array_replace($delivery, ['id' => (string) Str::uuid()]));
    }

    public function test_same_event_can_be_delivered_to_different_recipients_and_channels(): void
    {
        $delivery = $this->deliveryRow();
        DB::table('notification_deliveries')->insert([
            $delivery,
            array_replace($delivery, ['id' => (string) Str::uuid(), 'recipient_key' => 'user:second']),
            array_replace($delivery, ['id' => (string) Str::uuid(), 'channel' => 'database']),
            array_replace($delivery, ['id' => (string) Str::uuid(), 'event_key' => 'order.cancelled:'.$delivery['id']]),
        ]);

        $this->assertSame(4, DB::table('notification_deliveries')->count());
    }

    public function test_full_length_unicode_keys_fit_the_unique_index(): void
    {
        $delivery = $this->deliveryRow([
            'event_key' => str_repeat('đ', 128),
            'recipient_key' => str_repeat('ộ', 128),
            'channel' => str_repeat('a', 16),
        ]);
        DB::table('notification_deliveries')->insert($delivery);

        $this->assertDatabaseHas('notification_deliveries', [
            'id' => $delivery['id'],
            'event_key' => $delivery['event_key'],
            'recipient_key' => $delivery['recipient_key'],
        ]);
    }

    public function test_deleting_user_preserves_delivery_and_deduplication_key(): void
    {
        $user = User::factory()->create();
        $delivery = $this->deliveryRow([
            'recipient_user_id' => $user->id,
            'recipient_key' => 'user:'.$user->uuid,
            'channel' => 'database',
        ]);
        DB::table('notification_deliveries')->insert($delivery);
        $user->forceDelete();

        $this->assertDatabaseHas('notification_deliveries', [
            'id' => $delivery['id'],
            'recipient_user_id' => null,
            'recipient_key' => $delivery['recipient_key'],
        ]);
    }

    public function test_recipient_user_foreign_key_rejects_missing_users(): void
    {
        $this->expectException(QueryException::class);
        DB::table('notification_deliveries')->insert($this->deliveryRow(['recipient_user_id' => PHP_INT_MAX]));
    }

    public function test_removing_payload_does_not_remove_duplicate_protection(): void
    {
        $delivery = $this->deliveryRow();
        DB::table('notification_deliveries')->insert($delivery);
        DB::table('notification_deliveries')->where('id', $delivery['id'])->update([
            'payload' => null,
            'status' => 'sent',
            'finished_at' => now(),
        ]);
        $this->assertDatabaseHas('notification_deliveries', ['id' => $delivery['id'], 'payload' => null]);

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('notification_deliveries')->insert(array_replace($delivery, ['id' => (string) Str::uuid()]));
    }

    public function test_delivery_timestamps_preserve_microseconds(): void
    {
        $timestamp = '2026-11-10 12:30:00.123456';
        $delivery = $this->deliveryRow(['created_at' => $timestamp, 'available_at' => $timestamp]);
        DB::table('notification_deliveries')->insert($delivery);
        $saved = DB::table('notification_deliveries')->where('id', $delivery['id'])->first();

        $this->assertSame($timestamp, $saved->created_at);
        $this->assertSame($timestamp, $saved->available_at);
    }

    public function test_delivery_insert_participates_in_the_business_transaction(): void
    {
        $delivery = $this->deliveryRow();
        DB::beginTransaction();
        try {
            DB::table('notification_deliveries')->insert($delivery);
            $this->assertDatabaseHas('notification_deliveries', ['id' => $delivery['id']]);
        } finally {
            DB::rollBack();
        }

        $this->assertDatabaseMissing('notification_deliveries', ['id' => $delivery['id']]);
    }

    public function test_migrations_can_roll_back_and_run_again_without_dropping_existing_tables(): void
    {
        $originalSchema = Schema::getFacadeRoot();
        $connectionName = 'notification_schema_test';
        $connectionConfig = config('database.connections.'.config('database.default'));
        $connectionConfig['prefix'] = 'nt_'.Str::lower(Str::random(6)).'_';
        config(['database.connections.'.$connectionName => $connectionConfig]);
        $schema = DB::connection($connectionName)->getSchemaBuilder();
        $notifications = require database_path('migrations/2026_11_10_000001_create_notifications_table.php');
        $deliveries = require database_path('migrations/2026_11_10_000002_create_notification_deliveries_table.php');

        // A separate PDO and table prefix isolate MySQL DDL from the test transaction.
        try {
            Schema::swap($schema);
            $schema->create('users', fn (Blueprint $table) => $table->id());
            $notifications->up();
            $deliveries->up();
            $this->assertTrue($schema->hasTable('notifications'));
            $this->assertTrue($schema->hasTable('notification_deliveries'));

            $deliveries->down();
            $notifications->down();
            $this->assertFalse($schema->hasTable('notifications'));
            $this->assertFalse($schema->hasTable('notification_deliveries'));
            $this->assertTrue($schema->hasTable('users'));

            $notifications->up();
            $deliveries->up();
            $this->assertTrue($schema->hasTable('notifications'));
            $this->assertTrue($schema->hasTable('notification_deliveries'));
        } finally {
            Schema::swap($originalSchema);
            $schema->dropIfExists('notification_deliveries');
            $schema->dropIfExists('notifications');
            $schema->dropIfExists('users');
            DB::purge($connectionName);
        }

        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('notifications'));
        $this->assertTrue(Schema::hasTable('notification_deliveries'));
    }

    private function deliveryRow(array $overrides = []): array
    {
        $eventId = (string) Str::uuid();

        return array_replace([
            'id' => (string) Str::uuid(),
            'event_key' => 'order.placed:'.$eventId,
            'recipient_key' => 'order-customer:'.$eventId,
            'channel' => 'mail',
            'event_type' => 'order.placed',
            'payload' => json_encode(['title' => 'Đơn hàng mới'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);
    }
}
