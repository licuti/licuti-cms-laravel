<?php

namespace App\Services\Shared\Shipping;

use App\Contracts\Shipping\ShippingFeeCalculatorInterface;
use App\Core\Enums\ProductType;
use Illuminate\Support\Collection;

class FlatProductShippingCalculator implements ShippingFeeCalculatorInterface
{
    /**
     * @param Collection<int, \App\DTOs\Cart\CartLineSummary> $lines
     */
    public function calculate(Collection $lines, bool $isFreeShippingCoupon = false): float
    {
        if ($isFreeShippingCoupon || $lines->isEmpty()) {
            return 0.0;
        }

        $totalShipping = 0.0;
        foreach ($lines as $line) {
            $product = $line->product;
            if (!$product) {
                continue;
            }

            // Chỉ sản phẩm physical và không được miễn phí ship mới tính phí
            $isPhysical = ($product->product_type === ProductType::PHYSICAL->value) || empty($product->product_type);
            if ($isPhysical && !$product->is_free_shipping) {
                $shippingFee = (float) ($product->shipping_fee ?? 0);
                $totalShipping += $shippingFee;
            }
        }

        return $totalShipping;
    }
}
