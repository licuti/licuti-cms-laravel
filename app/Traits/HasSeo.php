<?php

namespace App\Traits;

use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    /**
     * Get all of the model's SEO metadata translations.
     */
    public function seoTranslations(): MorphMany
    {
        return $this->morphMany(SeoMetadata::class, 'seoable');
    }

    /**
     * Get the SEO metadata for the current locale.
     */
    public function currentSeo(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable')
                    ->where('locale', app()->getLocale());
    }

    /**
     * Helper to retrieve or initialize SEO meta for a specific locale.
     *
     * @param string $locale
     * @return SeoMetadata
     */
    public function seoForLocale(string $locale): SeoMetadata
    {
        return $this->seoTranslations()->firstOrNew(['locale' => $locale]);
    }

    /**
     * Helper to save SEO translations from request data.
     */
    public function saveSeoTranslations(array $translations): void
    {
        foreach ($translations as $locale => $data) {
            $seoData = [
                'meta_title'       => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords'    => $data['meta_keywords'] ?? null,
                'og_title'         => $data['og_title'] ?? null,
                'og_description'   => $data['og_description'] ?? null,
                'og_image'         => $data['og_image'] ?? null,
                'canonical_url'    => $data['canonical_url'] ?? null,
                'schema_type'      => $data['schema_type'] ?? null,
                'robots_index'     => $data['robots_index'] ?? 1,
                'robots_follow'    => $data['robots_follow'] ?? 1,
                'focus_keyword'    => $data['focus_keyword'] ?? null,
            ];
            
            // Lọc ra các giá trị rỗng để kiểm tra xem có thực sự cần lưu không
            // Nhưng nếu robots_index hoặc follow bị set = 0 (false), nó vẫn là giá trị hợp lệ cần lưu
            $hasData = collect($seoData)->except(['robots_index', 'robots_follow'])->filter()->isNotEmpty() 
                    || $seoData['robots_index'] == 0 
                    || $seoData['robots_follow'] == 0;

            if ($hasData) {
                $this->seoTranslations()->updateOrCreate(['locale' => $locale], $seoData);
            } else {
                // Nếu không có dữ liệu gì cả, có thể xóa dòng SEO này cho gọn DB (optional)
                $this->seoTranslations()->where('locale', $locale)->delete();
            }
        }
    }
}
