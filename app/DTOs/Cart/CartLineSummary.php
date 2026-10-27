<?php

namespace App\DTOs\Cart;

use App\Models\Product;
use App\Models\ProductVariant;

class CartLineSummary
{
    /**
     * @param array<int, string> $issues
     */
    public function __construct(
        public readonly string $itemUuid,
        public readonly string $productUuid,
        public readonly ?string $variantUuid,
        public readonly string $name,
        public readonly ?string $variantName,
        public readonly ?string $sku,
        public readonly ?string $imageUrl,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly ?float $comparePrice,
        public readonly float $snapshotPrice,
        public readonly float $lineTotal,
        public readonly bool $priceChanged,
        public readonly bool $isOnSale,
        public readonly int $maxQuantity,
        public readonly array $issues = [],
        public readonly ?Product $product = null,
        public readonly ?ProductVariant $variant = null
    ) {}
}
