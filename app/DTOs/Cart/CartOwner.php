<?php

namespace App\DTOs\Cart;

class CartOwner
{
    public function __construct(
        public readonly ?int $userId = null,
        public readonly ?string $token = null
    ) {}
}
