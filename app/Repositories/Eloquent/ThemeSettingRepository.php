<?php

namespace App\Repositories\Eloquent;

use App\Models\ThemeSetting;
use App\Repositories\Interfaces\ThemeSettingRepositoryInterface;
use Illuminate\Support\Collection;

class ThemeSettingRepository implements ThemeSettingRepositoryInterface
{
    public function __construct(
        private readonly ThemeSetting $model,
    ) {}

    public function getByTheme(string $themeSlug): Collection
    {
        return $this->model->where('theme_slug', $themeSlug)->get();
    }

    public function updateOrCreate(string $themeSlug, string $key, ?string $value): void
    {
        $this->model->updateOrCreate(
            ['theme_slug' => $themeSlug, 'key' => $key],
            ['value' => $value],
        );
    }
}
