<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Services\Admin\Product\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductAttributeVariantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private ProductAttribute $color;
    private ProductAttribute $size;
    private array $colorValueIds = [];
    private array $sizeValueIds = [];

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

        $this->color = ProductAttribute::create(['code' => 'color', 'type' => 'color']);
        $this->color->translations()->create(['locale' => 'vi', 'name' => 'Màu sắc']);
        foreach (['Đỏ' => '#ff0000', 'Xanh' => '#0000ff'] as $name => $code) {
            $value = $this->color->values()->create(['value' => $name, 'color_code' => $code]);
            $this->colorValueIds[$name] = $value->id;
        }

        $this->size = ProductAttribute::create(['code' => 'size', 'type' => 'button']);
        $this->size->translations()->create(['locale' => 'vi', 'name' => 'Kích thước']);
        foreach (['S', 'M'] as $name) {
            $value = $this->size->values()->create(['value' => $name]);
            $this->sizeValueIds[$name] = $value->id;
        }
    }

    private function createProduct(string $sku = 'VARIANT-01'): Product
    {
        $product = Product::create([
            'sku'            => $sku,
            'price'          => 100000,
            'stock_quantity' => 10,
            'status'         => 'published',
        ]);
        $product->translations()->create(['locale' => 'vi', 'name' => 'Áo thun', 'slug' => 'ao-thun']);

        return $product;
    }

    private function matrix(bool $sizeVariation = true): array
    {
        return [
            [
                'attribute_id' => $this->color->id,
                'is_variation' => true,
                'value_ids'    => array_values($this->colorValueIds),
            ],
            [
                'attribute_id' => $this->size->id,
                'is_variation' => $sizeVariation,
                'value_ids'    => array_values($this->sizeValueIds),
            ],
        ];
    }

    public function test_two_variation_attributes_generate_four_variants(): void
    {
        $product = $this->createProduct();

        $payload = [
            'sku'          => 'VARIANT-01',
            'price'        => 100000,
            'status'       => 'published',
            'translations' => ['vi' => ['name' => 'Áo thun']],
            'attributes'   => $this->matrix(),
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product->uuid), $payload)
            ->assertRedirect();

        $this->assertSame(4, Product::find($product->id)->variants()->count());
        $this->assertDatabaseHas('product_attribute', [
            'product_id'   => $product->id,
            'attribute_id' => $this->color->id,
            'is_variation' => true,
        ]);
        $this->assertSame(4, \DB::table('product_attribute_value')->where('product_id', $product->id)->count());
    }

    public function test_adding_value_creates_new_variants_and_preserves_existing_data(): void
    {
        $product = $this->createProduct();

        $service = app(ProductService::class);
        $service->syncAttributes($product, $this->matrix());
        $service->generateVariants($product, $this->matrix(), []);

        $green = $this->color->values()->create(['value' => 'Xanh lá', 'color_code' => '#00ff00']);
        $this->colorValueIds['Xanh lá'] = $green->id;

        $existingKey = collect([$this->colorValueIds['Đỏ'], $this->sizeValueIds['S']])
            ->sort()->implode('-');

        $service->syncAttributes($product, $this->matrix());
        $service->generateVariants($product, $this->matrix(), [
            $existingKey => ['sku' => 'PRESERVED-SKU', 'price' => 999000, 'stock_quantity' => 7],
        ]);

        $variants = Product::find($product->id)->variants;

        $this->assertCount(6, $variants);
        $preserved = $variants->firstWhere('sku', 'PRESERVED-SKU');
        $this->assertNotNull($preserved);
        $this->assertSame('999000.00', (string) $preserved->price);
        $this->assertSame(7, $preserved->stock_quantity);
    }

    public function test_removing_variation_flag_shrinks_variants(): void
    {
        $product = $this->createProduct();

        $service = app(ProductService::class);
        $service->syncAttributes($product, $this->matrix());
        $service->generateVariants($product, $this->matrix(), []);

        $this->assertSame(4, Product::find($product->id)->variants()->count());

        // Bỏ "dùng cho biến thể" của size → chỉ còn 2 variant theo màu
        $service->syncAttributes($product, $this->matrix(false));
        $service->generateVariants($product, $this->matrix(false), []);

        $variants = Product::find($product->id)->variants;

        $this->assertCount(2, $variants);
        foreach ($variants as $variant) {
            $this->assertSame(1, $variant->attributeValues()->count());
        }
    }

    public function test_variant_name_joins_values_in_attribute_order(): void
    {
        $product = $this->createProduct();

        $service = app(ProductService::class);
        $service->syncAttributes($product, $this->matrix());
        $service->generateVariants($product, $this->matrix(), []);

        $variant = Product::find($product->id)->variants()->first();

        $this->assertSame('Đỏ - S', $variant->name);
    }

    public function test_custom_attribute_created_from_product_form_is_scoped_out_of_catalog(): void
    {
        $product = $this->createProduct();

        $this->actingAs($this->admin)
            ->postJson(route('admin.products.attributes.store', $product->uuid), [
                'translations' => ['vi' => ['name' => 'Chất liệu']],
                'values'       => [
                    ['value' => 'Cotton'],
                    ['value' => 'Len'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $attribute = ProductAttribute::where('product_id', $product->id)->firstOrFail();

        $this->assertSame('Chất liệu', $attribute->name);
        $this->assertSame(2, $attribute->values()->count());

        $this->actingAs($this->admin)
            ->get(route('admin.product-attributes.index'))
            ->assertOk()
            ->assertDontSee('Chất liệu vải');
    }

    public function test_duplicate_variant_sku_is_rejected(): void
    {
        $product = $this->createProduct();

        $firstKey = collect([$this->colorValueIds['Đỏ'], $this->sizeValueIds['S']])->sort()->implode('-');

        $payload = [
            'sku'          => 'VARIANT-01',
            'price'        => 100000,
            'status'       => 'published',
            'translations' => ['vi' => ['name' => 'Áo thun']],
            'attributes'   => $this->matrix(),
            'variants'     => [
                $firstKey => ['sku' => 'DUP-VARIANT'],
            ],
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product->uuid), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('product_variants', ['sku' => 'DUP-VARIANT']);

        $product2 = $this->createProduct('VARIANT-02');
        $payload['sku'] = 'VARIANT-02';

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product2->uuid), $payload)
            ->assertRedirect()
            ->assertSessionHasErrors('variants.' . $firstKey . '.sku');

        $this->assertDatabaseMissing('product_variants', ['product_id' => $product2->id, 'sku' => 'DUP-VARIANT']);
    }

    public function test_editor_without_products_update_cannot_create_custom_attribute(): void
    {
        $editor = $this->makeEditor(['products.create']);
        $product = $this->createProduct();

        $this->actingAs($editor)
            ->postJson(route('admin.products.attributes.store', $product->uuid), [
                'translations' => ['vi' => ['name' => 'Bị từ chối']],
                'values'       => [['value' => 'X']],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('product_attributes', ['product_id' => $product->id]);
    }

    public function test_combination_explosion_is_rejected(): void
    {
        $product = $this->createProduct();

        $service = app(ProductService::class);

        $bigAttribute = ProductAttribute::create(['code' => 'big', 'type' => 'select']);
        $bigAttribute->translations()->create(['locale' => 'vi', 'name' => 'Thuộc tính lớn']);
        for ($i = 0; $i < 101; $i++) {
            $bigAttribute->values()->create(['value' => 'V' . $i]);
        }

        $valueIds = $bigAttribute->values()->pluck('id')->all();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $service->generateVariants($product, [
            ['attribute_id' => $bigAttribute->id, 'is_variation' => true, 'value_ids' => $valueIds],
        ], []);
    }

    public function test_product_form_renders_attribute_and_variant_blocks(): void
    {
        $product = $this->createProduct();

        $service = app(ProductService::class);
        $service->syncAttributes($product, $this->matrix());
        $service->generateVariants($product, $this->matrix(), []);

        $this->actingAs($this->admin)
            ->get(route('admin.products.edit', $product->uuid))
            ->assertOk()
            ->assertSee('Thuộc tính sản phẩm')
            ->assertSee('Biến thể sản phẩm')
            ->assertSee('Dùng cho biến thể')
            ->assertSee('Màu sắc')
            ->assertSee('Áo thun');
    }

    public function test_no_variation_attributes_yields_no_variants(): void
    {
        $product = $this->createProduct();

        $matrix = [
            [
                'attribute_id' => $this->color->id,
                'is_variation' => false,
                'value_ids'    => array_values($this->colorValueIds),
            ],
        ];

        $service = app(ProductService::class);
        $service->syncAttributes($product, $matrix);
        $service->generateVariants($product, $matrix, []);

        $this->assertSame(0, Product::find($product->id)->variants()->count());
        // Thuộc tính vẫn được sync (dùng cho bộ lọc)
        $this->assertSame(2, \DB::table('product_attribute_value')->where('product_id', $product->id)->count());
    }

    /**
     * Tạo user thuộc role `editor` với đúng các permission truyền vào.
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
