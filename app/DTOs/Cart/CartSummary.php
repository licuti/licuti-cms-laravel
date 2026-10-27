<?php

namespace App\DTOs\Cart;

use Illuminate\Support\Collection;

class CartSummary
{
    /**
     * @param Collection<int, CartLineSummary> $lines
     */
    public function __construct(
        public readonly Collection $lines,
        public readonly int $itemCount,
        public readonly float $subtotal,
        public readonly float $discountAmount,
        public readonly float $shippingFee,
        public readonly float $grandTotal,
        public readonly ?AppliedCoupon $coupon = null,
        public readonly bool $hasIssues = false,
        public readonly bool $hasSaleItems = false,
        public readonly ?string $couponWarning = null,
        public readonly array $issues = []
    ) {}
}
