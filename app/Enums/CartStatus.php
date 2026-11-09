<?php

namespace App\Enums;

enum CartStatus: string
{
    case ACTIVE    = 'active';
    case ABANDONED = 'abandoned';
    case CONVERTED = 'converted';
    case MERGED    = 'merged';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE    => __('Đang hoạt động'),
            self::ABANDONED => __('Bỏ quên'),
            self::CONVERTED => __('Đã đặt hàng'),
            self::MERGED    => __('Đã gộp vào tài khoản'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE    => 'badge bg-success',
            self::ABANDONED => 'badge bg-warning text-dark',
            self::CONVERTED => 'badge bg-primary',
            self::MERGED    => 'badge bg-secondary',
        };
    }
}
