<?php

namespace Tests\Unit\Observers;

use App\Models\Language;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * P3.1 — ProductObserver invalidate cache khi product thay đổi.
 */
class ProductObserverTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepositoryInterface $repository;

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
        $this->repository = app(ProductRepositoryInterface::class);
    }

    /** @test */
    public function it_forgets_product_cache_on_save(): void
    {
        $product = Product::factory()->create();

        Cache::forever('product:'.$product->uuid, 'cached');
        Cache::forever('products:featured', 'cached');

        $product->update(['price' => 999000]);

        $this->assertTrue(Cache::missing('product:'.$product->uuid));
        $this->assertTrue(Cache::missing('products:featured'));
    }

    /** @test */
    public function it_forgets_product_cache_on_delete(): void
    {
        $product = Product::factory()->create();

        Cache::forever('product:'.$product->uuid, 'cached');
        Cache::forever('products:featured', 'cached');

        $product->delete();

        $this->assertTrue(Cache::missing('product:'.$product->uuid));
        $this->assertTrue(Cache::missing('products:featured'));
    }

    /** @test */
    public function it_forgets_featured_cache_on_bulk_delete(): void
    {
        $product = Product::factory()->create();

        Cache::forever('products:featured', 'cached');

        // Bulk path không qua observer → repository clearCache phải được gọi.
        $this->repository->clearCache($product);

        $this->assertTrue(Cache::missing('products:featured'));
    }
}
