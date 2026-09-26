<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regression test cho các bug đã sửa trong đợt "Product module fix 09/2026":
 * #1 update sản phẩm giữ nguyên SKU biến thể không bị reject
 * #2 dropdown Category hiển thị tên (translated_name)
 * #4 custom attribute trùng code → 422 thân thiện (không 500)
 * #4 custom attribute color_code độc hại bị reject
 * #6 ownership scoping: không attach attribute/value của product khác
 * #7 read route yêu cầu products.view
 */
class ProductModuleFixTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    private Brand $brand;

    private ProductAttribute $color;

    private array $colorValueIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);

        Language::create([
            'code' => 'vi',
            'name' => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->category = Category::create(['is_active' => true]);
        $this->category->translations()->create(['locale' => 'vi', 'name' => 'Điện thoại', 'slug' => 'dien-thoai']);

        $this->brand = Brand::create(['is_active' => true]);
        $this->brand->translations()->create(['locale' => 'vi', 'name' => 'Apple', 'slug' => 'apple']);

        $this->color = ProductAttribute::create(['code' => 'color', 'type' => 'color']);
        $this->color->translations()->create(['locale' => 'vi', 'name' => 'Màu sắc']);
        foreach (['Đỏ' => '#ff0000', 'Xanh' => '#0000ff'] as $name => $code) {
            $this->colorValueIds[$name] = $this->color->values()->create([
                'value' => $name,
                'color_code' => $code,
            ])->id;
        }
    }

    private function createProduct(string $sku): Product
    {
        $product = Product::create([
            'sku' => $sku,
            'price' => 100000,
            'stock_quantity' => 10,
            'status' => 'published',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);
        $product->translations()->create(['locale' => 'vi', 'name' => 'Áo thun', 'slug' => 'ao-thun']);

        return $product;
    }

    private function matrix(): array
    {
        return [
            [
                'attribute_id' => $this->color->id,
                'is_variation' => true,
                'value_ids' => array_values($this->colorValueIds),
            ],
        ];
    }

    private function basePayload(): array
    {
        return [
            'sku' => 'FIX-01',
            'price' => 100000,
            'status' => 'published',
            'translations' => ['vi' => ['name' => 'Áo thun']],
            'attributes' => $this->matrix(),
        ];
    }

    // ── #1: update giữ nguyên SKU biến thể ──────────────────────────────────────

    public function test_update_product_with_unchanged_variant_sku_succeeds(): void
    {
        $product = $this->createProduct('FIX-01');
        $key = (string) $this->colorValueIds['Đỏ'];

        // Tạo variant có SKU
        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product->uuid), $this->basePayload() + [
                'variants' => [$key => ['sku' => 'KEEP-SKU', 'price' => 500]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'KEEP-SKU']);

        // Submit lại giữ nguyên SKU → phải pass và cập nhật giá
        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product->uuid), $this->basePayload() + [
                'variants' => [$key => ['sku' => 'KEEP-SKU', 'price' => 600]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'KEEP-SKU',
            'price' => 600,
        ]);
    }

    public function test_update_product_variant_sku_conflict_with_other_product_rejected(): void
    {
        $productA = $this->createProduct('FIX-A');
        $productB = $this->createProduct('FIX-B');
        $key = (string) $this->colorValueIds['Đỏ'];

        // Product A giữ variant SKU TAKEN-SKU
        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $productA->uuid), [
                'sku' => 'FIX-A',
                'price' => 100000,
                'status' => 'published',
                'translations' => ['vi' => ['name' => 'Áo thun']],
                'attributes' => $this->matrix(),
                'variants' => [$key => ['sku' => 'TAKEN-SKU']],
            ])
            ->assertRedirect();

        // Product B dùng lại SKU đó → phải reject
        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $productB->uuid), [
                'sku' => 'FIX-B',
                'price' => 100000,
                'status' => 'published',
                'translations' => ['vi' => ['name' => 'Áo thun']],
                'attributes' => $this->matrix(),
                'variants' => [$key => ['sku' => 'TAKEN-SKU']],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('variants.'.$key.'.sku');

        $this->assertDatabaseMissing('product_variants', ['product_id' => $productB->id, 'sku' => 'TAKEN-SKU']);
    }

    // ── #2: dropdown Category hiển thị tên ─────────────────────────────────────

    public function test_products_index_shows_category_name_in_filter_dropdown(): void
    {
        $this->createProduct('FIX-DROP-01');

        $this->actingAs($this->admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Điện thoại');
    }

    public function test_product_create_form_shows_category_and_brand_names(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Điện thoại')
            ->assertSee('Apple');
    }

    public function test_products_index_shows_category_name_in_table(): void
    {
        $this->createProduct('FIX-DROP-02');

        $this->actingAs($this->admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Điện thoại');
    }

    // ── #4: custom attribute code trùng + color_code độc hại ───────────────────

    public function test_custom_attribute_duplicate_code_rejected_with_validation_error(): void
    {
        $product = $this->createProduct('FIX-CODE-01');

        // Code đã tồn tại (catalog attribute 'color')
        $this->actingAs($this->admin)
            ->postJson(route('admin.products.attributes.store', $product->uuid), [
                'code' => 'color',
                'translations' => ['vi' => ['name' => 'Màu']],
                'values' => [['value' => 'Đỏ']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->assertDatabaseMissing('product_attributes', [
            'product_id' => $product->id,
            'code' => 'color',
        ]);
    }

    public function test_custom_attribute_malicious_color_code_rejected(): void
    {
        $product = $this->createProduct('FIX-COLOR-01');

        $this->actingAs($this->admin)
            ->postJson(route('admin.products.attributes.store', $product->uuid), [
                'translations' => ['vi' => ['name' => 'Chất liệu']],
                'values' => [
                    ['value' => 'Cotton', 'color_code' => 'red"></span><img src=x onerror=alert(1)>'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('values.0.color_code');

        $this->assertDatabaseMissing('product_attributes', ['product_id' => $product->id]);
    }

    // ── #6: ownership scoping ───────────────────────────────────────────────────

    public function test_cross_product_custom_attribute_attachment_rejected(): void
    {
        $productA = $this->createProduct('FIX-OWN-A');
        $productB = $this->createProduct('FIX-OWN-B');

        // Custom attribute của product A
        $custom = ProductAttribute::create([
            'code' => 'rieng_a',
            'type' => 'select',
            'product_id' => $productA->id,
        ]);
        $custom->translations()->create(['locale' => 'vi', 'name' => 'Riêng A']);
        $valueId = $custom->values()->create(['value' => 'X'])->id;

        // Product B cố attach attribute của A → reject
        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $productB->uuid), [
                'sku' => 'FIX-OWN-B',
                'price' => 100000,
                'status' => 'published',
                'translations' => ['vi' => ['name' => 'Áo thun']],
                'attributes' => [
                    [
                        'attribute_id' => $custom->id,
                        'is_variation' => true,
                        'value_ids' => [$valueId],
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('attributes.0.attribute_id');

        $this->assertDatabaseMissing('product_attribute', [
            'product_id' => $productB->id,
            'attribute_id' => $custom->id,
        ]);
    }

    public function test_catalog_attribute_attachment_allowed_on_update(): void
    {
        $product = $this->createProduct('FIX-OWN-OK');

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product->uuid), $this->basePayload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_attribute', [
            'product_id' => $product->id,
            'attribute_id' => $this->color->id,
        ]);
    }

    public function test_value_belonging_to_other_attribute_rejected(): void
    {
        $product = $this->createProduct('FIX-OWN-VAL');

        // Tạo attribute khác (size) với 1 value
        $size = ProductAttribute::create(['code' => 'size', 'type' => 'button']);
        $size->translations()->create(['locale' => 'vi', 'name' => 'Kích thước']);
        $sizeValueId = $size->values()->create(['value' => 'M'])->id;

        // Attach color attribute nhưng truyền value_id của size → reject
        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product->uuid), [
                'sku' => 'FIX-OWN-VAL',
                'price' => 100000,
                'status' => 'published',
                'translations' => ['vi' => ['name' => 'Áo thun']],
                'attributes' => [
                    [
                        'attribute_id' => $this->color->id,
                        'is_variation' => false,
                        'value_ids' => [$sizeValueId],
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('attributes.0.value_ids');

        $this->assertDatabaseMissing('product_attribute_value', [
            'product_id' => $product->id,
            'attribute_value_id' => $sizeValueId,
        ]);
    }

    // ── #7: read authorization ─────────────────────────────────────────────────

    public function test_editor_without_products_view_forbidden_from_read_routes(): void
    {
        $editor = $this->makeEditor(['products.create']);
        $product = $this->createProduct('FIX-AUTH-01');

        $this->actingAs($editor)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.products.create'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.products.edit', $product->uuid))->assertForbidden();
    }

    public function test_editor_with_products_view_can_access_read_routes(): void
    {
        $editor = $this->makeEditor(['products.view']);
        $product = $this->createProduct('FIX-AUTH-02');

        $this->actingAs($editor)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.products.create'))->assertOk();
        $this->actingAs($editor)->get(route('admin.products.edit', $product->uuid))->assertOk();
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
