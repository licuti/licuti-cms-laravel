<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'logo'                         => ['nullable', 'string'],
            'website'                      => ['nullable', 'url', 'max:255'],
            'is_active'                    => ['nullable', 'boolean'],
            'display_order'                => ['nullable', 'integer', 'min:0'],
            'translations'                 => ['required', 'array'],
            "translations.{$defaultLocale}.name" => ['required', 'string', 'max:255'],
            'translations.*.name'          => ['nullable', 'string', 'max:255'],
            'translations.*.slug'          => ['nullable', 'string', 'max:255'],
            'translations.*.description'   => ['nullable', 'string'],
            'seo'                          => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'translations.vi.name' => __('Tên thương hiệu (Tiếng Việt)'),
            'website'              => __('Địa chỉ website'),
            'display_order'        => __('Thứ tự hiển thị'),
        ];
    }
}