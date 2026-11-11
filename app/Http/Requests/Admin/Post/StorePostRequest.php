<?php

namespace App\Http\Requests\Admin\Post;

use App\Core\Enums\ContentStatus;
use App\Core\Traits\AuthorizesWithPermission;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePostRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'posts.create';
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if ($user && ! $user->hasRole(['admin', 'super-admin'])) {
            $this->merge([
                'author_id' => $user->id,
            ]);
        }
    }

    public function rules(): array
    {
        $defaultLocale = Language::where('is_default', true)->value('code') ?? app()->getLocale();
        $titleLocale = $this->titleLocale($defaultLocale);

        return [
            'status' => ['required', new Enum(ContentStatus::class)],
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:post_categories,id',
            'author_id' => 'nullable|exists:users,id',
            'image' => 'nullable|string|max:255',
            'image_uuid' => 'nullable|string|max:255',
            'image_remove' => 'nullable|boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',

            'translations' => 'required|array',
            "translations.{$titleLocale}.title" => 'required|string|max:255',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.slug' => 'nullable|string|max:255',
            'translations.*.excerpt' => 'nullable|string|max:1000',
            'translations.*.content' => 'nullable|string',
            'translations.*.meta_title' => 'nullable|string|max:60',
            'translations.*.meta_description' => 'nullable|string|max:160',
            'translations.*.meta_keywords' => 'nullable|string|max:255',
        ];
    }

    /** Ngôn ngữ bắt buộc có tiêu đề khi lưu (mặc định: ngôn ngữ mặc định). */
    protected function titleLocale(string $defaultLocale): string
    {
        return $defaultLocale;
    }

    /** Ngôn ngữ đang soạn, lấy từ ?lang= và chỉ chấp nhận ngôn ngữ đang hoạt động. */
    protected function contentLocale(): string
    {
        $defaultLocale = Language::where('is_default', true)->value('code') ?? app()->getLocale();
        $requested = $this->query('lang');

        if (! is_string($requested) || $requested === '') {
            return $defaultLocale;
        }

        return Language::where('is_active', true)->where('code', $requested)->exists()
            ? $requested
            : $defaultLocale;
    }

    public function attributes(): array
    {
        return [
            'status' => 'trạng thái',
            'category_ids' => 'danh mục',
            'translations.*.title' => 'tiêu đề',
            'translations.*.slug' => 'đường dẫn',
        ];
    }
}
