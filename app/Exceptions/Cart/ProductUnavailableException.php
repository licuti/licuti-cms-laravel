<?php

namespace App\Exceptions\Cart;

class ProductUnavailableException extends CartException
{
    public function __construct(string $message = 'Sản phẩm không khả dụng hoặc đã dừng kinh doanh.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
