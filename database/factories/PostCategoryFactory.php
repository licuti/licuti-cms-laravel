<?php

namespace Database\Factories;

use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostCategory>
 */
class PostCategoryFactory extends Factory
{
    protected $model = PostCategory::class;

    public function definition(): array
    {
        return [
            'uuid'          => $this->faker->uuid(),
            'parent_id'     => null,
            'image'         => null,
            'icon'          => null,
            'is_active'     => true,
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function childOf(PostCategory $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    public function withVietnameseName(string $name): static
    {
        return $this->afterCreating(function (PostCategory $cat) use ($name) {
            $cat->translations()->create([
                'locale' => 'vi',
                'name'   => $name,
                'slug'   => \Illuminate\Support\Str::slug($name),
            ]);
        });
    }
}