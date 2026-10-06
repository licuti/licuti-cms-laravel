<?php

namespace App\Http\Requests\Admin\ProductAttribute;

use App\Core\Traits\AuthorizesWithPermission;
use App\Enums\AttributeType;
use App\Models\Language;
use App\Models\ProductAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductAttributeRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'product-attributes.update';
    }

    public function rules(): array
    {
        $uuid = $this->route('uuid');
        $attribute = ProductAttribute::where('uuid', $uuid)->first();
        $attributeId = $attribute?->id;

        $defaultLocale = Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_\-]+$/',
                Rule::unique('product_attributes', 'code')
                    ->where(fn ($q) => $q->whereNull('product_id'))
                    ->ignore($attributeId),
            ],
            'type' => ['required', 'string', Rule::in(array_column(AttributeType::cases(), 'value'))],
            'is_filterable' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['required', 'array'],
            "translations.{$defaultLocale}.name" => ['required', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => __('Vui lòng nhập mã thuộc tính.'),
            'code.unique' => __('Mã thuộc tính này đã tồn tại trên hệ thống.'),
            'code.regex' => __('Mã thuộc tính chỉ được chứa chữ cái, chữ số, gạch ngang và gạch dưới.'),
            'type.required' => __('Vui lòng chọn loại thuộc tính.'),
            'translations.*.name.required' => __('Tên thuộc tính không được để trống.'),
        ];
    }
}
