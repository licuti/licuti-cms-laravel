<?php

namespace Tests\Feature\Api;

use App\Actions\Order\ReleaseFlashSaleQuotaAction;
use App\Core\Enums\CouponType;
use App\Enums\RedemptionStatus;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\FlashSaleRedemption;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Services\Admin\Order\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FLASH SALE PHASE 2 — tích hợp giá flash vào giỏ/checkout.
 *
 * Quy tắc đã chốt với chủ dự án 2026-11-03 và được kiểm chứng ở file này:
 * 1. Giá flash KHÔNG cộng dồn coupon (coupon chỉ tính trên hàng thường).
 * 2. Giá TÍNH LẠI tại thời điểm checkout (chiến dịch kết thúc giữa chừng -> về giá thường).
 * 3. Suất flash được GIỮ KHI ĐẶT HÀNG (không chờ webhook), release khi hủy đơn / fail.
 */
class FlashSaleCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Product $flashProduct;
    private Product $normalProduct;
    private FlashSale $flashSale;
    private FlashSaleProduct $offer;

    private const FLASH_PRICE   = 120000;
    private const REGULAR_PRICE = 200000;
    private const NORMAL_PRICE  = 150000;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashProduct = $this->makeProduct('SP-FLASH-01', self::REGULAR_PRICE, 100);
        $this->normalProduct = $this->makeProduct('SP-NORMAL-01', self::NORMAL_PRICE, 50);

        PaymentMethod::create([
            'code'      => 'cod',
            'name'      => 'Thanh toán COD',
            'is_active' => true,
        ]);

        $this->startCampaign(quantityLimit: 5, limitPerUser: 0);
    }

    private function makeProduct(string $sku, float $price, int $stock): Product
    {
        $product = Product::create([
            'sku'             => $sku,
            'price'           => $price,
            'is_active'       => true,
            'product_type'    => 'simple',
            'stock_quantity'  => $stock,
            'track_inventory' => true,
        ]);

        $product->translations()->create([
            'locale' => 'vi',
            'name'   => 'Sản phẩm ' . $sku,
            'slug'   => strtolower($sku),
        ]);

        return $product;
    }

    private function startCampaign(int $quantityLimit = 5, int $limitPerUser = 0): void
    {
        // Đảm bảo chỉ MỘT chiến dịch đang chạy cho sản phẩm này (giống ràng buộc đã áp ở admin:
        // commit a2e86df "prevent overlapping campaigns").
        FlashSale::query()->where('ends_at', '>', Carbon::now())->update([
            'ends_at'   => Carbon::now()->subMinute(),
            'is_active' => false,
        ]);

        $this->flashSale = FlashSale::create([
            'starts_at' => Carbon::now()->subHour(),
            'ends_at'   => Carbon::now()->addHours(2),
            'is_active' => true,
        ]);

        $this->flashSale->translations()->create([
            'locale' => 'vi',
            'name'   => 'Flash Sale 11/03',
        ]);

        $this->offer = $this->flashSale->products()->create([
            'product_id'     => $this->flashProduct->id,
            'variant_key'    => "{$this->flashProduct->id}-0",
            'flash_price'    => self::FLASH_PRICE,
            'quantity_limit' => $quantityLimit,
            'limit_per_user' => $limitPerUser,
            'sold_quantity'  => 0,
        ]);

        $this->offer->refresh();
    }

    /**
     * @param array<int, array{product_id:int, quantity:int}> $items
     * @return array<string, mixed>
     */
    private function payload(array $items, ?string $couponCode = null, string $email = 'khach@example.com'): array
    {
        $payload = [
            'payment_method_code' => 'cod',
            'recipient_name'      => 'Trần Test',
            'phone'               => '0909111222',
            'email'               => $email,
            'address_line'        => '1 Lê Lợi',
            'district'            => 'Quận 1',
            'province'            => 'TP.HCM',
            'items'               => $items,
        ];

        if ($couponCode !== null) {
            $payload['coupon_code'] = $couponCode;
        }

        return $payload;
    }

    public function test_checkout_applies_flash_price_instead_of_regular_price(): void
    {
        $response = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 2],
        ]));

        $response->assertStatus(201);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame(240000.0, (float) $order->subtotal);

        $this->assertDatabaseHas('order_items', [
            'order_id'                => $order->id,
            'product_id'              => $this->flashProduct->id,
            'unit_price'              => self::FLASH_PRICE,
            'quantity'                => 2,
            'flash_sale_product_id'   => $this->offer->id,
        ]);
    }

    public function test_checkout_reserves_quota_and_writes_redemption(): void
    {
        $response = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 3],
        ]));
        $response->assertStatus(201);

        $order = Order::latest('id')->first();
        $orderItem = $order->items->firstWhere('product_id', $this->flashProduct->id);

        $this->assertSame(3, (int) $this->offer->fresh()->sold_quantity);

        $this->assertDatabaseHas('flash_sale_redemptions', [
            'flash_sale_product_id' => $this->offer->id,
            'order_item_id'         => $orderItem->id,
            'guest_email'           => 'khach@example.com',
            'quantity'              => 3,
            'status'                => RedemptionStatus::RESERVED->value,
        ]);
    }

    public function test_checkout_is_blocked_when_flash_quota_is_exceeded_and_rolls_back(): void
    {
        $response = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 6], // limit = 5
        ]));

        $response->assertStatus(409);
        $this->assertStringContainsString('Flash Sale', (string) $response->json('message'));

        $this->assertSame(0, Order::count(), 'Đơn bị chặn thì không được tồn tại');
        $this->assertSame(0, (int) $this->offer->fresh()->sold_quantity);
        $this->assertSame(100, (int) $this->flashProduct->fresh()->stock_quantity, 'Tồn kho phải được rollback');
        $this->assertSame(0, FlashSaleRedemption::count());
    }

    public function test_limit_per_user_blocks_a_second_order_from_the_same_guest_email(): void
    {
        $this->startCampaign(quantityLimit: 10, limitPerUser: 2);

        $first = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 2],
        ]));
        $first->assertStatus(201);

        $second = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 1],
        ]));

        $second->assertStatus(409);
        $this->assertSame(2, (int) $this->offer->fresh()->sold_quantity);
    }

    public function test_coupon_is_not_stacked_on_flash_price(): void
    {
        Coupon::create([
            'code'      => 'FLASH10',
            'type'      => CouponType::PERCENT,
            'value'     => 10,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 1],   // 120.000 (flash)
            ['product_id' => $this->normalProduct->id, 'quantity' => 1],  // 150.000 (thường)
        ], 'FLASH10'));

        $response->assertStatus(201);

        $order = Order::latest('id')->first();

        // Coupon 10% CHỈ tính trên 150.000đ hàng thường = 15.000đ, không phải 27.000đ trên tổng 270.000đ.
        $this->assertSame(15000.0, (float) $order->discount_amount);
        $this->assertSame(270000.0, (float) $order->subtotal);
    }

    public function test_flash_only_cart_receives_no_coupon_discount(): void
    {
        Coupon::create([
            'code'      => 'FLASHONLY',
            'type'      => CouponType::PERCENT,
            'value'     => 10,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 1],
        ], 'FLASHONLY'));

        $response->assertStatus(201);

        $order = Order::latest('id')->first();
        $this->assertSame(0.0, (float) $order->discount_amount);
        $this->assertSame(120000.0, (float) $order->subtotal);
    }

    public function test_price_is_recomputed_at_checkout_when_campaign_ended_in_the_meantime(): void
    {
        // Khách thấy giá flash lúc xem giỏ, nhưng chiến dịch kết thúc trước khi bấm đặt hàng.
        $this->flashSale->update(['ends_at' => Carbon::now()->subMinute()]);

        $response = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 1],
        ]));

        $response->assertStatus(201);

        $this->assertDatabaseHas('order_items', [
            'unit_price'            => self::REGULAR_PRICE,
            'flash_sale_product_id' => null,
        ]);
        $this->assertSame(0, (int) $this->offer->fresh()->sold_quantity);
    }

    public function test_cancelling_order_releases_flash_quota(): void
    {
        $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 2],
        ]))->assertStatus(201);

        $order = Order::latest('id')->first();
        $this->assertSame(2, (int) $this->offer->fresh()->sold_quantity);

        app(OrderService::class)->cancel($order->uuid, 'Khách hủy đơn (test)');

        $this->assertSame(0, (int) $this->offer->fresh()->sold_quantity);
        $this->assertDatabaseHas('flash_sale_redemptions', [
            'order_item_id' => $order->items->first()->id,
            'status'        => RedemptionStatus::RELEASED->value,
        ]);

        // Release phải idempotent: gọi lại lần hai không được trừ/ cộng suất thêm lần nữa.
        $releasedAgain = app(ReleaseFlashSaleQuotaAction::class)->execute($order->fresh(['items']));

        $this->assertSame(0, $releasedAgain, 'Redemption đã released thì không release lại');
        $this->assertSame(0, (int) $this->offer->fresh()->sold_quantity);
    }

    public function test_cart_api_exposes_flash_price_for_storefront(): void
    {
        $cart = Cart::create(['currency' => 'VND']);

        $this->withHeader('X-Cart-Token', $cart->uuid)
            ->postJson(route('api.v1.cart.items.add'), [
                'product_uuid' => $this->flashProduct->uuid,
                'quantity'     => 1,
            ])
            ->assertStatus(201);

        $this->withHeader('X-Cart-Token', $cart->uuid)
            ->getJson(route('api.v1.cart.index'))
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.unit_price', self::FLASH_PRICE)
            ->assertJsonPath('data.items.0.is_flash_sale', true)
            ->assertJsonPath('data.items.0.flash_sale.regular_price', self::REGULAR_PRICE)
            ->assertJsonPath('data.items.0.flash_sale.remaining_quantity', 5)
            ->assertJsonPath('data.items.0.flash_sale.coupon_eligible', false);
    }

    public function test_api_exposes_active_flash_sales_for_storefront(): void
    {
        $response = $this->getJson(route('api.v1.flash-sales.active'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.flash_sales.0.products.0.flash_price', self::FLASH_PRICE)
            ->assertJsonPath('data.flash_sales.0.products.0.regular_price', self::REGULAR_PRICE)
            ->assertJsonPath('data.flash_sales.0.products.0.remaining_quantity', 5)
            ->assertJsonPath('data.flash_sales.0.products.0.sold_out', false);
    }

    public function test_api_hides_campaign_that_has_not_started_or_sold_out(): void
    {
        // Chiến dịch chưa bắt đầu
        $this->flashSale->update(['starts_at' => Carbon::now()->addHour(), 'ends_at' => Carbon::now()->addHours(3)]);
        $this->getJson(route('api.v1.flash-sales.active'))->assertJsonPath('data.count', 0);

        $this->flashSale->update(['starts_at' => Carbon::now()->subHour(), 'ends_at' => Carbon::now()->addHours(2)]);

        // Sold out vẫn là chiến dịch đang chạy nhưng sản phẩm hết suất
        $this->offer->update(['sold_quantity' => 5]);
        $this->getJson(route('api.v1.flash-sales.active'))
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.flash_sales.0.products.0.sold_out', true)
            ->assertJsonPath('data.flash_sales.0.products.0.remaining_quantity', 0);
    }

    public function test_sold_out_flash_line_falls_back_to_regular_price(): void
    {
        $this->offer->update(['sold_quantity' => 5]);

        $response = $this->postJson(route('api.v1.checkout'), $this->payload([
            ['product_id' => $this->flashProduct->id, 'quantity' => 1],
        ]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('order_items', [
            'unit_price'            => self::REGULAR_PRICE,
            'flash_sale_product_id' => null,
        ]);
    }
}
