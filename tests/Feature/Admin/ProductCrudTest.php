<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
                ['image_uuid' => 'https://example.com/mac.png'],
            ],
            'primary_index' => 0,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $payload);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['sku' => 'MACBOOK-M3', 'price' => 28990000]);
        $this->assertDatabaseHas('product_translations', ['name' => 'MacBook Air M3 2024']);
        $this->assertDatabaseHas('product_images', ['image' => 'https://example.com/mac.png', 'is_primary' => true]);
    }

    public function test_admin_can_save_multiple_images_with_primary_flag(): void
    {
        $payload = [
            'sku'            => 'GALLERY-01',
            'price'          => 5000000,
            'status'         => 'published',
            'translations'   => [
                'vi' => ['name' => 'Sản phẩm gallery'],
            ],
            'images'         => [
                ['image_uuid' => 'https://example.com/first.png'],
                ['image_uuid' => 'https://example.com/second.png'],
                ['image_uuid' => 'https://example.com/third.png'],
            ],
            'primary_index'  => 2,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['sku' => 'GALLERY-01']);
        $this->assertSame(3, \DB::table('product_images')->where('image', 'like', 'https://example.com/%')->count());
        $this->assertDatabaseHas('product_images', ['image' => 'https://example.com/third.png', 'is_primary' => true]);
        $this->assertSame(
            1,
            \DB::table('product_images')->where('image', 'https://example.com/first.png')->where('is_primary', false)->count()
        );
    }

    public function test_first_image_becomes_primary_when_none_selected(): void
    {
        $payload = [
            'sku'          => 'GALLERY-02',
            'price'        => 5000000,
            'status'       => 'published',
            'translations' => ['vi' => ['name' => 'Không chọn ảnh đại diện']],
            'images'       => [
                ['image_uuid' => 'https://example.com/a.png'],
                ['image_uuid' => 'https://example.com/b.png'],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('product_images', ['image' => 'https://example.com/a.png', 'is_primary' => true]);
        $this->assertDatabaseHas('product_images', ['image' => 'https://example.com/b.png', 'is_primary' => false]);
    }

    public function test_update_replaces_image_gallery(): void
    {
        $product = Product::create(['sku' => 'GALLERY-03', 'price' => 100000, 'status' => 'published']);
        $product->translations()->create(['locale' => 'vi', 'name' => 'Đổi ảnh', 'slug' => 'doi-anh']);
        $product->images()->create(['image' => 'https://example.com/old.png', 'is_primary' => true, 'display_order' => 0]);

        $payload = [
            'sku'          => 'GALLERY-03',
            'price'        => 100000,
            'status'       => 'published',
            'translations' => ['vi' => ['name' => 'Đổi ảnh']],
            'images'       => [
                ['image_uuid' => 'https://example.com/new1.png'],
                ['image_uuid' => 'https://example.com/new2.png'],
            ],
            'primary_index' => 1,
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product->uuid), $payload)
            ->assertRedirect();

        $this->assertDatabaseMissing('product_images', ['image' => 'https://example.com/old.png']);
        $this->assertSame(2, \DB::table('product_images')->where('product_id', $product->id)->count());
        $this->assertDatabaseHas('product_images', ['image' => 'https://example.com/new2.png', 'is_primary' => true]);
    }

    public function test_product_form_renders_image_gallery(): void
    {
        // Form tạo mới: gallery rỗng, vẫn render đủ nút "Thêm ảnh"
        $this->actingAs($this->admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('image-gallery')
            ->assertSee('Thêm ảnh');

        // Form sửa: gallery có ảnh cũ kèm radio ảnh đại diện
        $product = Product::create(['sku' => 'FORM-01', 'price' => 100, 'status' => 'published']);
        $product->translations()->create(['locale' => 'vi', 'name' => 'Form test', 'slug' => 'form-test']);
        $product->images()->create(['image' => 'https://example.com/form.png', 'is_primary' => true, 'display_order' => 0]);

        $this->actingAs($this->admin)
            ->get(route('admin.products.edit', $product->uuid))
            ->assertOk()
            ->assertSee('https://example.com/form.png')
            ->assertSee('Ảnh đại diện');
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

    public function test_admin_can_filter_products_by_status_tab(): void
    {
        $published = Product::create(['sku' => 'TAB-PUB', 'price' => 100, 'status' => 'published']);
        $published->translations()->create(['locale' => 'vi', 'name' => 'Sản phẩm đã xuất bản', 'slug' => 'sp-xuat-ban']);

        $draft = Product::create(['sku' => 'TAB-DRF', 'price' => 100, 'status' => 'draft']);
        $draft->translations()->create(['locale' => 'vi', 'name' => 'Sản phẩm bản nháp', 'slug' => 'sp-nhap']);

        $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['tab' => 'draft']))
            ->assertOk()
            ->assertSee('Sản phẩm bản nháp')
            ->assertDontSee('Sản phẩm đã xuất bản');
    }

    public function test_slug_is_auto_generated_and_made_unique(): void
    {
        $basePayload = [
            'sku'   => null,
            'price' => 100000,
            'status' => 'published',
            'translations' => [
                'vi' => ['name' => 'Điện thoại thông minh'],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), array_merge($basePayload, ['sku' => 'SLUG-01']))
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), array_merge($basePayload, ['sku' => 'SLUG-02']))
            ->assertRedirect();

        $slugs = \DB::table('product_translations')
            ->where('name', 'Điện thoại thông minh')
            ->pluck('slug')
            ->all();

        $this->assertCount(2, $slugs);
        $this->assertSame('dien-thoai-thong-minh', $slugs[0]);
        $this->assertSame('dien-thoai-thong-minh-1', $slugs[1]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        Product::create(['sku' => 'DUP-SKU', 'price' => 100, 'status' => 'published']);

        $payload = [
            'sku'   => 'DUP-SKU',
            'price' => 100000,
            'status' => 'published',
            'translations' => ['vi' => ['name' => 'Trùng SKU']],
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect()
            ->assertSessionHasErrors('sku');

        $this->assertDatabaseMissing('product_translations', ['name' => 'Trùng SKU']);
    }

    public function test_editor_without_products_create_is_forbidden_from_storing(): void
    {
        $editor = $this->makeEditor(['products.update']);

        $payload = [
            'sku'   => 'EDITOR-01',
            'price' => 100000,
            'status' => 'published',
            'translations' => ['vi' => ['name' => 'Sản phẩm editor']],
        ];

        $this->actingAs($editor)
            ->post(route('admin.products.store'), $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('products', ['sku' => 'EDITOR-01']);
    }

    public function test_editor_with_products_update_can_update(): void
    {
        $editor = $this->makeEditor(['products.update']);

        $product = Product::create(['sku' => 'EDITOR-UPD', 'price' => 100, 'status' => 'draft']);
        $product->translations()->create(['locale' => 'vi', 'name' => 'Tên cũ', 'slug' => 'ten-cu']);

        $this->actingAs($editor)
            ->put(route('admin.products.update', $product->uuid), [
                'sku'   => 'EDITOR-UPD',
                'price' => 200,
                'status' => 'published',
                'translations' => ['vi' => ['name' => 'Tên mới editor']],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('product_translations', ['name' => 'Tên mới editor']);
    }

    /**
     * Tạo user thuộc role `editor` với đúng các permission truyền vào
     * (luôn kèm admin.access để qua được AdminMiddleware).
     */
    private function makeEditor(array $permissions): User
    {
        $names = array_merge(['admin.access'], $permissions);

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $role->syncPermissions($names);

        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole($role);

        return $user;
    }
}
