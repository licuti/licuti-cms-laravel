<?php

namespace Tests\Unit\Services;

use App\Contracts\Pricing\PriceResolverInterface;
use App\Contracts\Shipping\ShippingFeeCalculatorInterface;
use App\Core\Enums\CouponType;
use App\DTOs\Cart\AppliedCoupon;
use App\DTOs\Cart\CartSummary;
use App\DTOs\Coupon\CouponSubject;
use App\DTOs\Pricing\PriceInfo;
use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductVariantRepositoryInterface;
use App\Services\Admin\Coupon\CouponValidationService;
use App\Services\Shared\Cart\CartPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CartPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartPricingService $service;
    private $productRepoMock;
    private $variantRepoMock;
    private $priceResolverMock;
    private $shippingCalcMock;
    private $couponValidatorMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepoMock = Mockery::mock(ProductRepositoryInterface::class);
        $this->variantRepoMock = Mockery::mock(ProductVariantRepositoryInterface::class);
        $this->priceResolverMock = Mockery::mock(PriceResolverInterface::class);
        $this->shippingCalcMock = Mockery::mock(ShippingFeeCalculatorInterface::class);
        $this->couponValidatorMock = Mockery::mock(CouponValidationService::class);

        $this->service = new CartPricingService(
            $this->productRepoMock,
            $this->variantRepoMock,
            $this->priceResolverMock,
            $this->shippingCalcMock,
            $this->couponValidatorMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_summarize_empty_cart_returns_zero_totals(): void
    {
        $cart = new Cart([
            'uuid'   => 'cart-test-uuid',
            'status' => CartStatus::ACTIVE,
        ]);
        $cart->setRelation('items', collect());

        $this->productRepoMock->shouldReceive('getForCart')->never();
        $this->variantRepoMock->shouldReceive('getForCart')->never();
        $this->shippingCalcMock->shouldReceive('calculate')->never();

        $summary = $this->service->summarize($cart);

        $this->assertInstanceOf(CartSummary::class, $summary);
        $this->assertEquals(0.0, $summary->subtotal);
        $this->assertEquals(0.0, $summary->grandTotal);
        $this->assertCount(0, $summary->lines);
        $this->assertFalse($summary->hasIssues);
    }

    public function test_summarize_computes_subtotal_and_detects_price_drift(): void
    {
        $product = Product::create([
            'sku'             => 'SKU-01',
            'price'           => 100000,
            'is_active'       => true,
            'stock_quantity'  => 10,
            'track_inventory' => true,
        ]);

        $cart = Cart::create(['status' => CartStatus::ACTIVE]);
        $item = CartItem::create([
            'cart_id'              => $cart->id,
            'product_id'           => $product->id,
            'product_variant_id'   => null,
            'line_key'             => "p{$product->id}-v0",
            'quantity'             => 2,
            'unit_price_snapshot'  => 100000,
        ]);
        $cart->setRelation('items', collect([$item]));

        $this->productRepoMock->shouldReceive('getForCart')
            ->once()
            ->with([$product->id])
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([$product]));

        // Giá hiện tại đã tăng lên 120.000 so với snapshot 100.000
        $this->priceResolverMock->shouldReceive('resolve')
            ->once()
            ->with($product, null)
            ->andReturn(new PriceInfo(unitPrice: 120000.0, comparePrice: null, isOnSale: false));

        $this->shippingCalcMock->shouldReceive('calculate')
            ->once()
            ->andReturn(20000.0);

        $summary = $this->service->summarize($cart);

        $this->assertEquals(240000.0, $summary->subtotal); // 120k * 2
        $this->assertEquals(20000.0, $summary->shippingFee);
        $this->assertEquals(260000.0, $summary->grandTotal); // 240k + 20k
        $this->assertTrue($summary->lines[0]->priceChanged);
        $this->assertTrue($summary->hasIssues);
        $this->assertNotEmpty($summary->issues);
        $this->assertStringContainsString('đã thay đổi từ', $summary->issues[0]);
    }

    public function test_summarize_applies_coupon_successfully(): void
    {
        $product = Product::create([
            'sku'             => 'SKU-02',
            'price'           => 200000,
            'is_active'       => true,
            'stock_quantity'  => 5,
            'track_inventory' => true,
        ]);

        $coupon = Coupon::create([
            'code'      => 'GIAM50K',
            'type'      => CouponType::FIXED,
            'value'     => 50000,
            'is_active' => true,
        ]);

        $cart = Cart::create(['status' => CartStatus::ACTIVE, 'coupon_id' => $coupon->id]);
        $item = CartItem::create([
            'cart_id'              => $cart->id,
            'product_id'           => $product->id,
            'line_key'             => "p{$product->id}-v0",
            'quantity'             => 1,
            'unit_price_snapshot'  => 200000,
        ]);
        $cart->setRelation('items', collect([$item]));
        $cart->setRelation('coupon', $coupon);

        $this->productRepoMock->shouldReceive('getForCart')
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([$product]));

        $this->priceResolverMock->shouldReceive('resolve')
            ->andReturn(new PriceInfo(unitPrice: 200000.0, comparePrice: null, isOnSale: false));

        $appliedCoupon = new AppliedCoupon(
            coupon: $coupon,
            code: 'GIAM50K',
            type: 'fixed',
            discountAmount: 50000.0,
            freeShipping: false
        );

        $this->couponValidatorMock->shouldReceive('validateForCart')
            ->once()
            ->with('GIAM50K', 200000.0, Mockery::type(CouponSubject::class), false)
            ->andReturn($appliedCoupon);

        $this->shippingCalcMock->shouldReceive('calculate')
            ->once()
            ->andReturn(0.0);

        $summary = $this->service->summarize($cart);

        $this->assertEquals(200000.0, $summary->subtotal);
        $this->assertEquals(50000.0, $summary->discountAmount);
        $this->assertEquals(150000.0, $summary->grandTotal);
        $this->assertNotNull($summary->coupon);
    }
}
