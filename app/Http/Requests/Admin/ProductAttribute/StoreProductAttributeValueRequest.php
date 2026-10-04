<?php

namespace App\Http\Requests\Admin\ProductAttribute;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductAttributeValueRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'product-attributes.update';
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'color_code'    => ['nullable', 'string', 'max:50', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'translations'  => ['required', 'array'],
            "translations.{$defaultLocale}.value" => ['required', 'string', 'max:255'],
            'translations.*.value' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'translations.*.value.required' => __('Giá trị không được để trống.'),
        ];
    }
}
