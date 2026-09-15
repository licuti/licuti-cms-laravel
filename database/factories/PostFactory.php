<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Post;
use App\Models\User;
use App\Core\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'uuid'         => $this->faker->uuid(),
            'status'       => ContentStatus::DRAFT->value,
            'is_featured'  => false,
            'view_count'   => 0,
            'author_id'    => User::factory(),
            'image'        => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => ContentStatus::PUBLISHED->value,
            'published_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ContentStatus::DRAFT->value,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => ContentStatus::ARCHIVED->value,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    /**
     * Đính kèm 1 bản dịch Tiếng Việt.
     */
    public function withVietnamese(?string $title = null): static
    {
        $title = $title ?? $this->faker->sentence(6);

        return $this->afterCreating(function (Post $post) use ($title) {
            $post->translations()->create([
                'locale'  => 'vi',
                'title'   => $title,
                'slug'    => \Illuminate\Support\Str::slug($title),
                'excerpt' => $this->faker->paragraph(),
                'content' => $this->faker->paragraphs(5, true),
            ]);
        });
    }

    /**
     * Đính kèm nhiều bản dịch.
     */
    public function withTranslations(array $titlesByLocale): static
    {
        return $this->afterCreating(function (Post $post) use ($titlesByLocale) {
            foreach ($titlesByLocale as $locale => $title) {
                $post->translations()->create([
                    'locale'  => $locale,
                    'title'   => $title,
                    'slug'    => \Illuminate\Support\Str::slug($title),
                    'excerpt' => $this->faker->paragraph(),
                    'content' => $this->faker->paragraphs(5, true),
                ]);
            }
        });
    }
}