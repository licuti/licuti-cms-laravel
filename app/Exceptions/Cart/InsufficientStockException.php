<?php

namespace App\Exceptions\Cart;

class InsufficientStockException extends CartException
{
    public function __construct(string $message = 'Số lượng trong kho không đủ đáp ứng.', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
