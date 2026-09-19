<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
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
            'code'        => 'vi',
            'name'        => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default'  => true,
            'is_active'   => true,
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
            'code'          => 'color',
            'type'          => 'color',
            'is_filterable' => true,
            'display_order' => 1,
        ]);
        $attribute->translations()->create([
            'locale' => 'vi',
            'name'   => 'Màu sắc',
        ]);
        $attribute->values()->create([
            'value'         => 'Đỏ',
            'color_code'    => '#ff0000',
            'display_order' => 1,
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
            'code'          => 'size',
            'type'          => 'button',
            'is_filterable' => 1,
            'display_order' => 2,
            'translations'  => [
                'vi' => [
                    'name' => 'Kích thước',
                ],
            ],
            'values'        => [
                ['value' => 'S', 'display_order' => 1],
                ['value' => 'M', 'display_order' => 2],
                ['value' => 'L', 'display_order' => 3],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.store'), $payload);

        $response->assertRedirect(route('admin.product-attributes.index'));
        $this->assertDatabaseHas('product_attributes', ['code' => 'size', 'type' => 'button']);
        $this->assertDatabaseHas('product_attribute_translations', ['name' => 'Kích thước']);
        $this->assertDatabaseHas('product_attribute_values', ['value' => 'S']);
        $this->assertDatabaseHas('product_attribute_values', ['value' => 'M']);
        $this->assertDatabaseHas('product_attribute_values', ['value' => 'L']);
    }

    public function test_admin_can_update_product_attribute(): void
    {
        $attribute = ProductAttribute::create([
            'code'          => 'material',
            'type'          => 'select',
            'is_filterable' => true,
            'display_order' => 1,
        ]);
        $attribute->translations()->create([
            'locale' => 'vi',
            'name'   => 'Chất liệu cũ',
        ]);

        $payload = [
            'code'          => 'material_updated',
            'type'          => 'select',
            'is_filterable' => 0,
            'display_order' => 5,
            'translations'  => [
                'vi' => [
                    'name' => 'Chất liệu vải cao cấp',
                ],
            ],
            'values'        => [
                ['value' => 'Cotton', 'display_order' => 1],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-attributes.update', $attribute->uuid), $payload);

        $response->assertRedirect(route('admin.product-attributes.index'));
        $this->assertDatabaseHas('product_attributes', ['code' => 'material_updated', 'display_order' => 5]);
        $this->assertDatabaseHas('product_attribute_translations', ['name' => 'Chất liệu vải cao cấp']);
        $this->assertDatabaseHas('product_attribute_values', ['value' => 'Cotton']);
    }

    public function test_admin_can_delete_product_attribute(): void
    {
        $attribute = ProductAttribute::create([
            'code'          => 'temp_attr',
            'type'          => 'select',
            'is_filterable' => true,
        ]);
        $attribute->translations()->create([
            'locale' => 'vi',
            'name'   => 'Thuộc tính tạm',
        ]);
        $val = $attribute->values()->create([
            'value' => 'Tạm 1',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product-attributes.destroy', $attribute->uuid));

        $response->assertRedirect(route('admin.product-attributes.index'));
        $this->assertDatabaseMissing('product_attributes', ['id' => $attribute->id]);
        $this->assertDatabaseMissing('product_attribute_values', ['id' => $val->id]);
    }
}
