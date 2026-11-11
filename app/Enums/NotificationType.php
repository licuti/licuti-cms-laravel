<?php

namespace App\Enums;

enum NotificationType: string
{
    case ORDER_PLACED = 'order.placed';
    case PAYMENT_SUCCEEDED = 'payment.succeeded';
    case ORDER_CANCELLED = 'order.cancelled';
    case REVIEW_APPROVED = 'review.approved';
    case STOCK_LOW = 'stock.low';

    public function permission(): ?string
    {
        return match ($this) {
            self::ORDER_PLACED, self::ORDER_CANCELLED => 'orders.view',
            self::PAYMENT_SUCCEEDED => 'payments.view',
            self::STOCK_LOW => 'products.view',
            self::REVIEW_APPROVED => null,
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::ORDER_PLACED, self::ORDER_CANCELLED => 'orders',
            self::PAYMENT_SUCCEEDED => 'payments',
            self::STOCK_LOW => 'products',
            self::REVIEW_APPROVED => 'reviews',
        };
    }

    public static function groupLabel(string $group): string
    {
        return match ($group) {
            'orders' => __('Đơn hàng'),
            'payments' => __('Thanh toán'),
            'products' => __('Sản phẩm'),
            'reviews' => __('Đánh giá'),
            default => $group,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ORDER_PLACED => __('Đơn hàng mới'),
            self::PAYMENT_SUCCEEDED => __('Thanh toán thành công'),
            self::ORDER_CANCELLED => __('Đơn hàng đã hủy'),
            self::REVIEW_APPROVED => __('Đánh giá được duyệt'),
            self::STOCK_LOW => __('Tồn kho chạm ngưỡng'),
        };
    }
}
