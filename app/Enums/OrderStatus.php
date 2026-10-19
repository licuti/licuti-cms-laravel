<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING    = 'pending';
    case CONFIRMED  = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPING   = 'shipping';
    case DELIVERED  = 'delivered';
    case COMPLETED  = 'completed';
    case CANCELLED  = 'cancelled';
    case REFUNDED   = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING    => __('Chờ xử lý'),
            self::CONFIRMED  => __('Đã xác nhận'),
            self::PROCESSING => __('Đang đóng gói'),
            self::SHIPPING   => __('Đang giao hàng'),
            self::DELIVERED  => __('Đã giao hàng'),
            self::COMPLETED  => __('Hoàn thành'),
            self::CANCELLED  => __('Đã hủy'),
            self::REFUNDED   => __('Đã hoàn tiền'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING    => 'badge bg-warning text-dark',
            self::CONFIRMED  => 'badge bg-info text-dark',
            self::PROCESSING => 'badge bg-primary',
            self::SHIPPING   => 'badge bg-indigo text-white',
            self::DELIVERED  => 'badge bg-teal text-white',
            self::COMPLETED  => 'badge bg-success',
            self::CANCELLED  => 'badge bg-danger',
            self::REFUNDED   => 'badge bg-secondary',
        };
    }

    /**
     * Kiểm tra trạng thái có thể chuyển đổi hợp lệ (State Machine).
     */
    public function canTransitionTo(self $newStatus): bool
    {
        if ($this === $newStatus) {
            return true;
        }

        return match ($this) {
            self::PENDING => in_array($newStatus, [
                self::CONFIRMED,
                self::PROCESSING,
                self::CANCELLED,
            ]),
            self::CONFIRMED => in_array($newStatus, [
                self::PROCESSING,
                self::SHIPPING,
                self::CANCELLED,
            ]),
            self::PROCESSING => in_array($newStatus, [
                self::SHIPPING,
                self::CANCELLED,
            ]),
            self::SHIPPING => in_array($newStatus, [
                self::DELIVERED,
                self::COMPLETED,
                self::CANCELLED,
            ]),
            self::DELIVERED => in_array($newStatus, [
                self::COMPLETED,
                self::REFUNDED,
            ]),
            self::COMPLETED => in_array($newStatus, [
                self::REFUNDED,
            ]),
            self::CANCELLED, self::REFUNDED => false,
        };
    }
}
