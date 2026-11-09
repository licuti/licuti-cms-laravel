<?php

namespace App\Exceptions\Cart;

use App\Exceptions\BusinessException;

class PriceChangedException extends BusinessException
{
    public function __construct(
        string $message = 'Giá sản phẩm hoặc tổng tiền đơn hàng đã thay đổi. Vui lòng xác nhận lại đơn hàng.',
        private readonly ?array $errors = null,
        int $code = 409,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
