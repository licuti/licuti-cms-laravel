<?php

namespace Tests\Feature\Admin;

use App\Enums\AttributeType;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAttributeCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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
    }

    public function test_guest_cannot_access_product_attributes_index(): void
    {
        $response = $this->get(route('admin.product-attributes.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_product_attributes_index(): void
    {
        $attribute = ProductAttribute::create([
            'code' => 'color',
            'type' => AttributeType::COLOR->value,
            'is_filterable' => true,
            'display_order' => 1,
        ]);
        $attribute->translations()->create([
            'locale' => 'vi',
            'name' => 'Màu sắc',
        ]);
        $value = $attribute->values()->create([
            'color_code' => '#ff0000',
            'display_order' => 1,
        ]);
        $value->translations()->create([
            'locale' => 'vi',
            'value' => 'Đỏ',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.product-attributes.index'));

        $response->assertStatus(200);
        $response->assertSee('Màu sắc');
        $response->assertSee('color');
        $response->assertSee('Đỏ');
    }

    public function test_admin_can_create_product_attribute(): void
    {
        $payload = [
            'code' => 'size',
            'type' => AttributeType::BUTTON->value,
            'is_filterable' => 1,
            'display_order' => 2,
            'translations' => [
                'vi' => [
                    'name' => 'Kích thước',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.store'), $payload);

        $response->assertRedirect(route('admin.product-attributes.index'));
        $this->assertDatabaseHas('product_attributes', ['code' => 'size', 'type' => AttributeType::BUTTON->value]);
        $this->assertDatabaseHas('product_attribute_translations', ['name' => 'Kích thước']);
    }

    public function test_admin_can_update_product_attribute(): void
    {
        $attribute = ProductAttribute::create([
            'code' => 'material',
            'type' => AttributeType::SELECT->value,
            'is_filterable' => true,
            'display_order' => 1,
        ]);
        $attribute->translations()->create([
            'locale' => 'vi',
            'name' => 'Chất liệu cũ',
        ]);

        $payload = [
            'code' => 'material_updated',
            'type' => AttributeType::SELECT->value,
            'is_filterable' => 0,
            'display_order' => 5,
            'translations' => [
                'vi' => [
                    'name' => 'Chất liệu vải cao cấp',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-attributes.update', $attribute->uuid), $payload);

        $response->assertRedirect(route('admin.product-attributes.index'));
        $this->assertDatabaseHas('product_attributes', ['code' => 'material_updated', 'display_order' => 5]);
        $this->assertDatabaseHas('product_attribute_translations', ['name' => 'Chất liệu vải cao cấp']);
    }

    public function test_admin_can_delete_product_attribute(): void
    {
        $attribute = ProductAttribute::create([
            'code' => 'temp_attr',
            'type' => AttributeType::SELECT->value,
            'is_filterable' => true,
        ]);
        $attribute->translations()->create([
            'locale' => 'vi',
            'name' => 'Thuộc tính tạm',
        ]);
        $val = $attribute->values()->create([]);
        $valTranslation = $val->translations()->create([
            'locale' => 'vi',
            'value' => 'Tạm 1',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product-attributes.destroy', $attribute->uuid));

        $response->assertRedirect(route('admin.product-attributes.index'));
        $this->assertDatabaseMissing('product_attributes', ['id' => $attribute->id]);
        $this->assertDatabaseMissing('product_attribute_values', ['id' => $val->id]);
        $this->assertDatabaseMissing('product_attribute_value_translations', ['id' => $valTranslation->id]);
    }

    /**
     * Helper tạo attribute + value (có translation vi).
     */
    private function makeAttributeWithValue(string $code = 'color', string $type = AttributeType::COLOR->value, string $valueText = 'Đỏ'): array
    {
        $attribute = ProductAttribute::create(['code' => $code, 'type' => $type]);
        $attribute->translations()->create(['locale' => 'vi', 'name' => 'Thuộc tính '.$code]);
        $value = $attribute->values()->create(['color_code' => '#ff0000', 'display_order' => 1]);
        $value->translations()->create(['locale' => 'vi', 'value' => $valueText]);

        return [$attribute, $value];
    }

    public function test_admin_cannot_edit_value_of_another_attribute(): void
    {
        [$attributeA] = $this->makeAttributeWithValue('color', AttributeType::COLOR->value, 'Đỏ');
        [, $valueB] = $this->makeAttributeWithValue('size', AttributeType::BUTTON->value, 'S');

        $this->actingAs($this->admin)
            ->get(route('admin.product-attributes.values.edit', [$attributeA->uuid, $valueB->uuid]))
            ->assertNotFound();
    }

    public function test_admin_cannot_update_value_of_another_attribute(): void
    {
        [$attributeA] = $this->makeAttributeWithValue('color', AttributeType::COLOR->value, 'Đỏ');
        [, $valueB] = $this->makeAttributeWithValue('size', AttributeType::BUTTON->value, 'S');

        $this->actingAs($this->admin)
            ->put(route('admin.product-attributes.values.update', [$attributeA->uuid, $valueB->uuid]), [
                'color_code' => '#00ff00',
                'translations' => ['vi' => ['value' => 'Hack']],
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('product_attribute_value_translations', ['value' => 'Hack']);
    }

    public function test_admin_cannot_delete_value_of_another_attribute(): void
    {
        [$attributeA] = $this->makeAttributeWithValue('color', AttributeType::COLOR->value, 'Đỏ');
        [, $valueB] = $this->makeAttributeWithValue('size', AttributeType::BUTTON->value, 'S');

        $this->actingAs($this->admin)
            ->delete(route('admin.product-attributes.values.destroy', [$attributeA->uuid, $valueB->uuid]))
            ->assertNotFound();

        $this->assertDatabaseHas('product_attribute_values', ['id' => $valueB->id]);
    }

    public function test_duplicate_value_text_is_rejected(): void
    {
        [$attribute] = $this->makeAttributeWithValue('color', AttributeType::COLOR->value, 'Đỏ');

        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.values.store', $attribute->uuid), [
                'color_code' => '#00ff00',
                'translations' => ['vi' => ['value' => 'Đỏ']],
            ])
            ->assertSessionHasErrors('translations.vi.value');

        $this->assertSame(1, \DB::table('product_attribute_value_translations')->where('value', 'Đỏ')->count());
    }

    public function test_value_can_be_updated_to_same_text(): void
    {
        [$attribute, $value] = $this->makeAttributeWithValue('color', AttributeType::COLOR->value, 'Đỏ');

        $this->actingAs($this->admin)
            ->put(route('admin.product-attributes.values.update', [$attribute->uuid, $value->uuid]), [
                'color_code' => '#ff0000',
                'translations' => ['vi' => ['value' => 'Đỏ']],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    public function test_custom_attribute_code_does_not_block_global(): void
    {
        $product = Product::create([
            'sku' => 'CODE-SCOPE-01',
            'price' => 100000,
            'stock_quantity' => 1,
            'status' => 'published',
        ]);
        $product->translations()->create(['locale' => 'vi', 'name' => 'Sản phẩm A', 'slug' => 'san-pham-a']);

        // Custom attribute code 'color' trên product A
        ProductAttribute::create(['code' => 'color', 'type' => AttributeType::COLOR->value, 'product_id' => $product->id]);

        // Vẫn tạo được global code 'color'
        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.store'), [
                'code' => 'color',
                'type' => AttributeType::COLOR->value,
                'is_filterable' => 0,
                'translations' => ['vi' => ['name' => 'Màu sắc global']],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(2, ProductAttribute::where('code', 'color')->count());
    }

    public function test_global_code_unique_only_within_global_scope(): void
    {
        ProductAttribute::create(['code' => 'material', 'type' => AttributeType::SELECT->value]);

        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.store'), [
                'code' => 'material',
                'type' => AttributeType::SELECT->value,
                'is_filterable' => 0,
                'translations' => ['vi' => ['name' => 'Chất liệu 2']],
            ])
            ->assertSessionHasErrors('code');

        $this->assertSame(1, ProductAttribute::where('code', 'material')->count());
    }

    public function test_color_code_required_when_attribute_is_color(): void
    {
        [$attribute] = $this->makeAttributeWithValue('color', AttributeType::COLOR->value, 'Đỏ');

        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.values.store', $attribute->uuid), [
                'translations' => ['vi' => ['value' => 'Xanh']],
            ])
            ->assertSessionHasErrors('color_code');
    }

    public function test_value_per_page_is_clamped(): void
    {
        [$attribute] = $this->makeAttributeWithValue('attr_pp', AttributeType::SELECT->value, 'V0');
        for ($i = 1; $i <= 20; $i++) {
            $value = $attribute->values()->create([]);
            $value->translations()->create(['locale' => 'vi', 'value' => 'V'.$i]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-attributes.values.index', $attribute->uuid).'?per_page=9999');

        $response->assertOk();
        $this->assertLessThanOrEqual(100, $response->viewData('values')->count());
        $this->assertGreaterThanOrEqual(5, $response->viewData('values')->count());
    }
}
