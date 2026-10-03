<?php

namespace Tests\Unit\Services;

use App\Events\Product\LowStockThresholdReached;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Services\Shared\Product\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * P3.2 — StockService: atomic stock operations (groundwork cho Cart/Order).
 */
class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Language::create([
            'code' => 'vi',
            'name' => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default' => true,
            'is_active' => true,
        ]);
        $this->service = app(StockService::class);
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'stock_quantity' => 5,
            'track_inventory' => true,
            'allow_backorder' => false,
        ], $overrides));
    }

    private function createVariant(Product $product, int $stock): ProductVariant
    {
        $attribute = ProductAttribute::create(['code' => 'size', 'type' => 'button']);
        $value = $attribute->values()->create(['value' => 'M']);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'VAR-'.uniqid(),
            'price' => $product->price,
            'stock_quantity' => $stock,
        ]);
        $variant->attributeValues()->attach($value->id);

        return $variant;
    }

    /** @test */
    public function it_decrements_stock_successfully(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5]);

        $result = $this->service->decrementStock($product->id, null, 3);

        $this->assertTrue($result);
        $this->assertSame(2, (int) $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_rejects_insufficient_stock(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5]);

        $result = $this->service->decrementStock($product->id, null, 10);

        $this->assertFalse($result, 'Không đủ hàng phải trả false (chống oversell).');
        $this->assertSame(5, (int) $product->fresh()->stock_quantity, 'Stock không đổi khi từ chối.');
    }

    /** @test */
    public function it_skips_decrement_when_track_inventory_disabled(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5, 'track_inventory' => false]);

        $result = $this->service->decrementStock($product->id, null, 100);

        $this->assertTrue($result, 'Không theo dõi tồn kho → cho qua.');
        $this->assertSame(5, (int) $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_allows_backorder_to_go_negative(): void
    {
        $product = $this->createProduct(['stock_quantity' => 0, 'allow_backorder' => true]);

        $result = $this->service->decrementStock($product->id, null, 1);

        $this->assertTrue($result);
        $this->assertSame(-1, (int) $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_decrements_variant_stock(): void
    {
        $product = $this->createProduct(['stock_quantity' => 10]);
        $variant = $this->createVariant($product, 4);

        $result = $this->service->decrementStock($product->id, $variant->id, 3);

        $this->assertTrue($result);
        $this->assertSame(1, (int) $variant->fresh()->stock_quantity);
        $this->assertSame(10, (int) $product->fresh()->stock_quantity, 'Stock product không đổi khi trừ theo variant.');
    }

    /** @test */
    public function it_rejects_variant_decrement_when_backorder_disabled(): void
    {
        $product = $this->createProduct(['stock_quantity' => 10, 'allow_backorder' => false]);
        $variant = $this->createVariant($product, 1);

        $result = $this->service->decrementStock($product->id, $variant->id, 5);

        $this->assertFalse($result);
        $this->assertSame(1, (int) $variant->fresh()->stock_quantity);
    }

    /** @test */
    public function it_increments_stock_back(): void
    {
        $product = $this->createProduct(['stock_quantity' => 2]);

        $result = $this->service->incrementStock($product->id, null, 3);

        $this->assertTrue($result);
        $this->assertSame(5, (int) $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_dispatches_low_stock_event_when_threshold_reached(): void
    {
        Event::fake();

        $product = $this->createProduct([
            'stock_quantity' => 5,
            'low_stock_threshold' => 4,
        ]);

        $this->service->decrementStock($product->id, null, 1);

        Event::assertDispatched(LowStockThresholdReached::class, function ($event) use ($product) {
            return $event->product->id === $product->id
                && $event->variant === null
                && $event->currentStock === 4;
        });
    }

    /** @test */
    public function it_does_not_dispatch_low_stock_event_above_threshold(): void
    {
        Event::fake();

        $product = $this->createProduct([
            'stock_quantity' => 10,
            'low_stock_threshold' => 3,
        ]);

        $this->service->decrementStock($product->id, null, 1);

        Event::assertNotDispatched(LowStockThresholdReached::class);
    }

    /** @test */
    public function it_detects_low_stock(): void
    {
        $product = $this->createProduct([
            'stock_quantity' => 2,
            'low_stock_threshold' => 5,
        ]);

        $this->assertTrue($this->service->isLowStock($product->id));

        $product2 = $this->createProduct([
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        $this->assertFalse($this->service->isLowStock($product2->id));
    }
}
