<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductTranslation>
 */
class ProductTranslationFactory extends Factory
{
    protected $model = ProductTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->words(4, true);

        return [
            'product_id' => Product::factory(),
            'locale' => 'vi',
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(3, true),
        ];
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn () => ['locale' => $locale]);
    }
}
