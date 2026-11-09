<?php

namespace App\Exceptions\FlashSale;

use App\Exceptions\BusinessException;

/**
 * Không còn đủ suất Flash Sale (hoặc vi phạm giới hạn mỗi khách) tại thời điểm đặt hàng.
 */
class FlashSaleQuotaExceededException extends BusinessException
{
    public function __construct(
        string $message = 'Suất Flash Sale không còn đủ cho số lượng bạn chọn.',
        int $code = 409
    ) {
        parent::__construct($message, $code);
    }
}
