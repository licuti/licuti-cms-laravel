<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageTranslation>
 */
class PageTranslationFactory extends Factory
{
    protected $model = PageTranslation::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);

        return [
            'page_id' => Page::factory(),
            'locale'  => 'vi',
            'title'   => $title,
            'slug'    => Str::slug($title),
            'excerpt' => $this->faker->sentence(20),
            'content' => $this->faker->paragraphs(5, true),
        ];
    }

    public function english(): static
    {
        $title = $this->faker->sentence(6);
        return $this->state(fn () => [
            'locale' => 'en',
            'title'  => $title,
            'slug'   => Str::slug($title),
        ]);
    }

    public function locale(string $code): static
    {
        return $this->state(fn () => ['locale' => $code]);
    }

    public function withTitle(string $title): static
    {
        return $this->state(fn () => [
            'title' => $title,
            'slug'  => Str::slug($title),
        ]);
    }

    public function withContent(string $content): static
    {
        return $this->state(fn () => ['content' => $content]);
    }
}
