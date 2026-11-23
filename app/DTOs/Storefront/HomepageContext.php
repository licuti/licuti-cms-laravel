<?php

namespace App\DTOs\Storefront;

final readonly class HomepageContext
{
    public function __construct(
        public string $locale,
        public string $fallbackLocale,
        public array $siteSettings,
        public array $themeSettings,
    ) {}
}
