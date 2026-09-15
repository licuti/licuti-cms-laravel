<?php

namespace App\Http\Requests\Admin\Page;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'translations.*.title'   => 'required|string|max:255',
            'translations.*.content' => 'nullable|string',
            'display_order'          => 'nullable|integer|min:0',
            'is_active'              => 'boolean',
            'meta_title'             => 'nullable|string|max:255',
            'meta_description'       => 'nullable|string|max:255',
            'meta_keywords'          => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'translations.*.title.required'   => __('Tiêu đề trang tĩnh là bắt buộc.'),
            'display_order.integer'           => __('Thứ tự hiển thị phải là số nguyên.'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'translations.*.title'   => __('tiêu đề'),
            'translations.*.content' => __('nội dung'),
        ];
    }
}
