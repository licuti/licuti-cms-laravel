<?php

namespace App\Http\Requests\Admin\Post;

use Illuminate\Foundation\Http\FormRequest;
use App\Core\Enums\PostStatus;
use Illuminate\Validation\Rules\Enum;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if ($user && !$user->hasRole(['admin', 'super-admin'])) {
            $this->merge([
                'author_id' => $user->id,
            ]);
        }
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'status'         => ['required', new Enum(PostStatus::class)],
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'exists:post_categories,id',
            'author_id'      => 'nullable|exists:users,id',
            'image'        => 'nullable|string|max:255',
            'image_uuid'   => 'nullable|string|max:255',
            'image_remove' => 'nullable|boolean',
            'is_featured'  => 'boolean',
            'published_at' => 'nullable|date',

            'translations'                    => 'required|array',
            "translations.{$defaultLocale}.title" => 'required|string|max:255',
            'translations.*.title'            => 'nullable|string|max:255',
            'translations.*.slug'             => 'nullable|string|max:255',
            'translations.*.excerpt'          => 'nullable|string|max:1000',
            'translations.*.content'          => 'nullable|string',
            'translations.*.meta_title'       => 'nullable|string|max:60',
            'translations.*.meta_description' => 'nullable|string|max:160',
            'translations.*.meta_keywords'    => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'status'                => 'trạng thái',
            'category_ids'          => 'danh mục',
            'translations.*.title'  => 'tiêu đề',
            'translations.*.slug'   => 'đường dẫn',
        ];
    }
}
