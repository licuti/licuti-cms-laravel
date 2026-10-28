<?php

namespace App\Services\Shared\Cart;

use App\Contracts\Pricing\PriceResolverInterface;
use App\Contracts\Shipping\ShippingFeeCalculatorInterface;
use App\DTOs\Cart\AppliedCoupon;
use App\DTOs\Cart\CartLineSummary;
use App\DTOs\Cart\CartSummary;
use App\DTOs\Coupon\CouponSubject;
use App\Enums\CartIssue;
use App\Exceptions\Coupon\CouponException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductVariantRepositoryInterface;
use App\Services\Admin\Coupon\CouponValidationService;
use Illuminate\Support\Collection;

class CartPricingService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductVariantRepositoryInterface $variantRepository,
        private readonly PriceResolverInterface $priceResolver,
        private readonly ShippingFeeCalculatorInterface $shippingCalculator,
        private readonly CouponValidationService $couponValidationService
    ) {}

    /**
     * Tóm tắt và tính toán toàn bộ giá trị của Giỏ hàng từ Database thực tế.
     */
    public function summarize(Cart $cart, ?CouponSubject $subject = null): CartSummary
    {
        $items = $cart->relationLoaded('items') ? $cart->items : $cart->items()->get();

        if ($items->isEmpty()) {
            return new CartSummary(
                lines: collect(),
                itemCount: 0,
                subtotal: 0.0,
                discountAmount: 0.0,
                shippingFee: 0.0,
                grandTotal: 0.0,
                coupon: null,
                hasIssues: false,
                hasSaleItems: false
            );
        }

        $lines = $this->buildLines($items);
        $hasIssues = $lines->contains(fn (CartLineSummary $line) => !empty($line->issues));
        $hasBlockingIssues = $lines->contains(function (CartLineSummary $line) {
            foreach ($line->issues as $issue) {
                $enum = CartIssue::tryFrom((string) $issue);
                if ($enum === null || $enum->isBlocking()) {
                    return true;
                }
            }
            return false;
        });
        $hasSaleItems = $lines->contains(fn (CartLineSummary $line) => $line->isOnSale);

        $subtotal = (float) $lines->sum('lineTotal');
        $itemCount = (int) $lines->sum('quantity');

        // Áp dụng Coupon nếu có
        [$appliedCoupon, $couponWarning] = $this->resolveCoupon($cart, $subtotal, $subject, $hasSaleItems);

        $discountAmount = $appliedCoupon ? $appliedCoupon->discountAmount : 0.0;
        $isFreeShipping = $appliedCoupon ? $appliedCoupon->freeShipping : false;

        $shippingFee = $this->shippingCalculator->calculate($lines, $isFreeShipping);
        $grandTotal = max(0.0, round($subtotal - $discountAmount + $shippingFee, 2));

        $aggregatedIssues = [];
        if ($couponWarning) {
            $aggregatedIssues[] = $couponWarning;
        }
        foreach ($lines as $line) {
            foreach ($line->issues as $issue) {
                $humanIssue = match ($issue) {
                    'out_of_stock'       => __('Sản phẩm ":name" đã hết hàng trong kho.', ['name' => $line->name]),
                    'insufficient_stock' => __('Sản phẩm ":name" không đủ tồn kho theo số lượng yêu cầu (tối đa :max).', ['name' => $line->name, 'max' => $line->maxQuantity]),
                    'price_changed'      => __('Giá sản phẩm ":name" đã thay đổi từ :old sang :new.', [
                        'name' => $line->name,
                        'old'  => number_format($line->snapshotPrice, 0, ',', '.') . 'đ',
                        'new'  => number_format($line->unitPrice, 0, ',', '.') . 'đ',
                    ]),
                    'unavailable'        => __('Sản phẩm ":name" hiện không còn khả dụng.', ['name' => $line->name]),
                    default              => (string) $issue,
                };
                $aggregatedIssues[] = $humanIssue;
            }
        }

        return new CartSummary(
            lines: $lines,
            itemCount: $itemCount,
            subtotal: round($subtotal, 2),
            discountAmount: round($discountAmount, 2),
            shippingFee: round($shippingFee, 2),
            grandTotal: $grandTotal,
            coupon: $appliedCoupon,
            hasIssues: $hasIssues || !empty($aggregatedIssues),
            hasBlockingIssues: $hasBlockingIssues,
            hasSaleItems: $hasSaleItems,
            couponWarning: $couponWarning,
            issues: $aggregatedIssues
        );
    }

    /**
     * Nạp dữ liệu sản phẩm, biến thể và tạo danh sách CartLineSummary (2 queries batch).
     *
     * @param Collection<int, CartItem> $items
     * @return Collection<int, CartLineSummary>
     */
    private function buildLines(Collection $items): Collection
    {
        $productIds = $items->pluck('product_id')->unique()->all();
        $variantIds = $items->pluck('product_variant_id')->filter()->unique()->all();

        $products = $this->productRepository->getForCart($productIds)->keyBy('id');
        $variants = !empty($variantIds)
            ? $this->variantRepository->getForCart($variantIds)->keyBy('id')
            : collect();

        return $items->map(function (CartItem $item) use ($products, $variants) {
            /** @var Product|null $product */
            $product = $products->get($item->product_id);
            /** @var ProductVariant|null $variant */
            $variant = $item->product_variant_id ? $variants->get($item->product_variant_id) : null;

            return $this->buildLineSummary($item, $product, $variant);
        });
    }

    private function buildLineSummary(CartItem $item, ?Product $product, ?ProductVariant $variant): CartLineSummary
    {
        if (!$product) {
            return new CartLineSummary(
                itemUuid: $item->uuid,
                productUuid: '',
                variantUuid: null,
                name: __('Sản phẩm không tồn tại'),
                variantName: null,
                sku: null,
                imageUrl: null,
                quantity: $item->quantity,
                unitPrice: (float) $item->unit_price_snapshot,
                comparePrice: null,
                snapshotPrice: (float) $item->unit_price_snapshot,
                lineTotal: (float) $item->unit_price_snapshot * $item->quantity,
                priceChanged: false,
                isOnSale: false,
                maxQuantity: 0,
                issues: ['unavailable'],
                product: null,
                variant: null
            );
        }

        $priceInfo = $this->priceResolver->resolve($product, $variant);
        $issues = $this->resolveLineIssues($item, $product, $variant);
        $maxQuantity = $this->resolveMaxAllowedQuantity($product, $variant);

        $lineTotal = round($priceInfo->unitPrice * $item->quantity, 2);
        $priceChanged = abs((float) $item->unit_price_snapshot - $priceInfo->unitPrice) > 0.001;
        if ($priceChanged) {
            $issues[] = 'price_changed';
        }

        $productName = $product->name ?: "Product #{$product->id}";
        $variantName = $variant?->name;
        $sku = $variant?->sku ?: $product->sku;
        $imageUrl = $variant?->image_url ?: $product->primary_image_url;

        return new CartLineSummary(
            itemUuid: $item->uuid,
            productUuid: $product->uuid,
            variantUuid: $variant?->uuid,
            name: $productName,
            variantName: $variantName,
            sku: $sku,
            imageUrl: $imageUrl,
            quantity: $item->quantity,
            unitPrice: $priceInfo->unitPrice,
            comparePrice: $priceInfo->comparePrice,
            snapshotPrice: (float) $item->unit_price_snapshot,
            lineTotal: $lineTotal,
            priceChanged: $priceChanged,
            isOnSale: $priceInfo->isOnSale,
            maxQuantity: $maxQuantity,
            issues: $issues,
            product: $product,
            variant: $variant
        );
    }

    /**
     * Kiểm tra các vấn đề của dòng sản phẩm (hết hàng, thiếu hàng, dừng bán).
     *
     * @return array<int, string>
     */
    private function resolveLineIssues(CartItem $item, Product $product, ?ProductVariant $variant): array
    {
        $issues = [];

        if (!$product->is_active || ($product->status && $product->status !== 'published')) {
            $issues[] = 'unavailable';
        }

        if ($product->track_inventory && !$product->allow_backorder) {
            $stockHolder = $variant ?: $product;
            $stock = (int) $stockHolder->stock_quantity;

            if ($stock <= 0) {
                $issues[] = 'out_of_stock';
            } elseif ($item->quantity > $stock) {
                $issues[] = 'insufficient_stock';
            }
        }

        if ($product->min_order_quantity && $item->quantity < $product->min_order_quantity) {
            $issues[] = 'below_min';
        }

        if ($product->max_order_quantity && $item->quantity > $product->max_order_quantity) {
            $issues[] = 'above_max';
        }

        if ($product->sold_individually && $item->quantity > 1) {
            $issues[] = 'sold_individually';
        }

        return $issues;
    }

    private function resolveMaxAllowedQuantity(Product $product, ?ProductVariant $variant): int
    {
        $cap = (int) config('cart.max_quantity_per_line', 999);

        if ($product->sold_individually) {
            return 1;
        }

        if ($product->max_order_quantity) {
            $cap = min($cap, (int) $product->max_order_quantity);
        }

        if ($product->track_inventory && !$product->allow_backorder) {
            $stockHolder = $variant ?: $product;
            $cap = min($cap, max(0, (int) $stockHolder->stock_quantity));
        }

        return $cap;
    }

    /**
     * @return array{0: ?AppliedCoupon, 1: ?string}
     */
    private function resolveCoupon(Cart $cart, float $subtotal, ?CouponSubject $subject, bool $hasSaleItems): array
    {
        $coupon = $cart->relationLoaded('coupon') ? $cart->coupon : $cart->coupon()->first();

        if (!$coupon) {
            return [null, null];
        }

        $effectiveSubject = $subject ?: new CouponSubject(
            userId: $cart->user_id,
            guestEmail: $cart->guest_email
        );

        try {
            $applied = $this->couponValidationService->validateForCart(
                code: $coupon->code,
                cartTotal: $subtotal,
                subject: $effectiveSubject,
                hasSaleItems: $hasSaleItems
            );

            return [$applied, null];
        } catch (CouponException $e) {
            // Coupon đã hết hạn hoặc không còn đủ điều kiện: không throw để khách vẫn xem được giỏ
            return [null, $e->getMessage()];
        }
    }
}
