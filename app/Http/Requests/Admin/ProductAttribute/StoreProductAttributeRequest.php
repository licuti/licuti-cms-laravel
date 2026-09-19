<?php

namespace App\Http\Requests\Admin\ProductAttribute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'code'          => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-]+$/', 'unique:product_attributes,code'],
            'type'          => ['required', 'string', Rule::in(['select', 'color', 'button', 'radio'])],
            'is_filterable' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'translations'  => ['required', 'array'],
            "translations.{$defaultLocale}.name" => ['required', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'values'        => ['nullable', 'array'],
            'values.*.value'       => ['nullable', 'string', 'max:255'],
            'values.*.color_code'  => ['nullable', 'string', 'max:50'],
            'values.*.display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => __('Vui lòng nhập mã thuộc tính.'),
            'code.unique'   => __('Mã thuộc tính này đã tồn tại trên hệ thống.'),
            'code.regex'    => __('Mã thuộc tính chỉ được chứa chữ cái, chữ số, gạch ngang và gạch dưới.'),
            'type.required' => __('Vui lòng chọn loại thuộc tính.'),
            'translations.*.name.required' => __('Tên thuộc tính không được để trống.'),
        ];
    }
}