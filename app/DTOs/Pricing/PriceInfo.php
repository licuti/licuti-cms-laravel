<?php

namespace App\DTOs\Pricing;

class PriceInfo
{
    public function __construct(
        public readonly float $unitPrice,
        public readonly ?float $comparePrice,
        public readonly bool $isOnSale,
        public readonly string $source = 'base'
    ) {}
}
