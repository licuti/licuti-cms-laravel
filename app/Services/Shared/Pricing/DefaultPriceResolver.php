<?php

namespace App\Services\Shared\Pricing;

use App\Contracts\Pricing\PriceResolverInterface;
use App\DTOs\Pricing\PriceInfo;
use App\Models\Product;
use App\Models\ProductVariant;

class DefaultPriceResolver implements PriceResolverInterface
{
    public function resolve(Product $product, ?ProductVariant $variant = null): PriceInfo
    {
        $unitPrice = (float) $product->price;
        $comparePrice = $product->compare_price !== null ? (float) $product->compare_price : null;

        if ($variant) {
            if ($variant->price !== null) {
                $unitPrice = (float) $variant->price;
            }
            if ($variant->compare_price !== null) {
                $comparePrice = (float) $variant->compare_price;
            }
        }

        $isOnSale = $comparePrice !== null && $comparePrice > $unitPrice;

        return new PriceInfo(
            unitPrice: $unitPrice,
            comparePrice: $comparePrice,
            isOnSale: $isOnSale,
            source: 'base'
        );
    }
}
