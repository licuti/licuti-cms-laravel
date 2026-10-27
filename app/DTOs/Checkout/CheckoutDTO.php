<?php

namespace App\DTOs\Checkout;

use Illuminate\Http\Request;

class CheckoutDTO
{
    /**
     * @param array<int, array{
     *     product_id: int,
     *     product_variant_id?: int|null,
     *     quantity: int,
     *     meta?: array|null
     * }> $items
     * @param array{
     *     recipient_name: string,
     *     phone: string,
     *     email?: string|null,
     *     address_line: string,
     *     ward?: string|null,
     *     district?: string|null,
     *     province?: string|null,
     *     country?: string,
     *     zip_code?: string|null
     * } $shippingAddress
     */
    public function __construct(
        public readonly ?int $userId,
        public readonly string $paymentMethodCode,
        public readonly array $shippingAddress,
        public readonly array $items = [],
        public readonly ?string $cartToken = null,
        public readonly ?string $couponCode = null,
        public readonly ?string $notes = null,
        public readonly string $currency = 'VND'
    ) {}

    public static function fromRequest(Request $request): self
    {
        $shipping = (array) $request->input('shipping_address', [
            'recipient_name' => $request->input('recipient_name', ''),
            'phone'          => $request->input('phone', ''),
            'email'          => $request->input('email'),
            'address_line'   => $request->input('address_line', ''),
            'ward'           => $request->input('ward'),
            'district'       => $request->input('district'),
            'province'       => $request->input('province'),
            'country'        => $request->input('country', 'VN'),
            'zip_code'       => $request->input('zip_code'),
        ]);

        $userId = $request->user('sanctum')?->id ?: $request->user()?->id;
        $cartToken = $request->input('cart_token') ?: $request->header('X-Cart-Token');

        return new self(
            userId: $userId,
            paymentMethodCode: (string) $request->input('payment_method_code', 'cod'),
            shippingAddress: $shipping,
            items: (array) $request->input('items', []),
            cartToken: $cartToken ? (string) $cartToken : null,
            couponCode: $request->input('coupon_code') ? (string) $request->input('coupon_code') : null,
            notes: $request->input('notes'),
            currency: (string) $request->input('currency', 'VND')
        );
    }
}
