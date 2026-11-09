<?php

namespace App\DTOs\FlashSale;

use Illuminate\Http\Request;

class FlashSaleDTO
{
    /**
     * @param FlashSaleProductItemDTO[] $products
     */
    public function __construct(
        public readonly string $startsAt,
        public readonly string $endsAt,
        public readonly bool $isActive = true,
        public readonly array $translations = [],
        public readonly array $products = [],
        public readonly ?int $createdBy = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        $products = [];
        foreach ($request->input('products', []) as $productData) {
            $products[] = FlashSaleProductItemDTO::fromArray($productData);
        }

        return new self(
            startsAt: (string) $request->input('starts_at'),
            endsAt: (string) $request->input('ends_at'),
            isActive: $request->boolean('is_active', true),
            translations: (array) $request->input('translations', []),
            products: $products,
            createdBy: $request->user()?->id
        );
    }

    public function toArray(): array
    {
        return [
            'starts_at'  => $this->startsAt,
            'ends_at'    => $this->endsAt,
            'is_active'  => $this->isActive,
            'created_by' => $this->createdBy,
        ];
    }
}