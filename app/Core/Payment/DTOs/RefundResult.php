<?php

namespace App\Core\Payment\DTOs;

class RefundResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';

    public function __construct(
        public readonly string $status,
        public readonly ?string $refundId = null,
        public readonly ?string $message = null,
        public readonly array $rawData = []
    ) {}

    public static function success(
        string $refundId,
        ?string $message = 'Hoàn tiền thành công.',
        array $rawData = []
    ): self {
        return new self(
            status: self::STATUS_SUCCESS,
            refundId: $refundId,
            message: $message,
            rawData: $rawData
        );
    }

    public static function failed(
        string $message = 'Hoàn tiền thất bại.',
        array $rawData = []
    ): self {
        return new self(
            status: self::STATUS_FAILED,
            refundId: null,
            message: $message,
            rawData: $rawData
        );
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
