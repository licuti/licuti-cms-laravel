<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

class PostDTO
{
    public function __construct(
        public readonly string $status,
        public readonly array $categoryIds,
        public readonly ?int $authorId,
        public readonly ?string $image,
        public readonly bool $isFeatured,
        public readonly ?string $publishedAt,
        public readonly array $translations,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $image = $request->boolean('image_remove')
            ? null
            : ($request->input('image_uuid') ?: ($request->input('image') ?: ($request->input('featured_image') ?: null)));

        return new self(
            status:       $request->input('status', 'draft'),
            categoryIds:  $request->input('category_ids') ?: [],
            authorId:     $request->input('author_id') ?: null,
            image:        $image,
            isFeatured:   $request->boolean('is_featured'),
            publishedAt:  $request->input('published_at') ?: null,
            translations: $request->input('translations', []),
        );
    }

    /**
     * Tạo DTO từ mảng — dùng cho seeder, test, hoặc import data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status:       $data['status'] ?? 'draft',
            categoryIds:  $data['category_ids'] ?? $data['categoryIds'] ?? [],
            authorId:     $data['author_id'] ?? $data['authorId'] ?? null,
            image:        $data['image'] ?? null,
            isFeatured:   (bool) ($data['is_featured'] ?? $data['isFeatured'] ?? false),
            publishedAt:  $data['published_at'] ?? $data['publishedAt'] ?? null,
            translations: $data['translations'] ?? [],
        );
    }

    /** Dữ liệu ghi vào bảng posts */
    public function toArray(): array
    {
        return [
            'status'       => $this->status,
            'author_id'    => $this->authorId,
            'image'        => $this->image,
            'is_featured'  => $this->isFeatured,
            'published_at' => $this->publishedAt,
        ];
    }
}