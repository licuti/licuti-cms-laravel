<?php

namespace App\Http\Requests\Admin\ContentBlock;

use App\Core\Enums\ContentStatus;
use App\Core\Traits\AuthorizesWithPermission;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreContentBlockRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'content-blocks.create';
    }

    protected function prepareForValidation(): void
    {
        $translations = $this->input('translations', []);

        if (is_array($translations)) {
            foreach ($translations as $locale => $data) {
                if (is_array($data) && ($data['slug'] ?? null) === '') {
                    $translations[$locale]['slug'] = null;
                }
            }

            $this->merge(['translations' => $translations]);
        }
    }

    public function rules(): array
    {
        $defaultLocale = Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'status' => ['required', new Enum(ContentStatus::class)],
            'image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'image_remove' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'translations' => ['required', 'array'],
            "translations.{$defaultLocale}.title" => ['required', 'string', 'max:255'],
            'translations.*.title' => ['nullable', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.excerpt' => ['nullable', 'string', 'max:1000'],
            'translations.*.content' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'translations.*.slug.regex' => __('Slug chỉ được chứa chữ thường, số và dấu gạch ngang.'),
        ];
    }
}
