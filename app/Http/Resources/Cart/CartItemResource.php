<?php

namespace App\Http\Resources\Cart;

use App\DTOs\Cart\CartLineSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * @param CartLineSummary $resource
     */
    public function toArray(Request $request): array
    {
        /** @var CartLineSummary $line */
        $line = $this->resource;

        return [
            'uuid'          => $line->itemUuid,
            'product'       => [
                'uuid'      => $line->productUuid,
                'name'      => $line->name,
                'sku'       => $line->sku,
                'image_url' => $line->imageUrl,
            ],
            'variant'       => $line->variantUuid ? [
                'uuid'      => $line->variantUuid,
                'name'      => $line->variantName,
                'sku'       => $line->sku,
            ] : null,
            'quantity'      => $line->quantity,
            'unit_price'    => $line->unitPrice,
            'compare_price' => $line->comparePrice,
            'line_total'    => $line->lineTotal,
            'price_changed' => $line->priceChanged,
            'is_on_sale'    => $line->isOnSale,
            'max_quantity'  => $line->maxQuantity,
            'issues'        => $line->issues,
            // Flash Sale (Phase 2): storefront cần cờ này để gắn badge "GIÁ SỐC" + đếm ngược suất.
            'is_flash_sale' => $line->isFlashSale,
            'flash_sale'    => $line->isFlashSale ? [
                'flash_sale_product_id' => $line->flashSaleProductId,
                'regular_price'         => $line->regularPrice,
                'flash_price'           => $line->unitPrice,
                'remaining_quantity'    => $line->flashRemainingQuantity === PHP_INT_MAX ? null : $line->flashRemainingQuantity,
                'coupon_eligible'       => !$line->isCouponIneligible(),
            ] : null,
        ];
    }
}
