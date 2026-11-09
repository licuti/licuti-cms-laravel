<?php

namespace App\DTOs\Cart;

class ApplyCouponDTO
{
    public function __construct(
        public readonly string $code,
        public readonly ?string $email = null
    ) {}
}
