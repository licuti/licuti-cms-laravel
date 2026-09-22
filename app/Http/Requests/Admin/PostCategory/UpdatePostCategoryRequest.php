<?php

namespace App\Http\Requests\Admin\PostCategory;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostCategoryRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'post-categories.update';
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'image_media_uuid' => ['nullable', 'string', 'max:255'],
            'remove_image' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $uuid = $this->route('uuid');
                    $categoryId = \App\Models\PostCategory::where('uuid', $uuid)->value('id');
                    $exists = \Illuminate\Support\Facades\DB::table('post_category_translations')
                        ->where('slug', $value)
                        ->where('post_category_id', '!=', $categoryId)
                        ->exists();
                    if ($exists) {
                        $fail('Slug đã tồn tại.');
                    }
                }
            ],
            'translations.*.description' => ['nullable', 'string'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string'],
            'translations.*.meta_keywords' => ['nullable', 'string', 'max:255'],
        ];
    }
}