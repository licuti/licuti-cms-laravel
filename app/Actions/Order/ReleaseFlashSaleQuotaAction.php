<?php

namespace App\Actions\Order;

use App\Enums\RedemptionStatus;
use App\Models\FlashSaleProduct;
use App\Models\FlashSaleRedemption;
use App\Models\Order;

/**
 * Hoàn trả suất Flash Sale đã giữ khi đơn bị hủy / thanh toán thất bại / hết hẹn thanh toán.
 *
 * Idempotent: chỉ xử lý redemption còn status = reserved, nên hủy lặp hoặc webhook fail đến muộn
 * không cộng trừ sold_quantity hai lần.
 */
class ReleaseFlashSaleQuotaAction
{
    /**
     * @return int Tổng số suất đã hoàn trả
     */
    public function execute(Order $order): int
    {
        $itemIds = $order->relationLoaded('items')
            ? $order->items->pluck('id')->filter()->all()
            : $order->items()->pluck('id')->all();

        if (empty($itemIds)) {
            return 0;
        }

        $redemptions = FlashSaleRedemption::query()
            ->whereIn('order_item_id', $itemIds)
            ->where('status', RedemptionStatus::RESERVED->value)
            ->orderBy('flash_sale_product_id')
            ->get()
            ->groupBy('flash_sale_product_id');

        if ($redemptions->isEmpty()) {
            return 0;
        }

        $released = 0;

        foreach ($redemptions as $flashSaleProductId => $rows) {
            $quantity = (int) $rows->sum('quantity');

            /** @var FlashSaleProduct|null $offer */
            $offer = FlashSaleProduct::query()
                ->whereKey($flashSaleProductId)
                ->lockForUpdate()
                ->first();

            if ($offer) {
                $offer->sold_quantity = max(0, (int) $offer->sold_quantity - $quantity);
                $offer->save();
            }

            FlashSaleRedemption::query()
                ->whereIn('id', $rows->pluck('id')->all())
                ->update([
                    'status'      => RedemptionStatus::RELEASED->value,
                    'released_at' => now(),
                    'updated_at'  => now(),
                ]);

            $released += $quantity;
        }

        return $released;
    }
}
