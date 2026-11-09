<?php

namespace App\DTOs\Checkout;

use App\Models\Cart;
use App\Models\Coupon;

class CheckoutContext
{
    /**
     * @param array<int, array{
     *     product_id: int,
     *     product_variant_id: ?int,
     *     product_name: string,
     *     product_sku: ?string,
     *     unit_price: float,
     *     quantity: int,
     *     discount_amount: float,
     *     subtotal: float,
     *     meta: ?array
     * }> $orderItems
     */
    public function __construct(
        public readonly ?int $userId,
        public readonly string $paymentMethodCode,
        public readonly array $shippingAddress,
        public readonly array $orderItems,
        public readonly float $subtotal,
        public readonly float $discountAmount,
        public readonly float $shippingFee,
        public readonly float $totalAmount,
        public readonly ?Cart $cart = null,
        public readonly ?Coupon $coupon = null,
        public readonly ?string $couponCode = null,
        public readonly ?string $notes = null,
        public readonly string $currency = 'VND',
        public readonly ?string $guestEmail = null
    ) {}
}
