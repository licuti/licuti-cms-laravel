<?php

namespace App\Exceptions\Cart;

class EmptyCartException extends CartException
{
    public function __construct(string $message = 'Giỏ hàng đang trống.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
