<?php

namespace App\Contracts\Theme;

use App\Services\Theme\ThemeSettingsRegistry;

interface ThemeSettingsProvider
{
    public function register(ThemeSettingsRegistry $registry): void;
}
