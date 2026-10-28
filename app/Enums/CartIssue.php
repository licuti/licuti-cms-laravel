<?php

namespace App\Enums;

enum CartIssue: string
{
    case UNAVAILABLE = 'unavailable';
    case OUT_OF_STOCK = 'out_of_stock';
    case INSUFFICIENT_STOCK = 'insufficient_stock';
    case BELOW_MIN = 'below_min';
    case ABOVE_MAX = 'above_max';
    case SOLD_INDIVIDUALLY = 'sold_individually';
    case PRICE_CHANGED = 'price_changed';

    /**
     * Xác định xem issue này có chặn checkout hay chỉ mang tính cảnh báo thông tin.
     */
    public function isBlocking(): bool
    {
        return $this !== self::PRICE_CHANGED;
    }
}
