<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper($this->faker->bothify('VAR-????####')),
            'price' => $this->faker->numberBetween(10000, 50000000),
            'compare_price' => null,
            'stock_quantity' => $this->faker->numberBetween(0, 50),
            'is_active' => true,
            'display_order' => 0,
        ];
    }
}
