<?php

namespace App\Exceptions\Coupon;

use App\Exceptions\BusinessException;

class CouponException extends BusinessException
{
    public function __construct(string $message = 'Mã giảm giá không hợp lệ.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
