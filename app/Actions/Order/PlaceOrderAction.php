<?php

namespace App\Actions\Order;

use App\Actions\Cart\MarkCartConvertedAction;
use App\DTOs\Checkout\CheckoutContext;
use App\DTOs\Order\OrderDTO;
use App\Enums\OrderStatus;
use App\Exceptions\Cart\InsufficientStockException;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\Admin\Coupon\CouponValidationService;
use App\Services\Admin\Order\OrderService;
use App\Services\Shared\Product\StockService;
use Illuminate\Support\Facades\DB;

class PlaceOrderAction
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly StockService $stockService,
        private readonly CouponValidationService $couponValidationService,
        private readonly MarkCartConvertedAction $markCartConvertedAction
    ) {}

    public function execute(CheckoutContext $context, PaymentMethod $paymentMethod): Order
    {
        // Sắp xếp thứ tự khóa kho nhất quán theo (product_id, variant_id) để loại trừ deadlock
        $lockOrderedItems = collect($context->orderItems)
            ->sortBy(fn ($i) => [(int) $i['product_id'], (int) ($i['product_variant_id'] ?? 0)])
            ->values();

        return DB::transaction(function () use ($context, $paymentMethod, $lockOrderedItems) {
            // 1. Trừ tồn kho từng dòng hàng (Atomic via StockService theo thứ tự khóa nhất quán)
            foreach ($lockOrderedItems as $item) {
                $decremented = $this->stockService->decrementStock(
                    (int) $item['product_id'],
                    !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null,
                    (int) $item['quantity']
                );

                if (!$decremented) {
                    throw new InsufficientStockException(
                        __('Sản phẩm ":name" không đủ số lượng trong kho.', [
                            'name' => $item['product_name'] ?? "ID #{$item['product_id']}",
                        ])
                    );
                }
            }

            // 2. Tạo đơn hàng
            $orderDTO = new OrderDTO(
                userId: $context->userId,
                paymentMethodId: $paymentMethod->id,
                shippingAddress: $context->shippingAddress,
                items: $context->orderItems,
                subtotal: $context->subtotal,
                discountAmount: $context->discountAmount,
                shippingFee: $context->shippingFee,
                taxAmount: 0.0,
                totalAmount: $context->totalAmount,
                notes: $context->notes,
                status: OrderStatus::PENDING,
                currency: $context->currency,
                couponId: $context->coupon?->id,
                couponCode: $context->couponCode
            );

            $order = $this->orderService->create($orderDTO);

            // 3. Khấu trừ lượt sử dụng mã giảm giá (nếu có)
            if ($context->coupon && $context->discountAmount > 0) {
                $this->couponValidationService->applyAndDeduct(
                    coupon: $context->coupon,
                    discountAmount: $context->discountAmount,
                    userId: $context->userId,
                    guestEmail: $context->guestEmail,
                    orderId: $order->id
                );
            }

            // 4. Đánh dấu giỏ hàng đã chuyển thành đơn hàng
            if ($context->cart) {
                $this->markCartConvertedAction->execute($context->cart, $order);
            }

            return $order;
        }, attempts: 3);
    }
}
