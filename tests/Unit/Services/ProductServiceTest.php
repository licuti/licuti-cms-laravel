<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\Tag;
use App\Services\Admin\Product\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLanguages;
use Tests\TestCase;

/**
 * Test cho ProductService — lớp business logic chính của module Product.
 *
 * Bao phủ:
 *  - getTabs() đếm đúng theo trạng thái (P1.1)
 *  - syncTags() tái sử dụng tag theo slug, không tạo trùng (P1.2)
 */
class ProductServiceTest extends TestCase
{
    use RefreshDatabase, WithLanguages;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpLanguages();
        $this->service = app(ProductService::class);
    }

    /** @test */
    public function it_returns_tabs_with_correct_counts(): void
    {
        Product::factory()->count(3)->published()->create();
        Product::factory()->count(2)->draft()->create();
        Product::factory()->count(1)->archived()->create();

        $tabs = $this->service->getTabs();

        $this->assertCount(4, $tabs); // 'all' + 3 statuses
        $this->assertSame('all', $tabs[0]['key']);
        $this->assertSame(6, $tabs[0]['count']);
        $this->assertSame(3, $tabs[1]['count']); // published
        $this->assertSame(2, $tabs[2]['count']); // draft
        $this->assertSame(1, $tabs[3]['count']); // archived
    }

    /** @test */
    public function it_returns_zero_counts_when_no_products(): void
    {
        $tabs = $this->service->getTabs();

        $this->assertCount(4, $tabs);
        $this->assertSame(0, $tabs[0]['count']);
    }

    /**
     * P1.2 — syncTags tái sử dụng tag có sẵn theo slug, không tạo bản ghi trùng.
     */
    /** @test */
    public function it_sync_tags_reuses_existing_tag_by_slug(): void
    {
        $existing = Tag::create(['name' => 'Hàng Hot', 'slug' => 'hang-hot']);
        $product = Product::factory()->create();

        $this->service->syncTags($product, [
            'ids' => [],
            'text' => ['Hàng Hot'], // trùng slug tag đã có
        ]);

        $this->assertSame(1, Tag::where('slug', 'hang-hot')->count(), 'Không tạo tag mới khi slug đã tồn tại.');
        $this->assertSame(1, $product->tags()->count());
        $this->assertTrue($product->tags->contains($existing));
    }

    /** @test */
    public function it_sync_tags_creates_new_tag_when_slug_is_new(): void
    {
        $product = Product::factory()->create();

        $this->service->syncTags($product, [
            'ids' => [],
            'text' => ['Thương hiệu mới'],
        ]);

        $this->assertDatabaseHas('tags', ['name' => 'Thương hiệu mới', 'slug' => 'thuong-hieu-moi']);
        $this->assertSame(1, $product->tags()->count());
    }
}
