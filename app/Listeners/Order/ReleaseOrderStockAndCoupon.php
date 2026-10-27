<?php

namespace App\Listeners\Order;

use App\Events\Order\OrderCancelled;
use App\Services\Admin\Coupon\CouponValidationService;
use App\Services\Shared\Product\StockService;

class ReleaseOrderStockAndCoupon
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly CouponValidationService $couponValidationService
    ) {}

    /**
     * Đồng bộ (không Queue) có chủ đích để rollback kho & coupon ngay lập tức trong transaction hủy đơn.
     */
    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        // 1. Hoàn lại tồn kho cho từng sản phẩm
        $items = $order->relationLoaded('items') ? $order->items : $order->items()->get();
        foreach ($items as $item) {
            if ($item->product_id) {
                $this->stockService->incrementStock(
                    (int) $item->product_id,
                    $item->product_variant_id ? (int) $item->product_variant_id : null,
                    (int) $item->quantity
                );
            }
        }

        // 2. Hoàn lại lượt sử dụng mã giảm giá
        if ($order->coupon_id) {
            $this->couponValidationService->releaseUsage($order->id);
        }
    }
}
