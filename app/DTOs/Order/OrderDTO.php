<?php

namespace App\DTOs\Order;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;

class OrderDTO
{
    /**
     * @param array<int, array{
     *     product_id?: int|null,
     *     product_variant_id?: int|null,
     *     product_name: string,
     *     product_sku?: string|null,
     *     unit_price: float,
     *     quantity: int,
     *     discount_amount?: float,
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
        public readonly ?int $paymentMethodId,
        public readonly array $shippingAddress,
        public readonly array $items,
        public readonly float $subtotal,
        public readonly float $discountAmount = 0.0,
        public readonly float $shippingFee = 0.0,
        public readonly float $taxAmount = 0.0,
        public readonly float $totalAmount = 0.0,
        public readonly ?string $notes = null,
        public readonly OrderStatus $status = OrderStatus::PENDING,
        public readonly string $currency = 'VND'
    ) {}

    public static function fromRequest(Request $request): self
    {
        $items = (array) $request->input('items', []);
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

        $subtotal = 0.0;
        foreach ($items as $item) {
            $price = (float) ($item['unit_price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);
            $itemDiscount = (float) ($item['discount_amount'] ?? 0);
            $subtotal += ($price * $qty) - $itemDiscount;
        }

        $discount = (float) $request->input('discount_amount', 0);
        $shippingFee = (float) $request->input('shipping_fee', 0);
        $tax = (float) $request->input('tax_amount', 0);
        $total = max(0, $subtotal - $discount + $shippingFee + $tax);

        $statusInput = $request->input('status', OrderStatus::PENDING->value);
        $status = OrderStatus::tryFrom($statusInput) ?? OrderStatus::PENDING;

        return new self(
            userId: $request->input('user_id') ? (int) $request->input('user_id') : auth()->id(),
            paymentMethodId: $request->input('payment_method_id') ? (int) $request->input('payment_method_id') : null,
            shippingAddress: $shipping,
            items: $items,
            subtotal: $subtotal,
            discountAmount: $discount,
            shippingFee: $shippingFee,
            taxAmount: $tax,
            totalAmount: $total,
            notes: $request->input('notes'),
            status: $status,
            currency: $request->input('currency', 'VND')
        );
    }
}