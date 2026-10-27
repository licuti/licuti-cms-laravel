<?php

namespace App\Exceptions\Cart;

class CartItemNotFoundException extends CartException
{
    public function __construct(string $message = 'Sản phẩm không tồn tại trong giỏ hàng.', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
