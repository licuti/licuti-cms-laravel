<?php

namespace App\Services\Shared\Pricing;

use App\DTOs\Pricing\FlashSaleOffer;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

/**
 * Kho tra cứu giá Flash Sale trong phạm vi một request.
 *
 * Muc tiêu (theo docs/modules/flash-sale.md mục 5 — Phase 2): lookup O(1) khi tính giá giỏ hàng,
 * KHÔNG gây N+1 khi giỏ có nhiều sản phẩm.
 *
 * Chiến lược:
 * - CartPricingService gọi loadFor() MỘT lần với toàn bộ product/variant id của giỏ → 1 query.
 * - offerFor() nhận ra cặp chưa nạp thì tự nạp bổ sung (đường checkout trực tiếp ít dòng).
 * - Kết quả được memoize trong request này; giá flash ĐỌC TRỰC TIẾP từ DB (không cache cross-request)
 *   vì sold_quantity thay đổi từng phút khi có đơn — cache sẽ gây bán vượt quota.
 */
class FlashSalePricingContext
{
    /** @var array<string, FlashSaleOffer|null> */
    private array $offers = [];

    /** @var array<int, true> product id đã nạp batch */
    private array $loadedProducts = [];

    public function reset(): void
    {
        $this->offers = [];
        $this->loadedProducts = [];
    }

    /**
     * Nạp một lượt cho toàn bộ sản phẩm/biến thể của giỏ hàng.
     *
     * @param array<int, int> $productIds
     * @param array<int, int> $variantIds
     */
    public function loadFor(array $productIds, array $variantIds = []): void
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));

        if (empty($productIds)) {
            return;
        }

        $rows = $this->queryOffers($productIds, array_values(array_unique(array_filter(array_map('intval', $variantIds)))));

        foreach ($rows as $row) {
            $offer = $this->toOffer($row);

            $keys = ["p:{$row->product_id}"];
            if (!empty($row->product_variant_id)) {
                array_unshift($keys, "v:{$row->product_variant_id}");
            }

            foreach ($keys as $key) {
                // Giữ ưu tiên bản ghi đầu tiên: query đã sắp xếp theo chiến dịch kết thúc sớm nhất.
                if (!array_key_exists($key, $this->offers)) {
                    $this->offers[$key] = $offer;
                }
            }
        }

        foreach ($productIds as $id) {
            $this->loadedProducts[$id] = true;
        }
    }

    public function offerFor(Product $product, ?ProductVariant $variant = null): ?FlashSaleOffer
    {
        if ($variant) {
            $variantKey = "v:{$variant->id}";
            if (array_key_exists($variantKey, $this->offers)) {
                return $this->offers[$variantKey];
            }
        }

        $productKey = "p:{$product->id}";
        if (array_key_exists($productKey, $this->offers)) {
            return $this->offers[$productKey];
        }

        if (!isset($this->loadedProducts[$product->id])) {
            $this->loadFor([$product->id], $variant ? [$variant->id] : []);

            return $this->offerFor($product, $variant);
        }

        return null;
    }

    /**
     * @param array<int, int> $productIds
     * @param array<int, int> $variantIds
     */
    private function queryOffers(array $productIds, array $variantIds): \Illuminate\Support\Collection
    {
        $now = Carbon::now();

        return FlashSaleProduct::query()
            ->from('flash_sale_products')
            ->join('flash_sales', 'flash_sales.id', '=', 'flash_sale_products.flash_sale_id')
            ->where('flash_sales.is_active', true)
            ->whereNull('flash_sales.deleted_at')
            ->where('flash_sales.starts_at', '<=', $now)
            ->where('flash_sales.ends_at', '>=', $now)
            ->whereIn('flash_sale_products.product_id', $productIds)
            ->when(!empty($variantIds), function ($q) use ($variantIds) {
                $q->where(function (Builder $w) use ($variantIds) {
                    $w->whereIn('flash_sale_products.product_variant_id', $variantIds)
                        ->orWhereNull('flash_sale_products.product_variant_id');
                });
            })
            ->orderBy('flash_sales.ends_at')
            ->orderBy('flash_sale_products.id')
            ->get([
                'flash_sale_products.*',
                'flash_sales.starts_at as campaign_starts_at',
                'flash_sales.ends_at as campaign_ends_at',
            ]);
    }

    private function toOffer(object $row): FlashSaleOffer
    {
        // quantity_limit NOT NULL trong migration; giữ nhánh null cho an toàn về sau.
        $limit = $row->quantity_limit !== null ? (int) $row->quantity_limit : null;
        $sold  = (int) $row->sold_quantity;

        // limit_per_user là unsignedSmallInteger DEFAULT 0 => 0 nghĩa là KHÔNG giới hạn (không phải "0 suất").
        $perUser = $row->limit_per_user !== null ? (int) $row->limit_per_user : 0;

        return new FlashSaleOffer(
            flashSaleProductId: (int) $row->id,
            flashSaleId: (int) $row->flash_sale_id,
            productId: (int) $row->product_id,
            variantId: $row->product_variant_id ? (int) $row->product_variant_id : null,
            flashPrice: (float) $row->flash_price,
            remainingQuantity: $limit === null ? PHP_INT_MAX : max(0, $limit - $sold),
            limitPerUser: $perUser > 0 ? $perUser : null,
            campaignName: null,
            endsAt: $row->campaign_ends_at ? Carbon::parse($row->campaign_ends_at) : null
        );
    }
}
