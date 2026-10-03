<?php

namespace Tests\Unit\Repositories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\ProductAttribute;
use App\Repositories\Eloquent\ProductAttributeRepository;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * P3.3 — Cache các catalog lookup ít đổi (attribute / category / brand select).
 */
class ProductAttributeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductAttributeRepository $repo;

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
        $this->repo = app(ProductAttributeRepository::class);
    }

    /** @test */
    public function it_caches_get_active_with_values(): void
    {
        $attribute = ProductAttribute::create(['code' => 'color', 'type' => 'color']);
        $attribute->translations()->create(['locale' => 'vi', 'name' => 'Màu sắc']);
        $attribute->values()->create(['value' => 'Đỏ']);

        // Observer đã fire lúc create → xóa cache trước khi đo.
        Cache::flush();

        \DB::enableQueryLog();

        $this->repo->getActiveWithValues();
        $queriesAfterFirst = count(\DB::getQueryLog());

        $this->repo->getActiveWithValues();

        \DB::disableQueryLog();

        $this->assertSame(0, count(\DB::getQueryLog()) - $queriesAfterFirst, 'Lần gọi thứ 2 phải dùng cache (0 query).');
        $this->assertCount(1, $this->repo->getActiveWithValues());
    }

    /** @test */
    public function it_caches_get_available_for_product(): void
    {
        $attribute = ProductAttribute::create(['code' => 'size', 'type' => 'button']);
        $attribute->translations()->create(['locale' => 'vi', 'name' => 'Kích thước']);

        Cache::flush();

        \DB::enableQueryLog();

        $this->repo->getAvailableForProduct(1);
        $queriesAfterFirst = count(\DB::getQueryLog());

        $this->repo->getAvailableForProduct(1);

        \DB::disableQueryLog();

        $this->assertSame(0, count(\DB::getQueryLog()) - $queriesAfterFirst, 'Lần gọi thứ 2 phải dùng cache (0 query).');
    }

    /** @test */
    public function it_invalidates_cache_when_attribute_saved(): void
    {
        $this->repo->getActiveWithValues();
        $this->assertTrue(Cache::has('product_attributes:with_values'));

        // Observer fire khi save attribute
        $attribute = ProductAttribute::create(['code' => 'material', 'type' => 'select']);

        $this->assertTrue(Cache::missing('product_attributes:with_values'));
    }

    /** @test */
    public function it_invalidates_cache_when_attribute_deleted(): void
    {
        $attribute = ProductAttribute::create(['code' => 'material2', 'type' => 'select']);

        $this->repo->getActiveWithValues();
        $this->assertTrue(Cache::has('product_attributes:with_values'));

        $attribute->delete();

        $this->assertTrue(Cache::missing('product_attributes:with_values'));
    }

    /** @test */
    public function it_invalidates_cache_when_value_created_via_repository(): void
    {
        $attribute = ProductAttribute::create(['code' => 'material3', 'type' => 'select']);

        $this->repo->getActiveWithValues();
        $this->assertTrue(Cache::has('product_attributes:with_values'));

        $this->repo->createValue($attribute->id, 'Cotton');

        $this->assertTrue(Cache::missing('product_attributes:with_values'));
    }

    /** @test */
    public function it_invalidates_per_product_cache_on_attribute_change(): void
    {
        $this->repo->getAvailableForProduct(42);
        $versionBefore = Cache::get('product_attributes:version', 0);

        ProductAttribute::create(['code' => 'newattr', 'type' => 'select']);

        $versionAfter = Cache::get('product_attributes:version', 0);

        $this->assertGreaterThan($versionBefore, $versionAfter, 'Version tăng → per-product keys cũ bị vô hiệu hóa.');
    }

    /** @test */
    public function it_caches_category_and_brand_select(): void
    {
        $category = Category::create(['is_active' => true]);
        $category->translations()->create(['locale' => 'vi', 'name' => 'Điện thoại', 'slug' => 'dien-thoai']);
        $brand = Brand::create(['is_active' => true]);
        $brand->translations()->create(['locale' => 'vi', 'name' => 'Apple', 'slug' => 'apple']);

        $categoryRepo = app(CategoryRepositoryInterface::class);
        $brandRepo = app(BrandRepositoryInterface::class);

        Cache::flush();

        \DB::enableQueryLog();

        $categoryRepo->getForSelect();
        $queriesAfterFirst = count(\DB::getQueryLog());
        $categoryRepo->getForSelect();
        $this->assertSame(0, count(\DB::getQueryLog()) - $queriesAfterFirst);

        $brandRepo->getForSelect();
        $queriesAfterFirst = count(\DB::getQueryLog());
        $brandRepo->getForSelect();
        $this->assertSame(0, count(\DB::getQueryLog()) - $queriesAfterFirst);

        \DB::disableQueryLog();

        // Invalidate khi category/brand thay đổi
        $this->assertTrue(Cache::has('categories:select'));
        $this->assertTrue(Cache::has('brands:select'));

        $category->update(['display_order' => 5]);
        $brand->update(['display_order' => 5]);

        $this->assertTrue(Cache::missing('categories:select'));
        $this->assertTrue(Cache::missing('brands:select'));
    }
}
