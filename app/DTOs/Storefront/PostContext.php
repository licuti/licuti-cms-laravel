<?php

namespace App\DTOs\Storefront;

use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\SeoMetadata;

final readonly class PostContext
{
    public function __construct(
        public Post $post,
        public ?PostTranslation $translation,
        public ?SeoMetadata $seo,
        public string $locale,
        public string $fallbackLocale,
    ) {}
}
