<?php

namespace App\Core\Enums;

enum PaymentMethod: string
{
    case COD    = 'cod';
    case VNPAY  = 'vnpay';
    case MOMO   = 'momo';
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';

    public function label(): string
    {
        return match($this) {
            self::COD    => 'Thanh toán khi nhận hàng (COD)',
            self::VNPAY  => 'VNPay',
            self::MOMO   => 'Ví MoMo',
            self::STRIPE => 'Stripe',
            self::PAYPAL => 'PayPal',
        };
    }

    public function isOnline(): bool
    {
        return $this !== self::COD;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
