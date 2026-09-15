<?php

namespace App\Http\Requests\Admin\Page;

use App\Core\Enums\ContentStatus;
use App\Core\Enums\PageTemplate;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
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
            'meta_title'                     => ['nullable', 'string', 'max:255'],
            'meta_description'               => ['nullable', 'string', 'max:255'],
            'meta_keywords'                  => ['nullable', 'string', 'max:255'],
            'translations'                   => ['required', 'array'],
            "translations.{$defaultLocale}.title" => ['required', 'string', 'max:255'],
            'translations.*.title'          => ['nullable', 'string', 'max:255'],
            'translations.*.slug'           => ['nullable', 'string', 'max:255'],
            'translations.*.excerpt'        => ['nullable', 'string', 'max:1000'],
            'translations.*.content'        => ['nullable', 'string'],
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
