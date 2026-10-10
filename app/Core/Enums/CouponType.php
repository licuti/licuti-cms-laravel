<?php

namespace App\Core\Enums;

enum CouponType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';
    case FREE_SHIPPING = 'free_shipping';

    public function label(): string
    {
        return match($this) {
            self::PERCENT => __('Phần trăm (%)'),
            self::FIXED => __('Số tiền cố định'),
            self::FREE_SHIPPING => __('Miễn phí vận chuyển'),
        };
    }
}
