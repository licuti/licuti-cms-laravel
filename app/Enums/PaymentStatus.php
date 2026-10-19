<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case SUCCESS    = 'success';
    case FAILED     = 'failed';
    case REFUNDED   = 'refunded';
    case CANCELLED  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING    => __('Chờ thanh toán'),
            self::PROCESSING => __('Đang xử lý'),
            self::SUCCESS    => __('Thành công'),
            self::FAILED     => __('Thất bại'),
            self::REFUNDED   => __('Đã hoàn tiền'),
            self::CANCELLED  => __('Đã hủy'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING    => 'badge bg-warning text-dark',
            self::PROCESSING => 'badge bg-info text-dark',
            self::SUCCESS    => 'badge bg-success',
            self::FAILED     => 'badge bg-danger',
            self::REFUNDED   => 'badge bg-secondary',
            self::CANCELLED  => 'badge bg-dark',
        };
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::SUCCESS, self::REFUNDED, self::CANCELLED, self::FAILED]);
    }
}
