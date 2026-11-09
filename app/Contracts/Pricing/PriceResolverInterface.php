<?php

namespace App\Contracts\Pricing;

use App\DTOs\Pricing\PriceInfo;
use App\Models\Product;
use App\Models\ProductVariant;

interface PriceResolverInterface
{
    public function resolve(Product $product, ?ProductVariant $variant = null): PriceInfo;
}
