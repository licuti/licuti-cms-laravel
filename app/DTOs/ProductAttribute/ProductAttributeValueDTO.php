<?php

namespace App\DTOs\ProductAttribute;

use Illuminate\Http\Request;

class ProductAttributeValueDTO
{
    public function __construct(
        public readonly int $attributeId,
        public readonly ?string $colorCode = null,
        public readonly int $displayOrder = 0,
        public readonly array $translations = []
    ) {}

    public static function fromRequest(Request $request, int $attributeId): self
    {
        return new self(
            attributeId: $attributeId,
            colorCode: $request->input('color_code'),
            displayOrder: (int) $request->input('display_order', 0),
            translations: $request->input('translations', [])
        );
    }

    public function toArray(): array
    {
        return [
            'attribute_id'  => $this->attributeId,
            'color_code'    => $this->colorCode,
            'display_order' => $this->displayOrder,
        ];
    }
}
