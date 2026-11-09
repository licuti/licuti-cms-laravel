<?php

namespace App\Services\Shared\Cart;

use App\Exceptions\Cart\InsufficientStockException;
use App\Exceptions\Cart\InvalidQuantityException;
use App\Exceptions\Cart\ProductUnavailableException;
use App\Models\Product;
use App\Models\ProductVariant;

class CartItemGuard
{
    /**
     * Kiểm tra sản phẩm và biến thể có đang khả dụng để mua hay không.
     *
     * @throws ProductUnavailableException
     */
    public function assertPurchasable(Product $product, ?ProductVariant $variant = null): void
    {
        if (!$product->is_active) {
            throw new ProductUnavailableException(__('Sản phẩm đã bị vô hiệu hóa hoặc ngừng bán.'));
        }

        if ($product->status && $product->status !== 'published') {
            throw new ProductUnavailableException(__('Sản phẩm chưa được công khai.'));
        }

        // Nếu sản phẩm có biến thể, bắt buộc phải chọn biến thể hợp lệ
        $hasVariants = $product->variants()->exists();
        if ($hasVariants) {
            if (!$variant) {
                throw new ProductUnavailableException(__('Vui lòng chọn phân loại/biến thể của sản phẩm.'));
            }

            if ((int) $variant->product_id !== (int) $product->id) {
                throw new ProductUnavailableException(__('Biến thể không thuộc về sản phẩm này.'));
            }

            if (!$variant->is_active) {
                throw new ProductUnavailableException(__('Biến thể sản phẩm này hiện không khả dụng.'));
            }
        }
    }

    /**
     * Kiểm tra số lượng có nằm trong giới hạn cho phép của sản phẩm hay không.
     *
     * @throws InvalidQuantityException
     */
    public function assertQuantityAllowed(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidQuantityException(__('Số lượng sản phẩm phải lớn hơn 0.'));
        }

        $maxCap = (int) config('cart.max_quantity_per_line', 999);
        if ($quantity > $maxCap) {
            throw new InvalidQuantityException(__('Số lượng vượt quá giới hạn tối đa cho phép (:max).', ['max' => $maxCap]));
        }

        if ($product->sold_individually && $quantity > 1) {
            throw new InvalidQuantityException(__('Sản phẩm này chỉ được phép mua tối đa 1 sản phẩm cho mỗi đơn hàng.'));
        }

        if ($product->min_order_quantity && $quantity < $product->min_order_quantity) {
            throw new InvalidQuantityException(__('Số lượng tối thiểu phải là :min.', [
                'min' => $product->min_order_quantity,
            ]));
        }

        if ($product->max_order_quantity && $quantity > $product->max_order_quantity) {
            throw new InvalidQuantityException(__('Số lượng vượt quá mức tối đa :max cho phép.', [
                'max' => $product->max_order_quantity,
            ]));
        }
    }

    /**
     * Kiểm tra tồn kho có đủ đáp ứng số lượng yêu cầu hay không.
     *
     * @throws InsufficientStockException
     */
    public function assertInStock(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if (!$product->track_inventory || $product->allow_backorder) {
            return;
        }

        $stockHolder = $variant ?: $product;
        $stock = (int) $stockHolder->stock_quantity;

        if ($stock < $quantity) {
            throw new InsufficientStockException(__('Số lượng trong kho không đủ đáp ứng (chỉ còn :stock sản phẩm).', [
                'stock' => max(0, $stock),
            ]));
        }
    }
}
