<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * LUỒNG C — an toàn vận hành:
 *  1. Route nhóm tiền (orders/payments/payment-methods) và nhóm kho (warehouses/inventories)
 *     trước đây chỉ có lớp "là admin", không có permission chi tiết. Nay đã gắn `can:`.
 *  2. Idempotency webhook phải được siết xuống tầng DB (unique index), không chỉ app-level.
 */
class MoneyModuleAuthorizationAndIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    /** Route admin + permission tương ứng vừa được gắn `can:` */
    private const GATED_ROUTES = [
        'admin.orders.index'          => 'orders.view',
        'admin.payments.index'        => 'payments.view',
        'admin.payment-methods.index' => 'payment-methods.view',
        'admin.warehouses.index'      => 'warehouses.view',
        'admin.inventories.index'     => 'inventories.view',
    ];

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makePayment(string $txnId): Payment
    {
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status'       => \App\Enums\OrderStatus::PENDING,
            'subtotal'     => 100000,
            'total_amount' => 100000,
        ]);

        return Payment::create([
            'uuid'                   => (string) Str::uuid(),
            'order_id'               => $order->id,
            'payment_method_id'      => null,
            'gateway_code'           => 'vnpay',
            'gateway_transaction_id' => $txnId,
            'status'                 => \App\Enums\PaymentStatus::SUCCESS,
            'amount'                 => 100000,
            'currency'               => 'VND',
        ]);
    }

    public function test_permission_names_exist_in_seeder(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        foreach (self::GATED_ROUTES as $permission) {
            $this->assertNotNull(
                Permission::where('guard_name', 'web')->where('name', $permission)->first(),
                "Permission {$permission} phải tồn tại (database/seeders/RolePermissionSeeder.php)"
            );
        }

        $this->assertNotNull(Permission::where('guard_name', 'web')->where('name', 'orders.delete')->first());
    }

    public function test_roles_without_permission_are_forbidden_from_gated_pages(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole('editor'); // editor không có permission nhóm tiền/kho

        foreach (self::GATED_ROUTES as $route => $permission) {
            $this->assertFalse($user->can($permission), "editor không được sở hữu {$permission}");

            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
    }

    public function test_is_admin_is_not_locked_out_by_the_new_gates(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Gate::before cho is_admin (AppServiceProvider) — bảo đảm đợt gắn can: không khóa super admin.
        foreach (array_keys(self::GATED_ROUTES) as $route) {
            $this->actingAs($this->makeAdmin())->get(route($route))->assertOk();
        }
    }

    public function test_finance_role_with_explicit_permission_can_open_payments(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $finance = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        $finance->syncPermissions([
            Permission::where('guard_name', 'web')->where('name', 'admin.access')->firstOrFail(),
            Permission::where('guard_name', 'web')->where('name', 'payments.view')->firstOrFail(),
        ]);

        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole($finance);

        $this->actingAs($user)->get(route('admin.payments.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.orders.index'))->assertForbidden();
    }

    public function test_duplicate_gateway_transaction_id_is_rejected_by_database(): void
    {
        $this->makePayment('TXN-DUPLICATE-001');

        // Webhook lặp lại / 2 request đồng thời không được tạo bản ghi thứ hai.
        $this->expectException(QueryException::class);

        $this->makePayment('TXN-DUPLICATE-001');
    }

    public function test_null_gateway_transaction_id_is_still_allowed(): void
    {
        $order = Order::create([
            'order_number'  => 'ORD-NULLTXN-01',
            'status'        => \App\Enums\OrderStatus::PENDING,
            'subtotal'      => 50000,
            'total_amount'  => 50000,
        ]);

        foreach ([1, 2] as $i) {
            $payment = Payment::create([
                'uuid'                   => (string) Str::uuid(),
                'order_id'               => $order->id,
                'payment_method_id'      => null,
                'gateway_code'           => 'cod',
                'gateway_transaction_id' => null,
                'status'                 => \App\Enums\PaymentStatus::PENDING,
                'amount'                 => 50000,
                'currency'               => 'VND',
            ]);

            $this->assertNotNull($payment->id, "Payment thứ {$i} không có gateway_transaction_id vẫn phải insert được");
        }
    }
}
