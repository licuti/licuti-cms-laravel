<?php

namespace App\DTOs\Page;

use Illuminate\Http\Request;

class PageDTO
{
    public function __construct(
        public readonly ?int $display_order,
        public readonly bool $is_active,
        public readonly ?string $meta_title,
        public readonly ?string $meta_description,
        public readonly ?string $meta_keywords,
        public readonly array $translations = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            display_order: $request->input('display_order'),
            is_active: $request->boolean('is_active', true),
            meta_title: $request->input('meta_title'),
            meta_description: $request->input('meta_description'),
            meta_keywords: $request->input('meta_keywords'),
            translations: $request->input('translations', [])
        );
    }

    public function toArray(): array
    {
        return [
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
        ];
    }
}