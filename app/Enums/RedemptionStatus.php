<?php

namespace App\Enums;

enum RedemptionStatus: string
{
    case RESERVED = 'reserved';
    case RELEASED = 'released';

    public function label(): string
    {
        return match ($this) {
            self::RESERVED => __('Đã giữ chỗ / Đã mua'),
            self::RELEASED => __('Đã hoàn trả kho'),
        };
    }
}
