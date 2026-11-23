<?php

use App\Services\Theme\ThemeManager;

if (! function_exists('theme_asset')) {
    /**
     * Lấy URL asset của storefront theme đang active.
     * Theme asset được self-host trong public/themes/{slug}/assets.
     */
    function theme_asset(string $path): string
    {
        return app(ThemeManager::class)->asset($path);
    }
}
