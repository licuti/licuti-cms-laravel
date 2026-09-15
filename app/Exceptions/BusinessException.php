<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception gốc cho tất cả lỗi nghiệp vụ trong dự án.
 * Mọi Custom Exception liên quan đến business logic đều phải extends class này.
 */
class BusinessException extends Exception
{
    public function __construct(
        string $message = 'Đã có lỗi nghiệp vụ xảy ra',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
