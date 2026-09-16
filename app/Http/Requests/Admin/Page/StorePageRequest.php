<?php

namespace App\Http\Requests\Admin\Page;

use App\Core\Enums\ContentStatus;
use App\Core\Enums\PageTemplate;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Chuẩn hoá input trước validate:
     * - parent_id rỗng/'0' → null (form select giá trị "")
     * - translations.xx.slug rỗng → null để Service tự sinh
     */
    protected function prepareForValidation(): void
    {
        if (!empty($this->input('parent_id')) === false) {
            $this->merge(['parent_id' => null]);
        }

        $translations = $this->input('translations', []);
        if (is_array($translations)) {
            foreach ($translations as $locale => $data) {
                if (is_array($data) && array_key_exists('slug', $data) && $data['slug'] === '') {
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
            'status'                         => ['required', new Enum(ContentStatus::class)],
            'parent_id'                      => ['nullable', 'integer', 'exists:pages,id'],
            'page_template'                  => ['required', new Enum(PageTemplate::class)],
            'image'                          => ['nullable', 'string', 'max:255'],
            'image_uuid'                     => ['nullable', 'string', 'max:255'],
            'image_remove'                   => ['nullable', 'boolean'],
            'display_order'                  => ['nullable', 'integer', 'min:0'],
            'published_at'                   => ['nullable', 'date'],
            'translations'                   => ['required', 'array'],
            "translations.{$defaultLocale}.title" => ['required', 'string', 'max:255'],
            'translations.*.title'          => ['nullable', 'string', 'max:255'],
            'translations.*.slug'           => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.excerpt'        => ['nullable', 'string', 'max:1000'],
            'translations.*.content'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'translations.*.slug.regex' => __('Đường dẫn (slug) chỉ được chứa chữ thường, số và dấu gạch ngang (ví dụ: trang-gioi-thieu).'),
        ];
    }

    public function attributes(): array
    {
        return [
            'status'                  => 'trạng thái',
            'parent_id'               => 'trang cha',
            'page_template'           => 'mẫu trang',
            'display_order'           => 'thứ tự hiển thị',
            'translations.*.title'    => 'tiêu đề',
            'translations.*.slug'     => 'đường dẫn (slug)',
            'translations.*.excerpt'   => 'mô tả ngắn',
        ];
    }
}
