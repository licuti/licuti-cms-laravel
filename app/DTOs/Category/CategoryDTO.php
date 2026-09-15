<?php

namespace App\DTOs\Category;

use Illuminate\Http\Request;

class CategoryDTO
{
    public function __construct(
        public readonly ?int $parent_id,
        public readonly ?string $image,
        public readonly bool $remove_image,
        public readonly ?string $icon,
        public readonly bool $is_active,
        public readonly int $display_order,
        public readonly array $translations = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            parent_id: $request->input('parent_id') ? (int) $request->input('parent_id') : null,
            image: $request->input('image_media_uuid'),
            remove_image: $request->boolean('remove_image', false),
            icon: $request->input('icon'),
            is_active: $request->boolean('is_active', true),
            display_order: (int) $request->input('display_order', 0),
            translations: $request->input('translations', [])
        );
    }

    public function toArray(): array
    {
        return [
            'parent_id' => $this->parent_id,
            'image' => $this->image,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'display_order' => $this->display_order,
        ];
    }
}