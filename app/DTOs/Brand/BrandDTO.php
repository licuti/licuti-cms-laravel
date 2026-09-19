<?php

namespace App\DTOs\Brand;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandDTO
{
    public function __construct(
        public readonly array $data,
        public readonly array $translations = [],
        public readonly array $seo = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        $rawTranslations = (array) $request->input('translations', []);
        $cleanTranslations = [];
        foreach ($rawTranslations as $locale => $trans) {
            if (!empty($trans['name'])) {
                $slug = !empty($trans['slug']) 
                    ? Str::slug($trans['slug']) 
                    : Str::slug($trans['name']);

                $cleanTranslations[$locale] = [
                    'name'        => trim($trans['name']),
                    'slug'        => $slug,
                    'description' => $trans['description'] ?? null,
                ];
            }
        }

        $data = [
            'logo'          => $request->filled('logo') ? (string) $request->input('logo') : null,
            'website'       => $request->filled('website') ? (string) $request->input('website') : null,
            'is_active'     => $request->boolean('is_active', true),
            'display_order' => (int) $request->input('display_order', 0),
        ];

        return new self(
            data: $data,
            translations: $cleanTranslations,
            seo: (array) $request->input('seo', [])
        );
    }

    public function toArray(): array
    {
        return $this->data;
    }
}