<?php

namespace Tests\Feature\Admin;

use App\Core\Enums\UserStatus;
use App\Enums\NotificationType;
use App\Http\Middleware\AdminMiddleware;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Shared\Notification\NotificationInboxService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['admin.access', 'orders.view', 'payments.view', 'products.view'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    public function test_guests_cannot_access_any_inbox_endpoint(): void
    {
        $this->get(route('admin.notifications.index'))->assertRedirect(route('admin.login'));
        $this->getJson(route('admin.notifications.feed'))->assertUnauthorized();
        foreach (['read', 'open', 'read-all'] as $action) {
            $this->postJson($this->actionUrl($action, (string) Str::uuid()))->assertUnauthorized();
        }
    }

    public function test_customer_is_blocked_by_middleware_and_by_form_requests_independently(): void
    {
        $customer = User::factory()->active()->create(['is_admin' => false]);
        $this->actingAs($customer)->get(route('admin.notifications.index'))->assertRedirect(route('admin.login'));
        $this->withoutMiddleware(AdminMiddleware::class);
        $this->actingAs($customer)->getJson(route('admin.notifications.feed'))->assertForbidden();
        $this->getJson(route('admin.notifications.index'))->assertForbidden();
        foreach (['read', 'open', 'read-all'] as $action) {
            $this->postJson($this->actionUrl($action, (string) Str::uuid()))->assertForbidden();
        }
    }

    public function test_inactive_superadmin_is_rejected_even_with_a_stale_session_user(): void
    {
        $admin = User::factory()->admin()->create();
        $notification = $this->notification($admin);
        $this->actingAs($admin)->getJson(route('admin.notifications.feed'))->assertOk();
        User::whereKey($admin->id)->update(['status' => UserStatus::BANNED->value]);
        $this->getJson(route('admin.notifications.index'))->assertForbidden();
        $this->getJson(route('admin.notifications.feed'))->assertForbidden();
        foreach (['read', 'open', 'read-all'] as $action) {
            $this->postJson($this->actionUrl($action, $notification->id))->assertForbidden();
        }
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_service_also_denies_inactive_owner_without_http_middleware(): void
    {
        $admin = User::factory()->admin()->create();
        User::whereKey($admin->id)->update(['status' => UserStatus::INACTIVE->value]);
        $this->expectException(AuthorizationException::class);
        app(NotificationInboxService::class)->feed($admin);
    }

    public function test_superadmin_cannot_read_or_open_another_owners_notification_or_morph(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        $mine = $this->notification($admin);
        $foreign = $this->notification($other);
        $foreignMorph = $this->notification($admin);
        $foreignMorph->update(['notifiable_type' => 'another-owner-type']);
        $this->actingAs($admin)->getJson(route('admin.notifications.feed'))
            ->assertJsonPath('data.counts.all', 1)->assertJsonPath('data.notifications.0.id', $mine->id);
        foreach ([$foreign, $foreignMorph] as $notification) {
            foreach (['read', 'open'] as $action) {
                $this->postJson($this->actionUrl($action, $notification->id))->assertNotFound();
            }
        }
        $this->postJson(route('admin.notifications.read-all'))->assertOk()->assertJsonPath('data.count', 1);
        $this->assertNull($foreign->fresh()->read_at);
        $this->assertNull($foreignMorph->fresh()->read_at);
        $this->assertNotNull($mine->fresh()->read_at);
    }

    public function test_permission_revocation_hides_existing_rows_before_count_pagination_read_and_open(): void
    {
        $staff = $this->staff(['orders.view', 'payments.view']);
        $order = $this->notification($staff);
        $payment = $this->notification($staff, NotificationType::PAYMENT_SUCCEEDED);
        $this->actingAs($staff)->getJson(route('admin.notifications.feed'))->assertJsonPath('data.counts.all', 2);
        $permission = Permission::findByName('orders.view', 'web');
        DB::table('model_has_permissions')->where('model_id', $staff->id)->where('permission_id', $permission->id)->delete();
        $this->getJson(route('admin.notifications.feed'))
            ->assertJsonPath('data.counts.all', 1)->assertJsonPath('data.notifications.0.id', $payment->id);
        $this->getJson(route('admin.notifications.index'))->assertJsonPath('data.notifications.total', 1);
        $this->postJson($this->actionUrl('read', $order->id))->assertNotFound();
        $this->postJson($this->actionUrl('open', $order->id))->assertNotFound();
        $this->postJson(route('admin.notifications.read-all'))->assertJsonPath('data.count', 1);
        $this->assertNull($order->fresh()->read_at);
    }

    public function test_role_permissions_are_reloaded_and_admin_access_revocation_is_checked_in_requests(): void
    {
        $staff = User::factory()->active()->create(['is_admin' => false]);
        $role = Role::findOrCreate('notification-reader', 'web');
        $role->givePermissionTo(['admin.access', 'orders.view']);
        $staff->assignRole($role);
        $notification = $this->notification($staff);
        // Independently exercise FormRequests even if global admin middleware uses cached permissions.
        $this->withoutMiddleware(AdminMiddleware::class);
        $this->actingAs($staff)->getJson(route('admin.notifications.feed'))->assertJsonPath('data.counts.all', 1);
        DB::table('role_has_permissions')->where('role_id', $role->id)->delete();
        $this->getJson(route('admin.notifications.feed'))->assertForbidden();
        $this->getJson(route('admin.notifications.index'))->assertForbidden();
        foreach (['read', 'open', 'read-all'] as $action) {
            $this->postJson($this->actionUrl($action, $notification->id))->assertForbidden();
        }
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_counts_and_twenty_row_pagination_filter_native_types_before_pagination(): void
    {
        $staff = $this->staff(['orders.view']);
        for ($index = 0; $index < 25; $index++) {
            $notification = $this->notification($staff);
            if ($index < 2) {
                $notification->update(['read_at' => now()]);
            }
        }
        for ($index = 0; $index < 12; $index++) {
            $this->notification($staff, NotificationType::STOCK_LOW, ['type' => NotificationType::ORDER_PLACED->value]);
        }
        $this->actingAs($staff)->getJson(route('admin.notifications.feed'))
            ->assertJsonPath('data.counts', ['all' => 25, 'unread' => 23, 'read' => 2])
            ->assertJsonCount(5, 'data.notifications');
        $this->getJson(route('admin.notifications.index'))
            ->assertJsonPath('data.notifications.total', 25)->assertJsonCount(20, 'data.notifications.data');
        $this->getJson(route('admin.notifications.index', ['page' => 2]))->assertJsonCount(5, 'data.notifications.data');
        $this->getJson(route('admin.notifications.index', ['tab' => 'read']))->assertJsonPath('data.notifications.total', 2);
        $this->getJson(route('admin.notifications.index', ['tab' => 'unread']))->assertJsonPath('data.notifications.total', 23);
        $this->getJson(route('admin.notifications.index', ['group' => 'products']))->assertJsonPath('data.notifications.total', 0);
    }

    public function test_staff_with_only_admin_access_can_view_an_empty_inbox(): void
    {
        $staff = $this->staff([]);
        $notification = $this->notification($staff);
        $this->actingAs($staff)->getJson(route('admin.notifications.feed'))
            ->assertOk()->assertJsonPath('data.counts.all', 0)->assertJsonCount(0, 'data.notifications');
        $this->get(route('admin.notifications.index'))->assertOk();
        $this->postJson(route('admin.notifications.read-all'))->assertOk()->assertJsonPath('data.count', 0);
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_revoking_superadmin_flag_is_not_bypassed_by_a_stale_session_user(): void
    {
        $admin = User::factory()->admin()->create();
        $notification = $this->notification($admin);
        $this->actingAs($admin)->getJson(route('admin.notifications.feed'))->assertOk();
        User::whereKey($admin->id)->update(['is_admin' => false]);
        $this->getJson(route('admin.notifications.feed'))->assertForbidden();
        $this->postJson($this->actionUrl('open', $notification->id))->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_group_filters_and_unknown_types_are_not_exposed(): void
    {
        $admin = User::factory()->admin()->create();
        $this->notification($admin);
        $this->notification($admin, NotificationType::PAYMENT_SUCCEEDED);
        $this->notification($admin, NotificationType::STOCK_LOW);
        $this->notification($admin)->update(['type' => 'unknown.event']);
        $this->actingAs($admin)->getJson(route('admin.notifications.feed'))->assertJsonPath('data.counts.all', 3);
        foreach (['orders', 'payments', 'products'] as $group) {
            $this->getJson(route('admin.notifications.index', ['group' => $group]))
                ->assertJsonPath('data.notifications.total', 1)
                ->assertJsonPath('data.notifications.data.0.group', $group);
        }
    }

    public function test_filters_uuid_and_owner_input_are_validated(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        foreach (['tab' => 'invalid', 'group' => 'reviews', 'page' => 0, 'user_id' => 999] as $key => $value) {
            $this->getJson(route('admin.notifications.index', [$key => $value]))->assertUnprocessable();
        }
        $this->postJson(route('admin.notifications.read', ['uuid' => 'not-a-uuid']))->assertNotFound();
        $this->postJson(route('admin.notifications.read-all'), ['user_id' => 999])->assertUnprocessable();
    }

    public function test_get_never_marks_read_and_post_read_is_idempotent(): void
    {
        $admin = User::factory()->admin()->create();
        $notification = $this->notification($admin);
        $this->actingAs($admin)->get(route('admin.notifications.index'))->assertOk();
        $this->getJson(route('admin.notifications.feed'))->assertOk();
        $this->get($this->actionUrl('open', $notification->id))->assertStatus(405);
        $this->get($this->actionUrl('read', $notification->id))->assertStatus(405);
        $this->assertNull($notification->fresh()->read_at);
        $this->postJson($this->actionUrl('read', $notification->id))->assertOk();
        $firstReadAt = $notification->fresh()->read_at;
        $this->travel(1)->minutes();
        $this->postJson($this->actionUrl('read', $notification->id))->assertOk();
        $this->assertTrue($firstReadAt->equalTo($notification->fresh()->read_at));
    }

    public function test_open_uses_only_whitelisted_routes_for_existing_targets(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::create(['order_number' => 'NOTIFICATION-TEST', 'subtotal' => 100, 'total_amount' => 100]);
        $payment = Payment::create(['order_id' => $order->id, 'gateway_code' => 'cod', 'amount' => 100]);
        $product = Product::withoutEvents(fn () => Product::create([
            'uuid' => (string) Str::uuid(), 'sku' => 'NOTIFICATION-SKU', 'price' => 100,
        ]));
        $targets = [
            [NotificationType::ORDER_PLACED, 'order', $order->uuid, 'admin.orders.show'],
            [NotificationType::PAYMENT_SUCCEEDED, 'payment', $payment->uuid, 'admin.payments.edit'],
            [NotificationType::STOCK_LOW, 'product', $product->uuid, 'admin.products.edit'],
        ];
        $this->actingAs($admin);
        foreach ($targets as [$type, $targetType, $uuid, $route]) {
            $notification = $this->notification($admin, $type, [
                'target_type' => $targetType, 'target_uuid' => $uuid, 'url' => 'https://evil.example/',
            ]);
            $this->postJson($this->actionUrl('open', $notification->id))->assertOk()
                ->assertJsonPath('data.url', route($route, ['uuid' => $uuid], false));
            $this->assertNotNull($notification->fresh()->read_at);
        }
    }

    public function test_deleted_and_invalid_targets_fall_back_with_a_clear_message(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::create(['order_number' => 'NOTIFICATION-DELETED', 'subtotal' => 100, 'total_amount' => 100]);
        $order->delete();
        $this->actingAs($admin);
        foreach ([['order', $order->uuid], ['order', 'https://evil.example/'], ['product', (string) Str::uuid()]] as [$type, $uuid]) {
            $notification = $this->notification($admin, NotificationType::ORDER_PLACED, [
                'target_type' => $type, 'target_uuid' => $uuid, 'url' => 'https://evil.example/',
            ]);
            $response = $this->postJson($this->actionUrl('open', $notification->id))->assertOk()
                ->assertJsonPath('data.url', route('admin.orders.index', [], false));
            $this->assertNotEmpty($response->json('data.message'));
            $response->assertSessionHas('warning');
        }
    }

    public function test_mark_all_updates_only_allowed_snapshot_ids_not_later_inserts(): void
    {
        $staff = $this->staff(['orders.view']);
        $existing = $this->notification($staff);
        $hidden = $this->notification($staff, NotificationType::STOCK_LOW);
        $foreign = $this->notification(User::factory()->admin()->create());
        $late = null;
        $captured = false;
        DB::listen(function (QueryExecuted $query) use ($staff, &$late, &$captured): void {
            if ($captured || ! str_starts_with(strtolower($query->sql), 'select')
                || ! str_contains($query->sql, 'notifications') || ! str_contains($query->sql, 'read_at')) {
                return;
            }
            $captured = true;
            $late = $this->notification($staff);
        });
        $this->actingAs($staff)->postJson(route('admin.notifications.read-all'))
            ->assertOk()->assertJsonPath('data.count', 1);
        $this->assertTrue($captured);
        $this->assertNotNull($existing->fresh()->read_at);
        $this->assertNull($late->fresh()->read_at);
        $this->assertNull($hidden->fresh()->read_at);
        $this->assertNull($foreign->fresh()->read_at);
    }

    public function test_notification_markup_is_escaped_and_feed_contains_no_stored_urls(): void
    {
        $admin = User::factory()->admin()->create();
        $malicious = '<img src=x onerror=alert(1)>';
        $this->notification($admin, NotificationType::ORDER_PLACED, [
            'title' => $malicious, 'message' => '<script>alert(2)</script>', 'url' => 'https://evil.example/',
        ]);
        $this->actingAs($admin)->get(route('admin.notifications.index'))->assertOk()
            ->assertSee($malicious)->assertDontSee($malicious, false)
            ->assertDontSee('<script>alert(2)</script>', false)->assertDontSee('https://evil.example/', false);
        $response = $this->getJson(route('admin.notifications.feed'))->assertOk();
        $response->assertJsonPath('data.notifications.0.title', $malicious);
        $this->assertArrayNotHasKey('url', $response->json('data.notifications.0'));
        $javascript = file_get_contents(resource_path('js/admin/notifications.js'));
        $this->assertStringContainsString('textContent', $javascript);
        $this->assertStringNotContainsString('innerHTML', $javascript);
        $this->assertStringNotContainsString('insertAdjacentHTML', $javascript);
    }

    public function test_writes_require_csrf_tokens(): void
    {
        $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
        $admin = User::factory()->admin()->create();
        $notification = $this->notification($admin);
        $this->actingAs($admin)->withSession(['_token' => 'notification-csrf-test']);
        foreach (['read', 'open', 'read-all'] as $action) {
            $this->postJson($this->actionUrl($action, $notification->id))->assertStatus(419);
        }
        $this->assertNull($notification->fresh()->read_at);
        $this->postJson($this->actionUrl('read', $notification->id), ['_token' => 'notification-csrf-test'])->assertOk();
    }

    private function staff(array $permissions): User
    {
        $user = User::factory()->active()->create(['is_admin' => false]);
        $user->givePermissionTo(array_merge(['admin.access'], $permissions));

        return $user;
    }

    private function notification(User $owner, NotificationType $type = NotificationType::ORDER_PLACED, array $payload = []): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => $type->value,
            'notifiable_type' => $owner->getMorphClass(),
            'notifiable_id' => $owner->getKey(),
            'data' => array_merge([
                'title' => 'Notification test', 'message' => 'Safe message', 'type' => $type->value,
                'occurred_at' => now()->toIso8601String(), 'target_type' => 'order', 'target_uuid' => (string) Str::uuid(),
            ], $payload),
        ]);
    }

    private function actionUrl(string $action, string $uuid): string
    {
        return route('admin.notifications.'.$action, $action === 'read-all' ? [] : ['uuid' => $uuid]);
    }
}
