<?php

namespace App\Actions\Order;

use App\Enums\RedemptionStatus;
use App\Exceptions\FlashSale\FlashSaleQuotaExceededException;
use App\Models\FlashSaleProduct;
use App\Models\FlashSaleRedemption;
use App\Models\Order;

/**
 * Giữ chỗ (reserve) suất Flash Sale NGAY KHI TẠO ĐƠN — quy tắc đã chốt với chủ dự án 2026-11-03.
 *
 * Lý do reserve lúc đặt hàng thay vì lúc webhook thành công: nếu chờ webhook, hai khách có thể
 * cùng thấy "còn hàng" và cùng đặt → bán vượt quantity_limit.
 *
 * Chạy BÊN TRONG transaction của PlaceOrderAction, lock dòng flash_sale_products theo thứ tự
 * id tăng dần (nhất quán để tránh deadlock, giống cách sort khóa kho trong PlaceOrderAction).
 */
class ReserveFlashSaleQuotaAction
{
    /**
     * @return int Tổng số suất đã giữ
     */
    public function execute(Order $order, ?int $userId = null, ?string $guestEmail = null): int
    {
        $items = $order->relationLoaded('items') ? $order->items : $order->items()->get();

        $flashItems = $items
            ->filter(fn ($item) => !empty($item->flash_sale_product_id))
            ->sortBy('flash_sale_product_id')
            ->values();

        if ($flashItems->isEmpty()) {
            return 0;
        }

        $reserved = 0;

        foreach ($flashItems as $item) {
            $quantity = (int) $item->quantity;

            /** @var FlashSaleProduct|null $offer */
            $offer = FlashSaleProduct::query()
                ->whereKey($item->flash_sale_product_id)
                ->lockForUpdate()
                ->first();

            if (!$offer) {
                throw new FlashSaleQuotaExceededException(
                    __('Suất Flash Sale của ":name" không còn tồn tại. Vui lòng kiểm tra lại giỏ hàng.', ['name' => $item->product_name])
                );
            }

            $offer->loadMissing('flashSale');

            if (!$offer->flashSale || !$offer->flashSale->isRunning()) {
                throw new FlashSaleQuotaExceededException(
                    __('Chiến dịch Flash Sale của ":name" đã kết thúc. Vui lòng mua với giá thường.', ['name' => $item->product_name])
                );
            }

            if ($offer->quantity_limit !== null && ((int) $offer->sold_quantity + $quantity) > (int) $offer->quantity_limit) {
                throw new FlashSaleQuotaExceededException(
                    __('Sản phẩm ":name" chỉ còn :remaining suất Flash Sale (đã giữ :sold/:limit).', [
                        'name'      => $item->product_name,
                        'remaining' => max(0, (int) $offer->quantity_limit - (int) $offer->sold_quantity),
                        'sold'      => (int) $offer->sold_quantity,
                        'limit'     => (int) $offer->quantity_limit,
                    ])
                );
            }

            $this->assertWithinUserLimit($offer, $item, $quantity, $userId, $guestEmail);

            $offer->sold_quantity = (int) $offer->sold_quantity + $quantity;
            $offer->save();

            FlashSaleRedemption::create([
                'flash_sale_product_id' => $offer->id,
                'order_item_id'         => $item->id,
                'user_id'               => $userId,
                'guest_email'           => $userId === null ? $guestEmail : null,
                'quantity'              => $quantity,
                'status'                => RedemptionStatus::RESERVED,
                'released_at'           => null,
            ]);

            $reserved += $quantity;
        }

        return $reserved;
    }

    private function assertWithinUserLimit(
        FlashSaleProduct $offer,
        object $item,
        int $quantity,
        ?int $userId,
        ?string $guestEmail
    ): void {
        // limit_per_user là unsignedSmallInteger DEFAULT 0 => 0 nghĩa là KHÔNG giới hạn.
        $perUserLimit = (int) $offer->limit_per_user;
        if ($perUserLimit <= 0) {
            return;
        }

        if ($userId === null && ($guestEmail === null || $guestEmail === '')) {
            return; // Không xác định được khách nào -> không thể áp giới hạn mỗi khách.
        }

        $already = FlashSaleRedemption::query()
            ->where('flash_sale_product_id', $offer->id)
            ->where('status', RedemptionStatus::RESERVED->value)
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->when($userId === null, fn ($q) => $q->where('guest_email', $guestEmail))
            ->sum('quantity');

        if (((int) $already + $quantity) > $perUserLimit) {
            throw new FlashSaleQuotaExceededException(
                __('":name" giới hạn tối đa :max suất cho mỗi khách Flash Sale. Bạn đã giữ :used suất.', [
                    'name' => $item->product_name,
                    'max'  => $perUserLimit,
                    'used' => (int) $already,
                ])
            );
        }
    }
}
