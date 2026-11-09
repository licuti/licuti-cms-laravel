<?php

namespace App\Exceptions\Cart;

use App\Exceptions\BusinessException;

abstract class CartException extends BusinessException
{
    public function httpStatus(): int
    {
        return $this->getCode() ?: 422;
    }
}
