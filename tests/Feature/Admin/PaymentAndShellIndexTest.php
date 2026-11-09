<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression test cho LUỒNG A (P0): 3 trang admin từng trả HTTP 500 vì
 * controller truyền biến viết hoa (compact('Payments')) trong khi view dùng
 * biến chữ thường (\$payments->total()).
 *
 * Xem them: docs/modules/00-module-status.md
 */
class PaymentAndShellIndexTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    private function makePayment(string $txnId, PaymentStatus $status = PaymentStatus::SUCCESS): Payment
    {
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 250000,
            'total_amount' => 250000,
        ]);

        $method = PaymentMethod::create([
            'code'      => 'vnpay',
            'name'      => 'VNPay Sandbox',
            'is_active' => true,
        ]);

        return Payment::create([
            'uuid'                   => (string) Str::uuid(),
            'order_id'               => $order->id,
            'payment_method_id'      => $method->id,
            'gateway_code'           => 'vnpay',
            'gateway_transaction_id' => $txnId,
            'status'                 => $status,
            'amount'                 => 250000,
            'currency'               => 'VND',
        ]);
    }

    public function test_guest_is_redirected_from_the_three_fixed_pages(): void
    {
        foreach (['admin.payments.index', 'admin.warehouses.index', 'admin.inventories.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('admin.login'));
        }
    }

    public function test_admin_payments_index_returns_200_and_renders_real_data(): void
    {
        $payment = $this->makePayment('TXN-SMOKE-000001');

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('TXN-SMOKE-000001');
        $response->assertSee($payment->order->order_number);
        $response->assertSee('VNPay Sandbox');
        $response->assertSee(PaymentStatus::SUCCESS->label());
    }

    public function test_admin_payments_index_returns_200_when_table_is_empty(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.payments.index'))
            ->assertStatus(200);
    }

    public function test_warehouse_and_inventory_shell_pages_return_200_with_notice(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.warehouses.index'))
            ->assertStatus(200)
            ->assertSee('Module Kho hàng chưa được triển khai');

        $this->actingAs($this->admin)
            ->get(route('admin.inventories.index'))
            ->assertStatus(200)
            ->assertSee('Module Kiểm kê chưa được triển khai');
    }

    public function test_sidebar_hides_shell_module_links_but_keeps_payments(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->getContent();

        $this->assertStringNotContainsString('/admin/warehouses', $html);
        $this->assertStringNotContainsString('/admin/inventories', $html);
        $this->assertStringContainsString('/admin/payments', $html);
    }
}
