<?php

namespace App\DTOs\Menu;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug = null,
        public readonly ?string $location = null,
        public readonly bool $isActive = true,
        public readonly array $items = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        $name = (string) $request->input('name');
        $slug = $request->filled('slug') 
            ? Str::slug($request->input('slug')) 
            : Str::slug($name);

        return new self(
            name: $name,
            slug: $slug,
            location: $request->filled('location') ? (string) $request->input('location') : null,
            isActive: $request->boolean('is_active', true),
            items: array_values(array_filter($request->input('items', []), fn($item) => !empty($item['title'])))
        );
    }

    public function toArray(): array
    {
        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'location'  => $this->location,
            'is_active' => $this->isActive,
        ];
    }
}