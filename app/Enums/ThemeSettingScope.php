<?php

namespace App\Enums;

enum ThemeSettingScope: string
{
    case GLOBAL = 'global';
    case COMMON_THEME = 'common_theme';
    case THEME_SPECIFIC = 'theme_specific';

    public static function themeScopes(): array
    {
        return [
            self::COMMON_THEME->value,
            self::THEME_SPECIFIC->value,
        ];
    }
}
