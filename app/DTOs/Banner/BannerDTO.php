<?php

namespace App\DTOs\Banner;

use Illuminate\Http\Request;

class BannerDTO
{
    public function __construct(
        public readonly string $position = 'home_slider',
        public readonly ?string $link = null,
        public readonly string $target = '_self',
        public readonly int $displayOrder = 0,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly bool $isActive = true,
        public readonly array $translations = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            position: (string) $request->input('position', 'home_slider'),
            link: $request->filled('link') ? (string) $request->input('link') : null,
            target: (string) $request->input('target', '_self'),
            displayOrder: (int) $request->input('display_order', 0),
            startDate: $request->filled('start_date') ? (string) $request->input('start_date') : null,
            endDate: $request->filled('end_date') ? (string) $request->input('end_date') : null,
            isActive: $request->boolean('is_active', true),
            translations: $request->input('translations', [])
        );
    }

    public function toArray(): array
    {
        return [
            'position'      => $this->position,
            'link'          => $this->link,
            'target'        => $this->target,
            'display_order' => $this->displayOrder,
            'start_date'    => $this->startDate,
            'end_date'      => $this->endDate,
            'is_active'     => $this->isActive,
        ];
    }
}