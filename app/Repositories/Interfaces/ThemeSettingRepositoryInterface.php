<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface ThemeSettingRepositoryInterface
{
    public function getByTheme(string $themeSlug): Collection;

    public function updateOrCreate(string $themeSlug, string $key, ?string $value): void;
}
