<?php

namespace App\Actions\Cart;

use App\Contracts\Pricing\PriceResolverInterface;
use App\DTOs\Cart\AddCartItemDTO;
use App\Exceptions\Cart\InvalidQuantityException;
use App\Exceptions\Cart\ProductUnavailableException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductVariantRepositoryInterface;
use App\Services\Shared\Cart\CartItemGuard;
use Illuminate\Support\Str;

class AddItemToCartAction
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductVariantRepositoryInterface $variantRepository,
        private readonly CartItemRepositoryInterface $cartItemRepository,
        private readonly CartItemGuard $guard,
        private readonly PriceResolverInterface $priceResolver
    ) {}

    public function execute(Cart $cart, AddCartItemDTO $dto): Cart
    {
        /** @var Product|null $product */
        $product = $this->productRepository->findByUuidOrNull($dto->productUuid);
        if (!$product) {
            throw new ProductUnavailableException(__('Sản phẩm không tồn tại.'));
        }

        /** @var ProductVariant|null $variant */
        $variant = null;
        if (!empty($dto->variantUuid)) {
            $variant = $this->variantRepository->findByUuidOrNull($dto->variantUuid);
            if (!$variant) {
                throw new ProductUnavailableException(__('Phân loại sản phẩm không tồn tại.'));
            }
        }

        // 1. Kiểm tra tính khả dụng
        $this->guard->assertPurchasable($product, $variant);

        $lineKey = CartItem::makeLineKey($product->id, $variant?->id);
        $existingItem = $this->cartItemRepository->findByLineKey($cart->id, $lineKey);

        $targetQuantity = $dto->quantity;
        if ($existingItem) {
            $targetQuantity += $existingItem->quantity;
        } else {
            $currentLineCount = $this->cartItemRepository->countByCart($cart->id);
            $maxLines = (int) config('cart.max_line_items', 50);
            if ($currentLineCount >= $maxLines) {
                throw new InvalidQuantityException(__('Giỏ hàng đã đạt giới hạn tối đa :max loại sản phẩm.', [
                    'max' => $maxLines,
                ]));
            }
        }

        // 2. Kiểm tra số lượng và tồn kho
        $this->guard->assertQuantityAllowed($product, $targetQuantity);
        $this->guard->assertInStock($product, $variant, $targetQuantity);

        $priceInfo = $this->priceResolver->resolve($product, $variant);

        // 3. Cập nhật hoặc tạo mới (chống race collision)
        if ($existingItem) {
            $existingItem->update([
                'quantity'            => $targetQuantity,
                'unit_price_snapshot' => $priceInfo->unitPrice,
                'meta'                => $dto->meta ?: $existingItem->meta,
            ]);
        } else {
            try {
                $this->cartItemRepository->create([
                    'uuid'                => (string) Str::uuid(),
                    'cart_id'             => $cart->id,
                    'product_id'          => $product->id,
                    'product_variant_id'  => $variant?->id,
                    'line_key'            => $lineKey,
                    'quantity'            => $targetQuantity,
                    'unit_price_snapshot' => $priceInfo->unitPrice,
                    'meta'                => $dto->meta,
                ]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $existingItem = $this->cartItemRepository->findByLineKey($cart->id, $lineKey);
                if ($existingItem) {
                    $existingItem->increment('quantity', $dto->quantity);
                    $existingItem->update(['unit_price_snapshot' => $priceInfo->unitPrice]);
                } else {
                    throw $e;
                }
            }
        }

        $cart->touchActivity();

        return $cart->fresh(['items.product.translations', 'items.variant.attributeValues', 'coupon']);
    }
}
