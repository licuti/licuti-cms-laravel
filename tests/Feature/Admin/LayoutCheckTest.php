<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Tag;
use App\Models\User;
use App\Services\Admin\Product\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_dump_form_html(): void
    {
        Language::firstOrCreate([
            'code' => 'vi', 'name' => 'Tiếng Việt', 'native_name' => 'Tiếng Việt',
            'is_default' => true, 'is_active' => true,
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $html = $this->actingAs($admin)->get(route('admin.products.create'))->getContent();
        file_put_contents(storage_path('layout-check-create.html'), $html);

        // Edit page với product có dữ liệu đầy đủ
        $product = Product::create([
            'sku' => 'LAYOUT-01', 'price' => 28990000, 'compare_price' => 34990000,
            'cost_price' => 22000000, 'stock_quantity' => 50, 'status' => 'published',
            'product_type' => 'physical', 'weight' => 1.5, 'length' => 30, 'width' => 20, 'height' => 10,
            'shipping_fee' => 30000, 'tax_rate' => 10,
        ]);
        $product->translations()->create(['locale' => 'vi', 'name' => 'Layout test', 'slug' => 'layout-test']);

        $color = ProductAttribute::create(['code' => 'color', 'type' => 'color']);
        $color->translations()->create(['locale' => 'vi', 'name' => 'Màu sắc']);
        $red = $color->values()->create(['value' => 'Đỏ', 'color_code' => '#ff0000']);
        $blue = $color->values()->create(['value' => 'Xanh', 'color_code' => '#0000ff']);

        $service = app(ProductService::class);
        $matrix = [
            ['attribute_id' => $color->id, 'is_variation' => true, 'value_ids' => [$red->id, $blue->id]],
        ];
        $service->syncAttributes($product, $matrix);
        $service->generateVariants($product, $matrix, []);

        $product->images()->create(['image' => 'https://example.com/p.png', 'display_order' => 0]);
        $tag = Tag::create(['name' => 'Test', 'slug' => 'test']);
        $product->tags()->sync([$tag->id]);

        $html = $this->actingAs($admin)->get(route('admin.products.edit', $product->uuid))->getContent();
        file_put_contents(storage_path('layout-check-edit.html'), $html);

        $this->assertNotEmpty($html);
    }
}
