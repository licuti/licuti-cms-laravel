<?php

namespace Database\Factories;

use App\Core\Enums\ContentStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => strtoupper(Str::random(10)),
            'price' => $this->faker->numberBetween(10000, 50000000),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'status' => ContentStatus::PUBLISHED->value,
            'published_at' => now(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ContentStatus::PUBLISHED->value,
            'published_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ContentStatus::DRAFT->value]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => ContentStatus::ARCHIVED->value]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function withCategory(Category $category): static
    {
        return $this->state(fn () => [
            'category_id' => $category->id,
        ]);
    }

    public function withBrand(Brand $brand): static
    {
        return $this->state(fn () => [
            'brand_id' => $brand->id,
        ]);
    }

    /**
     * Đính kèm bản dịch Tiếng Việt.
     */
    public function withVietnamese(?string $name = null): static
    {
        $name = $name ?? $this->faker->words(4, true);

        return $this->afterCreating(function (Product $product) use ($name) {
            $product->translations()->create([
                'locale' => 'vi',
                'name' => $name,
                'slug' => Str::slug($name),
                'short_description' => $this->faker->sentence(),
                'description' => $this->faker->paragraphs(3, true),
            ]);
        });
    }
}
