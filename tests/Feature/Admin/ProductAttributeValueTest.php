<?php

namespace Tests\Feature\Admin;

use App\Enums\AttributeType;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAttributeValueTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ProductAttribute $color;

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

        $this->color = ProductAttribute::create([
            'code' => 'color',
            'type' => AttributeType::COLOR->value,
        ]);
        $this->color->translations()->create(['locale' => 'vi', 'name' => 'Màu sắc']);
    }

    private function createValue(ProductAttribute $attribute, string $text, int $displayOrder = 0, ?string $colorCode = '#000000'): ProductAttributeValue
    {
        $value = $attribute->values()->create([
            'color_code' => $colorCode,
            'display_order' => $displayOrder,
        ]);
        $value->translations()->create(['locale' => 'vi', 'value' => $text]);

        return $value;
    }

    public function test_admin_can_bulk_delete_values(): void
    {
        $v1 = $this->createValue($this->color, 'Đỏ', 1);
        $v2 = $this->createValue($this->color, 'Xanh', 2);
        $v3 = $this->createValue($this->color, 'Vàng', 3);

        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.values.bulk', $this->color->uuid), [
                'bulk_module' => 'product_attribute_values',
                'action' => 'delete',
                'ids' => [$v1->uuid, $v3->uuid],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('product_attribute_values', ['id' => $v1->id]);
        $this->assertDatabaseMissing('product_attribute_values', ['id' => $v3->id]);
        $this->assertDatabaseHas('product_attribute_values', ['id' => $v2->id]);
    }

    public function test_admin_cannot_bulk_delete_in_use_values(): void
    {
        $v1 = $this->createValue($this->color, 'Đỏ', 1);
        $v2 = $this->createValue($this->color, 'Xanh', 2);

        $product = Product::create([
            'sku' => 'BULK-VAL-01',
            'price' => 100000,
            'stock_quantity' => 1,
            'status' => 'published',
        ]);
        $product->translations()->create(['locale' => 'vi', 'name' => 'Áo', 'slug' => 'ao']);
        $product->attributeValues()->attach($v1->id);

        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.values.bulk', $this->color->uuid), [
                'bulk_module' => 'product_attribute_values',
                'action' => 'delete',
                'ids' => [$v1->uuid, $v2->uuid],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        // Transaction rollback — giữ lại cả 2
        $this->assertDatabaseHas('product_attribute_values', ['id' => $v1->id]);
        $this->assertDatabaseHas('product_attribute_values', ['id' => $v2->id]);
    }

    public function test_move_up_swaps_display_order(): void
    {
        $v1 = $this->createValue($this->color, 'Đỏ', 1);
        $v2 = $this->createValue($this->color, 'Xanh', 2);

        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.values.move_up', [$this->color->uuid, $v2->uuid]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, (int) ProductAttributeValue::where('id', $v2->id)->value('display_order'));
        $this->assertSame(2, (int) ProductAttributeValue::where('id', $v1->id)->value('display_order'));
    }

    public function test_move_down_swaps_display_order(): void
    {
        $v1 = $this->createValue($this->color, 'Đỏ', 1);
        $v2 = $this->createValue($this->color, 'Xanh', 2);

        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.values.move_down', [$this->color->uuid, $v1->uuid]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(2, (int) ProductAttributeValue::where('id', $v1->id)->value('display_order'));
        $this->assertSame(1, (int) ProductAttributeValue::where('id', $v2->id)->value('display_order'));
    }

    public function test_move_up_on_first_value_is_noop(): void
    {
        $v1 = $this->createValue($this->color, 'Đỏ', 1);
        $v2 = $this->createValue($this->color, 'Xanh', 2);

        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.values.move_up', [$this->color->uuid, $v1->uuid]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, (int) ProductAttributeValue::where('id', $v1->id)->value('display_order'));
        $this->assertSame(2, (int) ProductAttributeValue::where('id', $v2->id)->value('display_order'));
    }

    public function test_move_value_scoped_to_attribute(): void
    {
        $size = ProductAttribute::create(['code' => 'size', 'type' => AttributeType::BUTTON->value]);
        $size->translations()->create(['locale' => 'vi', 'name' => 'Kích thước']);

        $colorValue = $this->createValue($this->color, 'Đỏ', 1);
        $sizeValue = $this->createValue($size, 'S', 1);

        // Move value của color qua URL size → 404, không thay đổi gì.
        $this->actingAs($this->admin)
            ->post(route('admin.product-attributes.values.move_up', [$size->uuid, $colorValue->uuid]))
            ->assertNotFound();

        $this->assertSame(1, (int) ProductAttributeValue::where('id', $sizeValue->id)->value('display_order'));
    }
}
