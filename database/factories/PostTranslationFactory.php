<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostTranslation>
 */
class PostTranslationFactory extends Factory
{
    protected $model = PostTranslation::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);

        return [
            'post_id'   => Post::factory(),
            'locale'    => 'vi',
            'title'     => $title,
            'slug'      => \Illuminate\Support\Str::slug($title),
            'excerpt'   => $this->faker->paragraph(),
            'content'   => $this->faker->paragraphs(5, true),
        ];
    }

    public function english(): static
    {
        return $this->state(fn () => [
            'locale' => 'en',
            'title'  => $this->faker->sentence(6),
        ]);
    }

    public function locale(string $code): static
    {
        return $this->state(fn () => ['locale' => $code]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(fn () => ['slug' => $slug]);
    }
}