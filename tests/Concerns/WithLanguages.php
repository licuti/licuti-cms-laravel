<?php

namespace Tests\Concerns;

use App\Models\Language;
use Database\Factories\LanguageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

/**
 * Trait dùng chung cho các test liên quan tới i18n.
 * Tự động tạo 2 ngôn ngữ mặc định (vi, en) + flush cache Language.
 */
trait WithLanguages
{
    protected Language $vietnamese;
    protected Language $english;

    protected function setUpLanguages(): void
    {
        Cache::flush('languages_active');

        $this->vietnamese = LanguageFactory::new()->vietnamese()->create();
        $this->english = LanguageFactory::new()->english()->create();

        app()->setLocale($this->vietnamese->code);
    }
}