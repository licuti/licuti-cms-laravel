<?php

namespace App\Http\Resources\Cart;

use App\DTOs\Cart\CartSummary;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * @param array{cart: Cart, summary: CartSummary} $resource
     */
    public function toArray(Request $request): array
    {
        /** @var Cart $cart */
        $cart = $this->resource['cart'];
        /** @var CartSummary $summary */
        $summary = $this->resource['summary'];

        return [
            'cart_token'     => $cart->uuid,
            'currency'       => $cart->currency,
            'status'         => $cart->status->value,
            'items'          => CartItemResource::collection($summary->lines),
            'coupon'         => $summary->coupon ? [
                'code'            => $summary->coupon->code,
                'type'            => $summary->coupon->type,
                'discount_amount' => $summary->coupon->discountAmount,
                'free_shipping'   => $summary->coupon->freeShipping,
            ] : null,
            'totals'         => [
                'item_count'      => $summary->itemCount,
                'subtotal'        => $summary->subtotal,
                'discount_amount' => $summary->discountAmount,
                'shipping_fee'    => $summary->shippingFee,
                'grand_total'     => $summary->grandTotal,
            ],
            'has_issues'     => $summary->hasIssues,
            'has_sale_items' => $summary->hasSaleItems,
            'coupon_warning' => $summary->couponWarning,
        ];
    }
}
