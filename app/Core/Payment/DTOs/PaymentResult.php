<?php

namespace App\Core\Payment\DTOs;

class PaymentResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED  = 'failed';

    public function __construct(
        public readonly string $status,
        public readonly ?string $transactionId = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $message = null,
        public readonly array $rawData = []
    ) {}

    public static function success(
        string $transactionId,
        ?string $redirectUrl = null,
        ?string $message = 'Thanh toán thành công.',
        array $rawData = []
    ): self {
        return new self(
            status: self::STATUS_SUCCESS,
            transactionId: $transactionId,
            redirectUrl: $redirectUrl,
            message: $message,
            rawData: $rawData
        );
    }

    public static function pending(
        string $transactionId,
        ?string $redirectUrl = null,
        ?string $message = 'Giao dịch đang chờ thanh toán.',
        array $rawData = []
    ): self {
        return new self(
            status: self::STATUS_PENDING,
            transactionId: $transactionId,
            redirectUrl: $redirectUrl,
            message: $message,
            rawData: $rawData
        );
    }

    public static function failed(
        string $message = 'Thanh toán thất bại.',
        array $rawData = []
    ): self {
        return new self(
            status: self::STATUS_FAILED,
            transactionId: null,
            redirectUrl: null,
            message: $message,
            rawData: $rawData
        );
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccess() || $this->isPending();
    }

    public function isRedirect(): bool
    {
        return !empty($this->redirectUrl);
    }
}
