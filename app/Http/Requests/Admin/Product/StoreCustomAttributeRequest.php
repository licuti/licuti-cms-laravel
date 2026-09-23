<?php

namespace App\Http\Requests\Admin\Product;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomAttributeRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'products.update';
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'code'          => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'type'          => ['nullable', 'string', Rule::in(['select', 'color', 'button', 'radio'])],
            'translations'  => ['required', 'array'],
            "translations.{$defaultLocale}.name" => ['required', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'values'        => ['required', 'array', 'min:1'],
            'values.*.value'       => ['required', 'string', 'max:255'],
            'values.*.color_code'  => ['nullable', 'string', 'max:50'],
            'values.*.display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'translations.*.name.required' => __('Tên thuộc tính không được để trống.'),
            'values.required'  => __('Vui lòng nhập ít nhất một giá trị cho thuộc tính.'),
            'values.*.value.required' => __('Giá trị thuộc tính không được để trống.'),
            'code.regex'       => __('Mã thuộc tính chỉ được chứa chữ cái, chữ số, gạch ngang và gạch dưới.'),
        ];
    }
}
