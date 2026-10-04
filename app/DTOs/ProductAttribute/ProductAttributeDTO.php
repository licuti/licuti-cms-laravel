<?php

namespace App\DTOs\ProductAttribute;

use Illuminate\Http\Request;
use App\Enums\AttributeType;

class ProductAttributeDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $type = AttributeType::SELECT->value,
        public readonly bool $isFilterable = true,
        public readonly int $displayOrder = 0,
        public readonly array $translations = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            code: (string) $request->input('code'),
            type: (string) $request->input('type', AttributeType::SELECT->value),
            isFilterable: $request->boolean('is_filterable', false),
            displayOrder: (int) $request->input('display_order', 0),
            translations: $request->input('translations', [])
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