<?php

namespace App\Http\Requests\Admin\ProductAttribute;

use App\Core\Traits\AuthorizesWithPermission;
use App\Core\Traits\ValidatesAttributeValueUniqueness;
use App\Enums\AttributeType;
use App\Models\Language;
use App\Models\ProductAttribute;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductAttributeValueRequest extends FormRequest
{
    use AuthorizesWithPermission, ValidatesAttributeValueUniqueness;

    protected function permission(): ?string
    {
        return 'product-attributes.update';
    }

    public function rules(): array
    {
        $defaultLocale = Language::where('is_default', true)->value('code') ?? app()->getLocale();
        $attributeId = $this->resolveAttributeIdFromRoute();
        $isColor = $this->isColorAttribute();

        $rules = [
            'color_code' => array_filter([
                $isColor ? 'required' : null,
                'nullable',
                'string',
                'max:50',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ]),
            'display_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['required', 'array'],
            "translations.{$defaultLocale}.value" => ['required', 'string', 'max:255'],
            'translations.*.value' => ['nullable', 'string', 'max:255'],
        ];

        if ($attributeId !== null) {
            foreach ($this->valueUniquenessRules($attributeId) as $key => $extra) {
                $rules[$key] = array_merge($rules[$key] ?? [], $extra);
            }
        }

        return $rules;
    }

    private function isColorAttribute(): bool
    {
        $uuid = $this->route('attribute_uuid');

        if (! is_string($uuid)) {
            return false;
        }

        return ProductAttribute::where('uuid', $uuid)->value('type') === AttributeType::COLOR->value;
    }

    public function messages(): array
    {
        return [
            'translations.*.value.required' => __('Giá trị không được để trống.'),
            'translations.*.value.unique' => __('Giá trị ":value" đã tồn tại trong thuộc tính này.'),
            'color_code.required' => __('Vui lòng chọn mã màu cho giá trị này.'),
        ];
    }
}
