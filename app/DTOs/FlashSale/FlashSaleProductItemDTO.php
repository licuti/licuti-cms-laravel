<?php

namespace App\DTOs\FlashSale;

class FlashSaleProductItemDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly ?int $productVariantId,
        public readonly float $flashPrice,
        public readonly int $quantityLimit,
        public readonly int $limitPerUser = 0,
        public readonly int $sortOrder = 0
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            productId: (int) $item['product_id'],
            productVariantId: !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null,
            flashPrice: (float) $item['flash_price'],
            quantityLimit: (int) $item['quantity_limit'],
            limitPerUser: isset($item['limit_per_user']) ? (int) $item['limit_per_user'] : 0,
            sortOrder: isset($item['sort_order']) ? (int) $item['sort_order'] : 0
        );
    }

    public function toArray(): array
    {
        return [
            'product_id'         => $this->productId,
            'product_variant_id' => $this->productVariantId,
            'variant_key'        => sprintf('%d-%d', $this->productId, $this->productVariantId ?? 0),
            'flash_price'        => $this->flashPrice,
            'quantity_limit'     => $this->quantityLimit,
            'limit_per_user'     => $this->limitPerUser,
            'sort_order'         => $this->sortOrder,
        ];
    }
}
