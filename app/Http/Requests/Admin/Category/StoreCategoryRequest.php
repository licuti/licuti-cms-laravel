<?php

namespace App\Http\Requests\Admin\Category;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'categories.create';
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'image_uuid' => ['nullable', 'string', 'max:255'],
            'image_media_uuid' => ['nullable', 'string', 'max:255'],
            'remove_image' => ['nullable', 'boolean'],
            'image_remove' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:1,0'],
            'submit_action' => ['nullable', 'string', 'in:save,save_and_edit'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['required', 'array'],
            "translations.{$defaultLocale}.name" => ['required', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string'],
            'translations.*.content' => ['nullable', 'string'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string'],
            'translations.*.meta_keywords' => ['nullable', 'string', 'max:255'],
        ];
    }
}