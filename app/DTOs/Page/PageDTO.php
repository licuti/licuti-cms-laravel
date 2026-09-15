<?php

namespace App\DTOs\Page;

use App\Core\Enums\ContentStatus;
use App\Core\Enums\PageTemplate;
use Illuminate\Http\Request;

class PageDTO
{
    public function __construct(
        public readonly string $status,
        public readonly ?int $parentId,
        public readonly string $pageTemplate,
        public readonly ?string $image,
        public readonly ?int $display_order,
        public readonly ?string $publishedAt,
        public readonly ?string $meta_title,
        public readonly ?string $meta_description,
        public readonly ?string $meta_keywords,
        public readonly array $translations = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        $image = $request->boolean('image_remove')
            ? null
            : ($request->input('image_uuid') ?: ($request->input('image') ?: null));

        return new self(
            status:          $request->input('status', ContentStatus::DRAFT->value),
            parentId:        $request->input('parent_id') ? (int) $request->input('parent_id') : null,
            pageTemplate:    $request->input('page_template', PageTemplate::DEFAULT->value),
            image:           $image,
            display_order:   $request->input('display_order') !== null ? (int) $request->input('display_order') : 0,
            publishedAt:     $request->input('published_at') ?: null,
            meta_title:      $request->input('meta_title'),
            meta_description: $request->input('meta_description'),
            meta_keywords:   $request->input('meta_keywords'),
            translations:    $request->input('translations', []),
        );
    }

    /**
     * Tạo DTO từ mảng — dùng cho seeder, test, hoặc import data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status:           $data['status'] ?? ContentStatus::DRAFT->value,
            parentId:         $data['parent_id'] ?? $data['parentId'] ?? null,
            pageTemplate:     $data['page_template'] ?? PageTemplate::DEFAULT->value,
            image:            $data['image'] ?? null,
            display_order:    isset($data['display_order']) ? (int) $data['display_order'] : 0,
            publishedAt:      $data['published_at'] ?? $data['publishedAt'] ?? null,
            meta_title:       $data['meta_title'] ?? null,
            meta_description: $data['meta_description'] ?? null,
            meta_keywords:    $data['meta_keywords'] ?? null,
            translations:     $data['translations'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'status'           => $this->status,
            'parent_id'        => $this->parentId,
            'page_template'    => $this->pageTemplate,
            'image'            => $this->image,
            'display_order'    => $this->display_order,
            'published_at'     => $this->publishedAt,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords'    => $this->meta_keywords,
        ];
    }
}
