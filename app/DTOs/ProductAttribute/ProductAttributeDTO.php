<?php

namespace App\DTOs\ProductAttribute;

use Illuminate\Http\Request;

class ProductAttributeDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $type = 'select',
        public readonly bool $isFilterable = true,
        public readonly int $displayOrder = 0,
        public readonly array $translations = [],
        public readonly array $values = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            code: (string) $request->input('code'),
            type: (string) $request->input('type', 'select'),
            isFilterable: $request->boolean('is_filterable', true),
            displayOrder: (int) $request->input('display_order', 0),
            translations: $request->input('translations', []),
            values: array_values(array_filter($request->input('values', []), fn($item) => !empty($item['value'])))
        );
    }

    public function toArray(): array
    {
        return [
            'code'          => $this->code,
            'type'          => $this->type,
            'is_filterable' => $this->isFilterable,
            'display_order' => $this->displayOrder,
        ];
    }
}