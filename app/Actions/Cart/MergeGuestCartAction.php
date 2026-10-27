<?php

namespace App\Actions\Cart;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MergeGuestCartAction
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartItemRepositoryInterface $cartItemRepository
    ) {}

    public function execute(Cart $guestCart, User $user): Cart
    {
        return DB::transaction(function () use ($guestCart, $user) {
            // Lấy hoặc tạo giỏ hàng active của user
            /** @var Cart|null $userCart */
            $userCart = $this->cartRepository->findActiveByUserId($user->id);

            if (!$userCart) {
                /** @var Cart $userCart */
                $userCart = $this->cartRepository->create([
                    'uuid'             => (string) Str::uuid(),
                    'user_id'          => $user->id,
                    'currency'         => $guestCart->currency ?: 'VND',
                    'status'           => CartStatus::ACTIVE->value,
                    'last_activity_at' => now(),
                ]);
            }

            if ($guestCart->id === $userCart->id) {
                return $userCart;
            }

            // Khoá cả 2 giỏ theo thứ tự ID tăng dần để chống deadlock
            $cartIds = [$guestCart->id, $userCart->id];
            sort($cartIds);
            Cart::whereIn('id', $cartIds)->lockForUpdate()->get();

            // Nếu giỏ guest đã merge hoặc converted, không làm lại
            if ($guestCart->status !== CartStatus::ACTIVE) {
                return $userCart->fresh(['items', 'coupon']);
            }

            $guestItems = $guestCart->items()->with(['product', 'variant'])->get();

            foreach ($guestItems as $guestItem) {
                $existingUserItem = $this->cartItemRepository->findByLineKey($userCart->id, $guestItem->line_key);
                $product = $guestItem->product;
                $variant = $guestItem->variant;

                $maxCap = (int) config('cart.max_quantity_per_line', 999);
                if ($product?->sold_individually) {
                    $maxCap = 1;
                } elseif ($product?->max_order_quantity) {
                    $maxCap = min($maxCap, (int) $product->max_order_quantity);
                }

                if ($product?->track_inventory && !$product?->allow_backorder) {
                    $stockHolder = $variant ?: $product;
                    $availableStock = max(0, (int) $stockHolder?->stock_quantity);
                    // Nếu kho còn hàng (> 0) thì giới hạn trần theo tồn kho khả dụng.
                    // Nếu kho đã hết hàng (== 0), giữ nguyên số lượng để CartPricingService cảnh báo 'out_of_stock'
                    // giúp khách thấy rõ sản phẩm trong giỏ thay vì âm thầm xóa mất hàng của họ.
                    if ($availableStock > 0) {
                        $maxCap = min($maxCap, $availableStock);
                    }
                }

                if ($existingUserItem) {
                    $combinedQty = $existingUserItem->quantity + $guestItem->quantity;
                    $newQty = max(1, min($maxCap, $combinedQty));

                    $meta = (array) ($existingUserItem->meta ?? []);
                    if ($combinedQty > $maxCap) {
                        $meta['quantity_adjusted_notice'] = __('Số lượng được điều chỉnh về :qty do giới hạn mua hoặc tồn kho.', ['qty' => $newQty]);
                    }

                    $existingUserItem->update(['quantity' => $newQty, 'meta' => $meta ?: null]);
                    $guestItem->delete();
                } else {
                    $newQty = max(1, min($maxCap, $guestItem->quantity));
                    $meta = (array) ($guestItem->meta ?? []);
                    if ($guestItem->quantity > $maxCap) {
                        $meta['quantity_adjusted_notice'] = __('Số lượng được điều chỉnh về :qty do giới hạn mua hoặc tồn kho.', ['qty' => $newQty]);
                    }

                    $guestItem->update([
                        'cart_id'  => $userCart->id,
                        'quantity' => $newQty,
                        'meta'     => $meta ?: null,
                    ]);
                }
            }

            // Giữ coupon của user, nếu user chưa có mà guest có thì chuyển sang
            if (!$userCart->coupon_id && $guestCart->coupon_id) {
                $userCart->update(['coupon_id' => $guestCart->coupon_id]);
            }

            // Đánh dấu giỏ guest là merged
            $guestCart->update(['status' => CartStatus::MERGED->value]);
            $userCart->touchActivity();

            return $userCart->fresh(['items.product.translations', 'items.variant.attributeValues', 'coupon']);
        });
    }
}
