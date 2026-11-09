<?php

namespace App\DTOs\Cart;

use App\Models\Coupon;

class AppliedCoupon
{
    public function __construct(
        public readonly Coupon $coupon,
        public readonly string $code,
        public readonly string $type,
        public readonly float $discountAmount,
        public readonly bool $freeShipping = false
    ) {}
}
