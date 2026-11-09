<?php

namespace App\Services\Shared\Pricing;

use App\Contracts\Pricing\PriceResolverInterface;
use App\DTOs\Pricing\PriceInfo;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * PriceResolver trang bị Flash Sale: nếu sản phẩm đang nằm trong chiến dịch chạy thật
 * (is_active + starts_at <= now <= ends_at) và còn suất, giá flash thay thế giá thường.
 *
 * Quy tắc đã chốt với chủ dự án 2026-11-03:
 * - Giá được TÍNH LẠI tại thời điểm tính giá giỏ/checkout (không snapshot lúc thêm vào giỏ).
 * - Hết suất flash (sold_quantity >= quantity_limit) -> tự rơi về giá thường.
 * - Chiến dịch chưa bắt đầu / đã kết thúc / tắt -> giá thường.
 */
class FlashSalePriceResolver implements PriceResolverInterface
{
    public const SOURCE = 'flash_sale';

    public function __construct(
        private readonly PriceResolverInterface $baseResolver,
        private readonly FlashSalePricingContext $context
    ) {}

    public function resolve(Product $product, ?ProductVariant $variant = null): PriceInfo
    {
        $base = $this->baseResolver->resolve($product, $variant);

        $offer = $this->context->offerFor($product, $variant);

        if (!$offer || !$offer->hasStock()) {
            return $base;
        }

        // Giá flash chỉ hợp lệ khi thực sự rẻ hơn giá hiện hành (chặn sai sót cấu hình giá).
        if ($offer->flashPrice <= 0 || $offer->flashPrice >= $base->unitPrice) {
            return $base;
        }

        return new PriceInfo(
            unitPrice: $offer->flashPrice,
            comparePrice: $base->comparePrice ?? $base->unitPrice,
            isOnSale: true,
            source: self::SOURCE,
            flashSaleProductId: $offer->flashSaleProductId,
            flashRemainingQuantity: $offer->remainingQuantity,
            regularPrice: $base->unitPrice
        );
    }
}
