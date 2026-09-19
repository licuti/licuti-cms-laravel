<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);

        Language::create([
            'code'        => 'vi',
            'name'        => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default'  => true,
            'is_active'   => true,
        ]);

        $this->category = Category::create(['is_active' => true]);
        $this->category->translations()->create(['locale' => 'vi', 'name' => 'Điện thoại', 'slug' => 'dien-thoai']);

        $this->brand = Brand::create(['is_active' => true]);
        $this->brand->translations()->create(['locale' => 'vi', 'name' => 'Apple', 'slug' => 'apple']);
    }

    public function test_guest_cannot_access_products_index(): void
    {
        $response = $this->get(route('admin.products.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_products_index(): void
    {
        $product = Product::create([
            'sku'            => 'IP15-128',
            'price'          => 22000000,
            'stock_quantity' => 15,
            'status'         => 'published',
            'category_id'    => $this->category->id,
            'brand_id'       => $this->brand->id,
        ]);
        $product->translations()->create([
            'locale' => 'vi',
            'name'   => 'iPhone 15 128GB',
            'slug'   => 'iphone-15-128gb',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertSee('iPhone 15 128GB');
        $response->assertSee('IP15-128');
    }

    public function test_admin_can_create_product(): void
    {
        $payload = [
            'sku'            => 'MACBOOK-M3',
            'price'          => 28990000,
            'compare_price'  => 31990000,
            'stock_quantity' => 20,
            'status'         => 'published',
            'category_id'    => $this->category->id,
            'brand_id'       => $this->brand->id,
            'translations'   => [
                'vi' => [
                    'name'              => 'MacBook Air M3 2024',
                    'short_description' => 'Mô tả ngắn gọn laptop',
                    'description'       => '<p>Chi tiết sản phẩm</p>',
                ],
            ],
            'images' => [
                ['image' => 'https://example.com/mac.png', 'is_primary' => 1],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $payload);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['sku' => 'MACBOOK-M3', 'price' => 28990000]);
        $this->assertDatabaseHas('product_translations', ['name' => 'MacBook Air M3 2024']);
        $this->assertDatabaseHas('product_images', ['image' => 'https://example.com/mac.png']);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'sku'            => 'OLD-SKU',
            'price'          => 1000000,
            'stock_quantity' => 5,
            'status'         => 'draft',
        ]);
        $product->translations()->create([
            'locale' => 'vi',
            'name'   => 'Tên cũ',
            'slug'   => 'ten-cu',
        ]);

        $payload = [
            'sku'            => 'NEW-SKU-99',
            'price'          => 1500000,
            'stock_quantity' => 50,
            'status'         => 'published',
            'translations'   => [
                'vi' => [
                    'name' => 'Tên mới sau cập nhật',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product->uuid), $payload);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['sku' => 'NEW-SKU-99', 'price' => 1500000, 'status' => 'published']);
        $this->assertDatabaseHas('product_translations', ['name' => 'Tên mới sau cập nhật']);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::create([
            'sku'    => 'DELETE-ME',
            'price'  => 50000,
            'status' => 'archived',
        ]);
        $product->translations()->create([
            'locale' => 'vi',
            'name'   => 'Sản phẩm sắp xóa',
            'slug'   => 'san-pham-sap-xoa',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.products.destroy', $product->uuid));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
