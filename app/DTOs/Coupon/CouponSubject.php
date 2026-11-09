<?php

namespace App\DTOs\Coupon;

class CouponSubject
{
    public function __construct(
        public readonly ?int $userId = null,
        public readonly ?string $guestEmail = null
    ) {}
}
