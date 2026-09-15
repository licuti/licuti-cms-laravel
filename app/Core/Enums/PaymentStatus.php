<?php

namespace App\Core\Enums;

enum PaymentStatus: string
{
    case PENDING    = 'pending';
    case PAID       = 'paid';
    case FAILED     = 'failed';
    case REFUNDED   = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING  => 'Chờ thanh toán',
            self::PAID     => 'Đã thanh toán',
            self::FAILED   => 'Thanh toán thất bại',
            self::REFUNDED => 'Đã hoàn tiền',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING  => 'yellow',
            self::PAID     => 'green',
            self::FAILED   => 'red',
            self::REFUNDED => 'orange',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
