<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_cannot_access_orders_index(): void
    {
        $response = $this->get(route('admin.orders.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_orders_index(): void
    {
        $order = Order::create([
            'order_number'    => 'ORD-20261019-TEST01',
            'status'          => OrderStatus::PENDING,
            'subtotal'        => 300000,
            'total_amount'    => 300000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('ORD-20261019-TEST01');
    }

    public function test_admin_can_create_order_with_items_and_address(): void
    {
        $pm = PaymentMethod::create([
            'code'      => 'cod',
            'name'      => 'Tiền mặt khi nhận hàng (COD)',
            'is_active' => true,
        ]);

        $payload = [
            'payment_method_id' => $pm->id,
            'recipient_name'    => 'Nguyễn Văn Test',
            'phone'             => '0987654321',
            'email'             => 'test@example.com',
            'address_line'      => '123 Đường Số 1',
            'ward'              => 'Phường 1',
            'district'          => 'Quận 1',
            'province'          => 'TP.HCM',
            'shipping_fee'      => 30000,
            'discount_amount'   => 10000,
            'notes'             => 'Giao cẩn thận',
            'items'             => [
                [
                    'product_name' => 'Sản phẩm thử nghiệm A',
                    'product_sku'  => 'SKU-TEST-01',
                    'unit_price'   => 200000,
                    'quantity'     => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.orders.store'), $payload);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'payment_method_id' => $pm->id,
            'subtotal'          => 400000,
            'shipping_fee'      => 30000,
            'discount_amount'   => 10000,
            'total_amount'      => 420000,
            'status'            => OrderStatus::PENDING->value,
        ]);

        $order = Order::where('subtotal', 400000)->first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->uuid);
        $this->assertStringStartsWith('ORD-', $order->order_number);

        $this->assertDatabaseHas('shipping_addresses', [
            'order_id'       => $order->id,
            'recipient_name' => 'Nguyễn Văn Test',
            'phone'          => '0987654321',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id'     => $order->id,
            'product_name' => 'Sản phẩm thử nghiệm A',
            'unit_price'   => 200000,
            'quantity'     => 2,
            'subtotal'     => 400000,
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id'  => $order->id,
            'to_status' => OrderStatus::PENDING->value,
        ]);
    }

    public function test_admin_can_view_order_show(): void
    {
        $order = Order::create([
            'order_number'    => 'ORD-SHOW-001',
            'status'          => OrderStatus::PENDING,
            'subtotal'        => 150000,
            'total_amount'    => 150000,
        ]);

        $order->items()->create([
            'product_name' => 'Áo thun cotton',
            'product_sku'  => 'AT-01',
            'unit_price'   => 150000,
            'quantity'     => 1,
            'subtotal'     => 150000,
        ]);

        $order->shippingAddress()->create([
            'recipient_name' => 'Trần Thị B',
            'phone'          => '0912345678',
            'address_line'   => '456 Lê Lợi',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $order->uuid));

        $response->assertStatus(200);
        $response->assertSee('ORD-SHOW-001');
        $response->assertSee('Áo thun cotton');
        $response->assertSee('Trần Thị B');
    }

    public function test_admin_can_update_order_status_with_history(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-STATUS-001',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 100000,
            'total_amount' => 100000,
        ]);

        $payload = [
            'status' => OrderStatus::CONFIRMED->value,
            'note'   => 'Đã gọi điện xác nhận đơn hàng thành công.',
        ];

        $response = $this->actingAs($this->admin)->patch(
            route('admin.orders.update-status', $order->uuid),
            $payload
        );

        $response->assertRedirect(route('admin.orders.show', $order->uuid));
        $this->assertSame(OrderStatus::CONFIRMED, $order->fresh()->status);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id'    => $order->id,
            'from_status' => OrderStatus::PENDING->value,
            'to_status'   => OrderStatus::CONFIRMED->value,
            'note'        => 'Đã gọi điện xác nhận đơn hàng thành công.',
        ]);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-INVALID-001',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 100000,
            'total_amount' => 100000,
        ]);

        // PENDING không thể nhảy thẳng sang DELIVERED
        $payload = [
            'status' => OrderStatus::DELIVERED->value,
        ];

        $response = $this->actingAs($this->admin)->patch(
            route('admin.orders.update-status', $order->uuid),
            $payload
        );

        $response->assertSessionHas('error');
        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_admin_can_cancel_order(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-CANCEL-001',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 100000,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admin.orders.cancel', $order->uuid),
            ['cancel_reason' => 'Khách hàng đổi ý muốn mua màu khác']
        );

        $response->assertRedirect(route('admin.orders.show', $order->uuid));
        $fresh = $order->fresh();
        $this->assertSame(OrderStatus::CANCELLED, $fresh->status);
        $this->assertSame('Khách hàng đổi ý muốn mua màu khác', $fresh->cancel_reason);
        $this->assertNotNull($fresh->cancelled_at);
    }

    public function test_admin_can_soft_delete_order(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-DELETE-001',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 50000,
            'total_amount' => 50000,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(
            route('admin.orders.destroy', $order->uuid)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);
    }
}
