<?php

namespace App\DTOs\Tag;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,
        public readonly int $display_order = 0,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->input('name'),
            slug: $request->input('slug') ? Str::slug($request->input('slug')) : null,
            display_order: (int) $request->input('display_order', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'slug'          => $this->slug,
            'display_order' => $this->display_order,
        ];
    }
}