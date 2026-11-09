<?php

namespace App\DTOs\Pricing;

class PriceInfo
{
    public function __construct(
        public readonly float $unitPrice,
        public readonly ?float $comparePrice,
        public readonly bool $isOnSale,
        public readonly string $source = 'base',
        /** ID dòng flash_sale_products khi giá đến từ chiến dịch Flash Sale (source = flash_sale). */
        public readonly ?int $flashSaleProductId = null,
        /** Số suất flash còn lại của dòng chiến dịch đó (null = không lấy từ flash). */
        public readonly ?int $flashRemainingQuantity = null,
        /** Giá thường (base/variant) đã bị giá flash thay thế, dùng để đối chiếu và trừ coupon. */
        public readonly ?float $regularPrice = null
    ) {}

    public function isFlashSale(): bool
    {
        return $this->source === 'flash_sale' && $this->flashSaleProductId !== null;
    }
}
