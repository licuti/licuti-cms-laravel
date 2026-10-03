<?php

namespace App\Services\Shared\Product;

use App\Core\Base\BaseService;
use App\Events\Product\LowStockThresholdReached;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductVariantRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Atomic stock operations — groundwork cho Cart/Order/Inventory (Tầng 5–6).
 *
 * Đặt ở `Services/Shared/` vì sẽ dùng bởi cả Admin + storefront + Cart/Order.
 *
 * Thiết kế:
 *  - Dùng `lockForUpdate()` + `decrement()` — atomic, chống race 2 đơn hàng
 *    cùng trừ. KHÔNG read-then-write qua accessor.
 *  - Trả `bool` không throw — caller (Order service) tự quyết định rollback.
 *  - Chính sách tồn kho (track_inventory / allow_backorder /
 *    low_stock_threshold) nằm ở Product; variant chỉ giữ stock_quantity.
 */
class StockService extends BaseService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductVariantRepositoryInterface $variantRepository
    ) {}

    /**
     * Trừ tồn kho atomic, chống oversell.
     *
     * @return bool true nếu thành công (hoặc product không theo dõi tồn kho),
     *              false nếu không đủ hàng và không cho phép backorder.
     */
    public function decrementStock(int $productId, ?int $variantId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $variantId, $quantity) {
            $product = $this->productRepository->lockForUpdateFind($productId);

            if (! $product) {
                return false;
            }

            // Product không theo dõi tồn kho → cho qua.
            if (! $product->track_inventory) {
                return true;
            }

            $stockHolder = $variantId
                ? $this->variantRepository->lockForUpdateFind($variantId)
                : $product;

            if (! $stockHolder) {
                return false;
            }

            // Không đủ hàng và không cho phép backorder → từ chối.
            if ($stockHolder->stock_quantity < $quantity && ! $product->allow_backorder) {
                return false;
            }

            $stockHolder->decrement('stock_quantity', $quantity);

            $this->dispatchLowStockEventIfThresholdReached($product, $stockHolder);

            return true;
        });
    }

    /**
     * Cộng lại tồn kho (hủy đơn / trả hàng).
     */
    public function incrementStock(int $productId, ?int $variantId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $variantId, $quantity) {
            $product = $this->productRepository->lockForUpdateFind($productId);

            if (! $product) {
                return false;
            }

            $stockHolder = $variantId
                ? $this->variantRepository->lockForUpdateFind($variantId)
                : $product;

            if (! $stockHolder) {
                return false;
            }

            $stockHolder->increment('stock_quantity', $quantity);

            return true;
        });
    }

    /**
     * Tồn kho hiện tại có đang ở/below ngưỡng cảnh báo không.
     */
    public function isLowStock(int $productId, ?int $variantId = null): bool
    {
        $product = $this->productRepository->find($productId);

        if (! $product || ! $product->track_inventory || $product->low_stock_threshold === null) {
            return false;
        }

        $stock = $variantId
            ? (int) ($this->variantRepository->find($variantId)?->stock_quantity ?? 0)
            : (int) $product->stock_quantity;

        return $stock <= $product->low_stock_threshold;
    }

    private function dispatchLowStockEventIfThresholdReached(Product $product, Product|ProductVariant $stockHolder): void
    {
        if ($product->low_stock_threshold === null) {
            return;
        }

        if ((int) $stockHolder->stock_quantity > $product->low_stock_threshold) {
            return;
        }

        LowStockThresholdReached::dispatch(
            $product,
            $stockHolder instanceof ProductVariant ? $stockHolder : null,
            (int) $stockHolder->stock_quantity
        );
    }
}
