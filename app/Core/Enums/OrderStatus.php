<?php

namespace App\Core\Enums;

enum OrderStatus: string
{
    case PENDING    = 'pending';
    case CONFIRMED  = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED    = 'shipped';
    case DELIVERED  = 'delivered';
    case COMPLETED  = 'completed';
    case CANCELLED  = 'cancelled';
    case REFUNDED   = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING    => 'Chờ xác nhận',
            self::CONFIRMED  => 'Đã xác nhận',
            self::PROCESSING => 'Đang xử lý',
            self::SHIPPED    => 'Đang giao hàng',
            self::DELIVERED  => 'Đã giao hàng',
            self::COMPLETED  => 'Hoàn thành',
            self::CANCELLED  => 'Đã hủy',
            self::REFUNDED   => 'Đã hoàn tiền',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING    => 'yellow',
            self::CONFIRMED  => 'blue',
            self::PROCESSING => 'indigo',
            self::SHIPPED    => 'purple',
            self::DELIVERED  => 'teal',
            self::COMPLETED  => 'green',
            self::CANCELLED  => 'red',
            self::REFUNDED   => 'orange',
        };
    }

    /**
     * Các trạng thái cho phép hủy đơn hàng
     */
    public function isCancellable(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }

    /**
     * Các trạng thái đơn hàng đang hoạt động (chưa kết thúc)
     */
    public function isActive(): bool
    {
        return !in_array($this, [self::COMPLETED, self::CANCELLED, self::REFUNDED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
