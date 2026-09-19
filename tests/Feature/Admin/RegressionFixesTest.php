<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Language;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests cho các lỗi phát hiện qua E2E browser test (09/2026):
 * 1. UpdateMenuRequest/UpdateProductRequest/UpdateProductAttributeRequest dùng sai route param
 *    -> update không persist khi giá trị unique không đổi.
 * 2. Validation yêu cầu tên cho MỌI ngôn ngữ -> chỉ được yêu cầu ngôn ngữ mặc định.
 * 3. Banner: target required nhưng form chỉ submit checkbox khi checked.
 * 4. BrandRepository eager-load relationship không tồn tại (seoMetadata).
 */
class RegressionFixesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);

        Language::create([
            'code' => 'vi', 'name' => 'Tiếng Việt', 'native_name' => 'Tiếng Việt',
            'is_default' => true, 'is_active' => true,
        ]);
        Language::create([
            'code' => 'en', 'name' => 'English', 'native_name' => 'English',
            'is_default' => false, 'is_active' => true,
        ]);
    }

    // ---- 1. Route param fix: update với giá trị unique không đổi phải thành công ----

    public function test_menu_update_persists_when_slug_unchanged(): void
    {
        $menu = Menu::create([
            'uuid' => fake()->uuid(), 'name' => 'Menu chính', 'slug' => 'menu-chinh',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.menus.update', $menu->uuid), [
            'name'   => 'Menu chính UPD',
            'slug'   => 'menu-chinh', // giữ nguyên slug của chính nó
            'items'  => [],
        ]);

        $response->assertRedirect(route('admin.menus.index'));
        $this->assertDatabaseHas('menus', ['id' => $menu->id, 'name' => 'Menu chính UPD']);
    }

    public function test_product_update_persists_when_sku_unchanged(): void
    {
        $product = Product::create([
            'uuid' => fake()->uuid(), 'sku' => 'SKU-001', 'price' => 100000, 'status' => 'draft',
        ]);
        $product->translations()->create(['locale' => 'vi', 'name' => 'SP cũ', 'slug' => 'sp-cu']);

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product->uuid), [
            'sku'           => 'SKU-001', // giữ nguyên SKU của chính nó
            'price'         => 200000,
            'status'        => 'published',
            'translations'  => ['vi' => ['name' => 'SP mới']],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'sku' => 'SKU-001', 'price' => 200000]);
    }

    public function test_product_attribute_update_persists_when_code_unchanged(): void
    {
        $attr = ProductAttribute::create([
            'uuid' => fake()->uuid(), 'code' => 'color', 'type' => 'select', 'display_order' => 0,
        ]);
        $attr->translations()->create(['locale' => 'vi', 'name' => 'Màu sắc']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-attributes.update', $attr->uuid), [
                'code'         => 'color', // giữ nguyên code của chính nó
                'type'         => 'color',
                'translations' => ['vi' => ['name' => 'Màu']],
            ]);

        $response->assertRedirect(route('admin.product-attributes.index'));
        $this->assertDatabaseHas('product_attribute_translations', [
            'attribute_id' => $attr->id, 'name' => 'Màu',
        ]);
    }

    public function test_product_attribute_update_rejects_duplicate_code_of_other(): void
    {
        $other = ProductAttribute::create([
            'uuid' => fake()->uuid(), 'code' => 'size', 'type' => 'select',
        ]);
        $other->translations()->create(['locale' => 'vi', 'name' => 'Kích thước']);

        $attr = ProductAttribute::create([
            'uuid' => fake()->uuid(), 'code' => 'color', 'type' => 'select',
        ]);
        $attr->translations()->create(['locale' => 'vi', 'name' => 'Màu sắc']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-attributes.update', $attr->uuid), [
                'code'         => 'size', // mã của attribute khác
                'type'         => 'select',
                'translations' => ['vi' => ['name' => 'Màu']],
            ]);

        $response->assertSessionHasErrors('code');
    }

    // ---- 2. Multi-locale: chỉ default locale bắt buộc ----

    public function test_banner_create_without_target_defaults_to_self(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'position'     => 'home_slider',
            'display_order' => 1,
            // không gửi target (checkbox bỏ trống như trên UI)
            'translations' => ['vi' => ['title' => 'Khuyến mãi hè']],
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banners', ['target' => '_self', 'position' => 'home_slider']);
        $this->assertDatabaseHas('banner_translations', ['locale' => 'vi', 'title' => 'Khuyến mãi hè']);
    }

    public function test_banner_create_requires_default_locale_title_only(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'position'     => 'sidebar',
            'translations' => [
                'vi' => ['title' => 'Banner VI'],
                'en' => ['title' => ''], // bỏ trống en vẫn hợp lệ
            ],
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banner_translations', ['locale' => 'vi', 'title' => 'Banner VI']);
    }

    public function test_product_attribute_create_with_default_locale_only(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.product-attributes.store'), [
            'code'         => 'material',
            'type'         => 'select',
            'translations' => ['vi' => ['name' => 'Chất liệu']],
            'values'       => [['value' => 'Cotton'], ['value' => 'Len']],
        ]);

        $response->assertRedirect(route('admin.product-attributes.index'));
        $this->assertDatabaseHas('product_attribute_translations', ['locale' => 'vi', 'name' => 'Chất liệu']);
        $this->assertDatabaseHas('product_attribute_values', ['value' => 'Cotton']);
        $this->assertDatabaseHas('product_attribute_values', ['value' => 'Len']);
    }

    public function test_product_create_with_default_locale_only(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'price'        => 99000,
            'status'       => 'published',
            'translations' => ['vi' => ['name' => 'Áo thun']],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('product_translations', ['locale' => 'vi', 'name' => 'Áo thun']);
    }

    public function test_brand_create_with_default_locale_only(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'), [
            'is_active'     => 1,
            'display_order' => 3,
            'translations'  => ['vi' => ['name' => 'Nike']],
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertDatabaseHas('brand_translations', ['locale' => 'vi', 'name' => 'Nike']);
        $this->assertDatabaseHas('brands', ['display_order' => 3, 'is_active' => true]);
    }

    // ---- 3. BrandRepository::findByUuidWithRelations không còn ném lỗi ----

    public function test_admin_can_open_brand_edit_form(): void
    {
        $brand = Brand::create(['is_active' => true, 'display_order' => 1]);
        $brand->translations()->create(['locale' => 'vi', 'name' => 'Adidas', 'slug' => 'adidas']);

        $response = $this->actingAs($this->admin)->get(route('admin.brands.edit', $brand->uuid));

        $response->assertStatus(200);
        $response->assertSee('Adidas');
    }

    // ---- 4. Doc-required repository methods ----

    public function test_brand_repository_get_active_brands(): void
    {
        $active = Brand::create(['is_active' => true, 'display_order' => 2]);
        $active->translations()->create(['locale' => 'vi', 'name' => 'Active', 'slug' => 'active']);
        $inactive = Brand::create(['is_active' => false, 'display_order' => 1]);
        $inactive->translations()->create(['locale' => 'vi', 'name' => 'Inactive', 'slug' => 'inactive']);

        $repo = app(\App\Repositories\Interfaces\BrandRepositoryInterface::class);
        $result = $repo->getActiveBrands();

        $this->assertCount(1, $result);
        $this->assertEquals('Active', $result->first()->name);
    }

    public function test_banner_repository_get_active_by_position(): void
    {
        $b1 = Banner::create([
            'uuid' => fake()->uuid(), 'position' => 'home_slider', 'target' => '_self',
            'display_order' => 1, 'is_active' => true,
        ]);
        $b1->translations()->create(['locale' => 'vi', 'title' => 'Slide 1']);
        Banner::create([
            'uuid' => fake()->uuid(), 'position' => 'sidebar', 'target' => '_self',
            'display_order' => 1, 'is_active' => true,
        ])->translations()->create(['locale' => 'vi', 'title' => 'Sidebar 1']);

        $repo = app(\App\Repositories\Interfaces\BannerRepositoryInterface::class);
        $result = $repo->getActiveByPosition('home_slider');

        $this->assertCount(1, $result);
        $this->assertEquals('Slide 1', $result->first()->translate('vi')->title);
    }

    public function test_menu_repository_get_by_location(): void
    {
        $menu = Menu::create([
            'uuid' => fake()->uuid(), 'name' => 'Menu footer', 'slug' => 'menu-footer',
            'location' => 'footer', 'is_active' => true,
        ]);
        $menu->items()->create([
            'uuid' => fake()->uuid(), 'title' => 'Giới thiệu', 'url' => '/about',
            'target' => '_self', 'type' => 'custom', 'display_order' => 0,
        ]);
        Menu::create([
            'uuid' => fake()->uuid(), 'name' => 'Menu tạm ẩn', 'slug' => 'menu-an',
            'location' => 'footer', 'is_active' => false,
        ]);

        $repo = app(\App\Repositories\Interfaces\MenuRepositoryInterface::class);
        $found = $repo->getByLocation('footer');

        $this->assertNotNull($found);
        $this->assertEquals('Menu footer', $found->name);
        $this->assertCount(1, $found->items);
        $this->assertNull($repo->getByLocation('header'));
    }

    public function test_product_attribute_repository_get_active_with_values(): void
    {
        $attr = ProductAttribute::create([
            'uuid' => fake()->uuid(), 'code' => 'size', 'type' => 'select', 'display_order' => 1,
        ]);
        $attr->translations()->create(['locale' => 'vi', 'name' => 'Kích thước']);
        $attr->values()->create(['uuid' => fake()->uuid(), 'value' => 'L', 'display_order' => 0]);

        $repo = app(\App\Repositories\Interfaces\ProductAttributeRepositoryInterface::class);
        $result = $repo->getActiveWithValues();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->relationLoaded('values'));
        $this->assertEquals('L', $result->first()->values->first()->value);
    }
}
