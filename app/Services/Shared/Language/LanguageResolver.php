<?php

namespace App\Services\Shared\Language;

use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Support\Collection;

class LanguageResolver
{
    public function __construct(
        private readonly LanguageRepositoryInterface $languageRepository
    ) {}

    /**
     * Lấy danh sách tất cả ngôn ngữ đang hoạt động
     */
    public function getActiveLanguages(): Collection
    {
        return $this->languageRepository->getActiveLanguages();
    }

    /**
     * Lấy ngôn ngữ mặc định của hệ thống
     */
    public function getDefaultLanguage()
    {
        return $this->getActiveLanguages()->where('is_default', true)->first();
    }

    /**
     * Xác định ngôn ngữ hiện tại đang sử dụng (detect từ session, url hoặc fallback)
     */
    public function getCurrentLanguage()
    {
        $activeLanguages = $this->getActiveLanguages();
        
        if ($activeLanguages->isEmpty()) {
            return null;
        }

        $currentLocale = app()->getLocale();
        $language = $activeLanguages->where('code', $currentLocale)->first();

        return $language ?: $this->getDefaultLanguage() ?: $activeLanguages->first();
    }
}
