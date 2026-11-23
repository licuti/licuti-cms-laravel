<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

class PostDTO
{
    public function __construct(
        public readonly string $status,
        public readonly array $categoryIds,
        public readonly ?int $authorId,
        public readonly ?int $imageMediaId,
        public readonly bool $isFeatured,
        public readonly ?string $publishedAt,
        public readonly array $translations,
        public readonly ?int $viewCount = null,
        public readonly bool $imageMediaProvided = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status:       $request->input('status', 'draft'),
            categoryIds:  $request->input('category_ids') ?: [],
            authorId:     $request->input('author_id') ?: null,
            imageMediaId: $request->boolean('image_remove') ? null : ($request->filled('image_media_id') ? (int) $request->input('image_media_id') : null),
            isFeatured:   $request->boolean('is_featured'),
            publishedAt:  $request->input('published_at') ?: null,
            translations: $request->input('translations', []),
            viewCount:    $request->filled('view_count') ? (int) $request->input('view_count') : null,
            imageMediaProvided: $request->has('image_media_id') || $request->boolean('image_remove'),
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
            imageMediaId: isset($data['image_media_id']) ? (int) $data['image_media_id'] : (isset($data['imageMediaId']) ? (int) $data['imageMediaId'] : null),
            isFeatured:   (bool) ($data['is_featured'] ?? $data['isFeatured'] ?? false),
            publishedAt:  $data['published_at'] ?? $data['publishedAt'] ?? null,
            translations: $data['translations'] ?? [],
            viewCount:    isset($data['view_count']) ? (int) $data['view_count'] : (isset($data['viewCount']) ? (int) $data['viewCount'] : null),
            imageMediaProvided: array_key_exists('image_media_id', $data) || array_key_exists('imageMediaId', $data),
        );
    }

    /** Dữ liệu ghi vào bảng posts */
    public function toArray(): array
    {
        $data = [
            'status'       => $this->status,
            'author_id'    => $this->authorId,
            'is_featured'  => $this->isFeatured,
            'published_at' => $this->publishedAt,
        ];

        if ($this->imageMediaProvided) {
            $data['image'] = $this->imageMediaId;
        }

        // Chỉ ghi khi form gửi lên, tránh reset lượt xem ở các luồng không có trường này.
        if ($this->viewCount !== null) {
            $data['view_count'] = $this->viewCount;
        }

        return $data;
    }
}