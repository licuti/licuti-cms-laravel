<?php

namespace Tests\Feature\Order;

use App\Core\Enums\CouponType;
use App\DTOs\Order\OrderDTO;
use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\Order\OrderService;
use App\Services\Shared\Product\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancelRestoresStockTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;
    private StockService $stockService;
    private Product $product;
    private Coupon $coupon;
    private PaymentMethod $paymentMethod;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);
        $this->stockService = app(StockService::class);

        $this->user = User::factory()->create();

        $this->paymentMethod = PaymentMethod::create([
            'code'      => 'cod',
            'name'      => 'Thanh toán COD',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'sku'             => 'RESTORE-01',
            'price'           => 100000,
            'is_active'       => true,
            'product_type'    => 'simple',
            'stock_quantity'  => 10,
            'track_inventory' => true,
        ]);
        $this->product->translations()->create(['locale' => 'vi', 'name' => 'Sản phẩm Test Hoàn Kho', 'slug' => 'sp-hoan-kho']);

        $this->coupon = Coupon::create([
            'code'            => 'RESTORECOUPON',
            'type'            => CouponType::FIXED,
            'value'           => 20000,
            'min_order_value' => 50000,
            'usage_limit'     => 10,
            'used_count'      => 1,
            'is_active'       => true,
        ]);
    }

    public function test_cancelling_order_restores_product_stock_and_coupon_usage(): void
    {
        // 1. Giả lập trừ kho khi đặt hàng: kho 10 -> còn 7
        $this->stockService->decrementStock($this->product->id, null, 3);
        $this->product->refresh();
        $this->assertEquals(7, $this->product->stock_quantity);

        // Giả lập coupon usage record
        $this->coupon->usages()->create([
            'user_id'         => $this->user->id,
            'discount_amount' => 20000,
            'order_id'        => null, // sẽ gắn sau khi tạo order
        ]);

        // 2. Tạo đơn hàng với 3 sản phẩm và mã giảm giá
        $orderDTO = new OrderDTO(
            userId: $this->user->id,
            paymentMethodId: $this->paymentMethod->id,
            shippingAddress: [
                'recipient_name' => 'Nguyễn Văn A',
                'phone'          => '0912345678',
                'address_line'   => '123 Phố Huế',
                'district'       => 'Hai Bà Trưng',
                'province'       => 'Hà Nội',
            ],
            items: [
                [
                    'product_id'      => $this->product->id,
                    'product_name'    => 'Sản phẩm Test Hoàn Kho',
                    'product_sku'     => $this->product->sku,
                    'unit_price'      => 100000,
                    'quantity'        => 3,
                    'discount_amount' => 0,
                    'subtotal'        => 300000,
                ],
            ],
            subtotal: 300000,
            discountAmount: 20000,
            shippingFee: 0,
            taxAmount: 0,
            totalAmount: 280000,
            notes: 'Đơn hàng test hoàn kho',
            status: OrderStatus::PENDING,
            currency: 'VND',
            couponId: $this->coupon->id,
            couponCode: $this->coupon->code
        );

        $order = $this->orderService->create($orderDTO);

        // Gắn order_id vào coupon usage
        $this->coupon->usages()->update(['order_id' => $order->id]);

        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertEquals(1, $this->coupon->fresh()->used_count);

        // 3. Thực hiện HỦY đơn hàng
        $cancelledOrder = $this->orderService->updateStatus($order->uuid, OrderStatus::CANCELLED, 'Khách hàng đổi ý');

        $this->assertEquals(OrderStatus::CANCELLED, $cancelledOrder->status);

        // 4. Kiểm tra tồn kho đã được hoàn lại 3 (7 + 3 = 10)
        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock_quantity);

        // 5. Kiểm tra coupon usage đã được hoàn lại (used_count giảm về 0 và usage record bị xóa)
        $this->coupon->refresh();
        $this->assertEquals(0, $this->coupon->used_count);
        $this->assertDatabaseMissing('coupon_users', ['order_id' => $order->id]);
    }
}
