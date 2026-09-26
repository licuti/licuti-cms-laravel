<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAttribute>
 */
class ProductAttributeFactory extends Factory
{
    protected $model = ProductAttribute::class;

    public function definition(): array
    {
        return [
            'product_id' => null, // catalog toàn cục mặc định
            'code' => $this->faker->unique()->slug(2),
            'type' => 'select',
            'is_filterable' => true,
            'display_order' => 0,
        ];
    }

    /**
     * Thuộc tính catalog toàn cục (product_id IS NULL).
     */
    public function global(): static
    {
        return $this->state(fn () => ['product_id' => null]);
    }

    /**
     * Custom attribute của 1 product cụ thể.
     */
    public function custom(Product $product): static
    {
        return $this->state(fn () => ['product_id' => $product->id]);
    }

    /**
     * Đính kèm bản dịch Tiếng Việt.
     */
    public function withVietnamese(?string $name = null): static
    {
        return $this->afterCreating(function (ProductAttribute $attribute) use ($name) {
            $attribute->translations()->create([
                'locale' => 'vi',
                'name' => $name ?? $this->faker->words(2, true),
            ]);
        });
    }
}
