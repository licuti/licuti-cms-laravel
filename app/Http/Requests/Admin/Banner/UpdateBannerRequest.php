<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'position'      => ['required', 'string', 'max:50'],
            'link'          => ['nullable', 'string', 'max:255'],
            'target'        => ['nullable', 'string', Rule::in(['_self', '_blank'])],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'start_date'    => ['nullable', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active'     => ['nullable', 'boolean'],
            'translations'  => ['required', 'array'],
            "translations.{$defaultLocale}.title" => ['required', 'string', 'max:255'],
            'translations.*.title'       => ['nullable', 'string', 'max:255'],
            'translations.*.image'       => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'position.required' => __('Vui lòng chọn vị trí hiển thị banner.'),
            'translations.*.title.required' => __('Tiêu đề banner không được để trống.'),
            'end_date.after_or_equal' => __('Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.'),
        ];
    }
}
