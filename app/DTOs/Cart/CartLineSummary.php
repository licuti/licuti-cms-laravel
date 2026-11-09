<?php

namespace App\DTOs\Cart;

use App\Models\Product;
use App\Models\ProductVariant;

class CartLineSummary
{
    /**
     * @param array<int, string> $issues
     */
    public function __construct(
        public readonly string $itemUuid,
        public readonly string $productUuid,
        public readonly ?string $variantUuid,
        public readonly string $name,
        public readonly ?string $variantName,
        public readonly ?string $sku,
        public readonly ?string $imageUrl,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly ?float $comparePrice,
        public readonly float $snapshotPrice,
        public readonly float $lineTotal,
        public readonly bool $priceChanged,
        public readonly bool $isOnSale,
        public readonly int $maxQuantity,
        public readonly array $issues = [],
        public readonly ?Product $product = null,
        public readonly ?ProductVariant $variant = null,
        /** Dòng này đang được bán theo giá Flash Sale. */
        public readonly bool $isFlashSale = false,
        /** ID flash_sale_products để reserve/release suất flash khi đặt hàng. */
        public readonly ?int $flashSaleProductId = null,
        public readonly ?int $flashRemainingQuantity = null,
        /** Giá thường trước khi áp giá flash (null nếu không phải flash). */
        public readonly ?float $regularPrice = null,
        public readonly ?string $flashSaleName = null,
        public readonly ?string $flashSaleEndsAt = null
    ) {}

    /**
     * Số tiền của dòng này KHÔNG được tính vào cơ sở tính mã giảm giá
     * (quy tắc đã chốt: giá flash không cộng dồn coupon).
     */
    public function isCouponIneligible(): bool
    {
        return $this->isFlashSale;
    }
}
