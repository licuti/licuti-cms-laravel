<?php

namespace App\Exceptions\Cart;

class InvalidQuantityException extends CartException
{
    public function __construct(string $message = 'Số lượng sản phẩm không hợp lệ.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
