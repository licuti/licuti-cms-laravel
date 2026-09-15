<?php

namespace Database\Factories;

use App\Core\Enums\ContentStatus;
use App\Core\Enums\PageTemplate;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'uuid'          => $this->faker->uuid(),
            'parent_id'     => null,
            'page_template' => PageTemplate::DEFAULT->value,
            'image'         => null,
            'display_order' => $this->faker->numberBetween(0, 100),
            'status'        => ContentStatus::DRAFT->value,
            'published_at'  => null,
            'meta_title'        => null,
            'meta_description'  => null,
            'meta_keywords'     => null,
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
        return $this->state(fn () => [
            'status' => ContentStatus::DRAFT->value,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => ContentStatus::ARCHIVED->value,
        ]);
    }

    public function childOf(Page $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    /**
     * Đính kèm 1 bản dịch Tiếng Việt sau khi tạo.
     */
    public function withVietnamese(?string $title = null, ?string $slug = null): static
    {
        $title = $title ?? $this->faker->sentence(6);
        $slug = $slug ?? \Illuminate\Support\Str::slug($title);

        return $this->afterCreating(function (Page $page) use ($title, $slug) {
            $page->translations()->create([
                'locale'  => 'vi',
                'title'   => $title,
                'slug'    => $slug,
                'excerpt' => $this->faker->sentence(20),
                'content' => $this->faker->paragraphs(5, true),
            ]);
        });
    }

    /**
     * Đính kèm nhiều bản dịch theo locale.
     */
    public function withTranslations(array $titlesByLocale): static
    {
        return $this->afterCreating(function (Page $page) use ($titlesByLocale) {
            foreach ($titlesByLocale as $locale => $title) {
                $page->translations()->create([
                    'locale'  => $locale,
                    'title'   => $title,
                    'slug'    => \Illuminate\Support\Str::slug($title),
                    'excerpt' => $this->faker->sentence(20),
                    'content' => $this->faker->paragraphs(5, true),
                ]);
            }
        });
    }
}
