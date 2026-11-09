<?php

namespace App\Contracts\Shipping;

use Illuminate\Support\Collection;

interface ShippingFeeCalculatorInterface
{
    /**
     * @param Collection<int, \App\DTOs\Cart\CartLineSummary> $lines
     */
    public function calculate(Collection $lines, bool $isFreeShippingCoupon = false): float;
}
