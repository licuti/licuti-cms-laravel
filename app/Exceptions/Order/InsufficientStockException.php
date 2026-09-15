<?php

namespace App\Exceptions\Order;

use App\Exceptions\BusinessException;

class InsufficientStockException extends BusinessException
{
    public function __construct(string $productName = '', int $requested = 0, int $available = 0)
    {
        if ($productName && $requested && $available !== null) {
            $message = "Sản phẩm \"{$productName}\" không đủ số lượng. Yêu cầu: {$requested}, Còn lại: {$available}";
        } else {
            $message = 'Sản phẩm không đủ số lượng trong kho';
        }

        parent::__construct($message, 422);
    }
}
