<?php

namespace Database\Factories;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAttributeValue>
 */
class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

    public function definition(): array
    {
        return [
            'attribute_id' => ProductAttribute::factory(),
            'value' => $this->faker->word(),
            'color_code' => null,
            'display_order' => 0,
        ];
    }

    public function forAttribute(ProductAttribute $attribute): static
    {
        return $this->state(fn () => ['attribute_id' => $attribute->id]);
    }

    public function withColor(string $value, string $colorCode): static
    {
        return $this->state(fn () => [
            'value' => $value,
            'color_code' => $colorCode,
        ]);
    }
}
