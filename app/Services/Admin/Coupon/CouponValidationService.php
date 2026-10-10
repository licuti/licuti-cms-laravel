<?php

namespace App\Services\Admin\Coupon;

use App\Models\Coupon;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class CouponValidationService
{
    public function __construct(
        private readonly CouponRepositoryInterface $repository
    ) {
    }

    /**
     * Validate a coupon before applying
     */
    public function validate(string $code, float $cartTotal, ?int $userId = null, ?string $guestEmail = null): array
    {
        if (!$userId && !$guestEmail) {
            throw new Exception(__('Vui lòng đăng nhập hoặc cung cấp email để sử dụng mã giảm giá.'));
        }

        /** @var Coupon $coupon */
        $coupon = $this->repository->findByCode($code);

        if (!$coupon || !$coupon->is_active) {
            throw new Exception(__('Mã giảm giá không tồn tại hoặc đã bị vô hiệu hóa.'));
        }

        // Check dates
        if ($coupon->start_date && $coupon->start_date->isFuture()) {
            throw new Exception(__('Mã giảm giá chưa đến thời gian áp dụng.'));
        }

        if ($coupon->end_date && $coupon->end_date->isPast()) {
            throw new Exception(__('Mã giảm giá đã hết hạn sử dụng.'));
        }

        // Check min order value
        if ($coupon->min_order_value > 0 && $cartTotal < $coupon->min_order_value) {
            throw new Exception(__('Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã này.'));
        }

        // Check global usage limit
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new Exception(__('Mã giảm giá đã hết lượt sử dụng.'));
        }

        // Check per-user usage limit
        if ($coupon->usage_per_user !== null) {
            $query = $coupon->usages();
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('guest_email', $guestEmail);
            }
            
            $userUsages = $query->count();
            if ($userUsages >= $coupon->usage_per_user) {
                throw new Exception(__('Bạn đã hết lượt sử dụng mã giảm giá này.'));
            }
        }

        // Calculate discount
        $discountAmount = 0;
        if ($coupon->type->value === 'percent') {
            $discountAmount = ($cartTotal * $coupon->value) / 100;
            if ($coupon->max_discount_amount && $discountAmount > $coupon->max_discount_amount) {
                $discountAmount = $coupon->max_discount_amount;
            }
        } elseif ($coupon->type->value === 'fixed') {
            $discountAmount = $coupon->value;
            // fixed discount shouldn't exceed cart total
            if ($discountAmount > $cartTotal) {
                $discountAmount = $cartTotal;
            }
        } elseif ($coupon->type->value === 'free_shipping') {
            // Free shipping logic might be handled separately, return value 0 or specific flag
            // Assuming we just mark it as valid for now
        }

        return [
            'is_valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $discountAmount
        ];
    }

    /**
     * Deduct coupon usage (should be called inside a DB Transaction during checkout)
     */
    public function applyAndDeduct(Coupon $coupon, float $discountAmount, ?int $userId = null, ?string $guestEmail = null, ?int $orderId = null): void
    {
        // Pessimistic Lock to ensure no race condition on usage_limit
        $lockedCoupon = Coupon::where('id', $coupon->id)->lockForUpdate()->first();
        
        if ($lockedCoupon->usage_limit !== null && $lockedCoupon->used_count >= $lockedCoupon->usage_limit) {
            throw new Exception(__('Mã giảm giá đã hết lượt sử dụng trong lúc thanh toán.'));
        }

        // Create pivot record
        $lockedCoupon->usages()->create([
            'user_id' => $userId,
            'guest_email' => $userId ? null : $guestEmail, // user_id takes precedence
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'used_at' => now(),
        ]);

        // Increment usage
        $lockedCoupon->increment('used_count');
    }
}
