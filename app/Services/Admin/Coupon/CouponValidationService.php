<?php

namespace App\Services\Admin\Coupon;

use App\DTOs\Cart\AppliedCoupon;
use App\DTOs\Coupon\CouponSubject;
use App\Exceptions\Coupon\CouponException;
use App\Models\Coupon;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CouponValidationService
{
    public function __construct(
        private readonly CouponRepositoryInterface $repository
    ) {
    }

    /**
     * Validate a coupon before applying (legacy/direct method)
     */
    public function validate(string $code, float $cartTotal, ?int $userId = null, ?string $guestEmail = null): array
    {
        $subject = new CouponSubject(userId: $userId, guestEmail: $guestEmail);
        $applied = $this->validateForCart($code, $cartTotal, $subject, false);

        return [
            'is_valid'        => true,
            'coupon'          => $applied->coupon,
            'discount_amount' => $applied->discountAmount,
        ];
    }

    /**
     * Validate a coupon for Cart / Checkout flow
     *
     * @throws CouponException
     */
    public function validateForCart(string $code, float $cartTotal, CouponSubject $subject, bool $hasSaleItems = false): AppliedCoupon
    {
        if (!$subject->userId && !$subject->guestEmail) {
            throw new CouponException(__('Vui lòng đăng nhập hoặc cung cấp email để sử dụng mã giảm giá.'));
        }

        /** @var Coupon|null $coupon */
        $coupon = $this->repository->findByCode($code);

        if (!$coupon || !$coupon->is_active) {
            throw new CouponException(__('Mã giảm giá không tồn tại hoặc đã bị vô hiệu hóa.'));
        }

        // Check dates
        if ($coupon->start_date && $coupon->start_date->isFuture()) {
            throw new CouponException(__('Mã giảm giá chưa đến thời gian áp dụng.'));
        }

        if ($coupon->end_date && $coupon->end_date->isPast()) {
            throw new CouponException(__('Mã giảm giá đã hết hạn sử dụng.'));
        }

        // Check cannot combine with sale items
        if ($coupon->cannot_combine_with_sale_items && $hasSaleItems) {
            throw new CouponException(__('Mã giảm giá không áp dụng cho đơn hàng chứa sản phẩm đang giảm giá.'));
        }

        // Check min order value
        if ($coupon->min_order_value > 0 && $cartTotal < $coupon->min_order_value) {
            throw new CouponException(__('Đơn hàng chưa đạt giá trị tối thiểu :min để áp dụng mã này.', [
                'min' => number_format((float) $coupon->min_order_value) . ' đ'
            ]));
        }

        // Check global usage limit
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new CouponException(__('Mã giảm giá đã hết lượt sử dụng.'));
        }

        // Check per-user usage limit
        if ($coupon->usage_per_user !== null) {
            $query = $coupon->usages();
            if ($subject->userId) {
                $query->where('user_id', $subject->userId);
            } else {
                $query->where('guest_email', $subject->guestEmail);
            }

            $userUsages = $query->count();
            if ($userUsages >= $coupon->usage_per_user) {
                throw new CouponException(__('Bạn đã hết lượt sử dụng mã giảm giá này.'));
            }
        }

        // Calculate discount
        $discountAmount = 0.0;
        $isFreeShipping = false;

        $typeValue = is_string($coupon->type) ? $coupon->type : $coupon->type->value;

        if ($typeValue === 'percent') {
            $discountAmount = ($cartTotal * (float) $coupon->value) / 100;
            if ($coupon->max_discount_amount && $discountAmount > (float) $coupon->max_discount_amount) {
                $discountAmount = (float) $coupon->max_discount_amount;
            }
        } elseif ($typeValue === 'fixed') {
            $discountAmount = (float) $coupon->value;
            if ($discountAmount > $cartTotal) {
                $discountAmount = $cartTotal;
            }
        } elseif ($typeValue === 'free_shipping') {
            $isFreeShipping = true;
            $discountAmount = 0.0;
        }

        return new AppliedCoupon(
            coupon: $coupon,
            code: $coupon->code,
            type: $typeValue,
            discountAmount: round($discountAmount, 2),
            freeShipping: $isFreeShipping
        );
    }

    /**
     * Deduct coupon usage (should be called inside a DB Transaction during checkout)
     *
     * @throws CouponException
     */
    public function applyAndDeduct(Coupon $coupon, float $discountAmount, ?int $userId = null, ?string $guestEmail = null, ?int $orderId = null): void
    {
        // Pessimistic Lock to ensure no race condition on usage_limit
        /** @var Coupon $lockedCoupon */
        $lockedCoupon = Coupon::where('id', $coupon->id)->lockForUpdate()->first();

        if ($lockedCoupon->usage_limit !== null && $lockedCoupon->used_count >= $lockedCoupon->usage_limit) {
            throw new CouponException(__('Mã giảm giá đã hết lượt sử dụng trong lúc thanh toán.'));
        }

        // Create pivot record
        $lockedCoupon->usages()->create([
            'user_id'         => $userId,
            'guest_email'     => $userId ? null : $guestEmail,
            'order_id'        => $orderId,
            'discount_amount' => $discountAmount,
            'used_at'         => now(),
        ]);

        // Increment usage
        $lockedCoupon->increment('used_count');
    }

    /**
     * Hoàn lại lượt sử dụng coupon khi đơn hàng bị hủy
     */
    public function releaseUsage(int $orderId): int
    {
        return $this->repository->releaseUsageByOrder($orderId);
    }
}
