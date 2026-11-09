<?php

namespace App\DTOs\Pricing;

use Carbon\Carbon;

/**
 * Một suất Flash Sale đang chạy, áp cho đúng (product, variant) của giỏ hàng.
 */
class FlashSaleOffer
{
    public function __construct(
        public readonly int $flashSaleProductId,
        public readonly int $flashSaleId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly float $flashPrice,
        public readonly int $remainingQuantity,
        public readonly ?int $limitPerUser,
        public readonly ?string $campaignName,
        public readonly ?Carbon $endsAt
    ) {}

    public function hasStock(): bool
    {
        return $this->remainingQuantity > 0;
    }

    public function isUnlimited(): bool
    {
        return $this->remainingQuantity === PHP_INT_MAX;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'flash_sale_product_id' => $this->flashSaleProductId,
            'flash_sale_id'         => $this->flashSaleId,
            'flash_price'           => $this->flashPrice,
            'remaining_quantity'    => $this->isUnlimited() ? null : $this->remainingQuantity,
            'limit_per_user'        => $this->limitPerUser,
            'campaign_name'         => $this->campaignName,
            'ends_at'               => $this->endsAt?->toIso8601String(),
        ];
    }
}
