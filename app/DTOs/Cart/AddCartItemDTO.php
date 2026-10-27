<?php

namespace App\DTOs\Cart;

class AddCartItemDTO
{
    public function __construct(
        public readonly string $productUuid,
        public readonly ?string $variantUuid,
        public readonly int $quantity,
        public readonly ?array $meta = null
    ) {}
}
