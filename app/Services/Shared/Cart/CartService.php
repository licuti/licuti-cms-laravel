<?php

namespace App\Services\Shared\Cart;

use App\Actions\Cart\AddItemToCartAction;
use App\Actions\Cart\MergeGuestCartAction;
use App\Core\Base\BaseService;
use App\DTOs\Cart\AddCartItemDTO;
use App\DTOs\Cart\ApplyCouponDTO;
use App\DTOs\Cart\CartOwner;
use App\DTOs\Coupon\CouponSubject;
use App\Enums\CartStatus;
use App\Exceptions\Cart\CartItemNotFoundException;
use App\Models\Cart;
use App\Models\User;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Services\Admin\Coupon\CouponValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService extends BaseService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartItemRepositoryInterface $cartItemRepository,
        private readonly AddItemToCartAction $addItemAction,
        private readonly MergeGuestCartAction $mergeAction,
        private readonly CartItemGuard $guard,
        private readonly CartPricingService $pricingService,
        private readonly CouponValidationService $couponValidationService
    ) {}

    /**
     * Lấy hoặc khởi tạo giỏ hàng cho User hoặc Guest.
     */
    public function resolveCart(CartOwner $owner): Cart
    {
        return $this->handleTransaction(function () use ($owner) {
            /** @var Cart|null $cart */
            $cart = null;

            if ($owner->userId) {
                $cart = Cart::where('user_id', $owner->userId)
                    ->where('status', CartStatus::ACTIVE->value)
                    ->lockForUpdate()
                    ->first();
            } elseif (!empty($owner->token)) {
                $cart = $this->cartRepository->findActiveByUuid($owner->token);
                // Giỏ hàng của user không được truy cập qua guest token
                if ($cart && $cart->user_id !== null) {
                    $cart = null;
                }
            }

            if (!$cart) {
                $cart = $this->cartRepository->create([
                    'uuid'             => (string) Str::uuid(),
                    'user_id'          => $owner->userId,
                    'currency'         => 'VND',
                    'status'           => CartStatus::ACTIVE->value,
                    'last_activity_at' => now(),
                ]);
            } else {
                $cart->touchActivity();
            }

            return $cart->load(['items.product.translations', 'items.variant.attributeValues.attribute', 'coupon']);
        });
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function addItem(Cart $cart, AddCartItemDTO $dto): Cart
    {
        return $this->handleTransaction(function () use ($cart, $dto) {
            // Khóa hàng cart để chống race condition
            Cart::where('id', $cart->id)->lockForUpdate()->first();

            return $this->addItemAction->execute($cart, $dto);
        });
    }

    /**
     * Cập nhật số lượng của dòng sản phẩm (IDOR-safe).
     */
    public function updateItemQuantity(Cart $cart, string $itemUuid, int $quantity): Cart
    {
        if ($quantity <= 0) {
            return $this->removeItem($cart, $itemUuid);
        }

        return $this->handleTransaction(function () use ($cart, $itemUuid, $quantity) {
            Cart::where('id', $cart->id)->lockForUpdate()->first();

            $item = $this->cartItemRepository->findByUuidInCart($cart->id, $itemUuid);
            if (!$item) {
                throw new CartItemNotFoundException(__('Sản phẩm không tồn tại trong giỏ hàng này.'));
            }

            $product = $item->product;
            $variant = $item->variant;

            $this->guard->assertPurchasable($product, $variant);
            $this->guard->assertQuantityAllowed($product, $quantity);
            $this->guard->assertInStock($product, $variant, $quantity);

            $item->update(['quantity' => $quantity]);
            $cart->touchActivity();

            return $cart->fresh(['items.product.translations', 'items.variant.attributeValues.attribute', 'coupon']);
        });
    }

    /**
     * Xóa dòng sản phẩm khỏi giỏ hàng.
     */
    public function removeItem(Cart $cart, string $itemUuid): Cart
    {
        return $this->handleTransaction(function () use ($cart, $itemUuid) {
            Cart::where('id', $cart->id)->lockForUpdate()->first();

            $item = $this->cartItemRepository->findByUuidInCart($cart->id, $itemUuid);
            if (!$item) {
                throw new CartItemNotFoundException(__('Sản phẩm không tồn tại trong giỏ hàng này.'));
            }

            $item->delete();
            $cart->touchActivity();

            return $cart->fresh(['items.product.translations', 'items.variant.attributeValues.attribute', 'coupon']);
        });
    }

    /**
     * Xóa sạch toàn bộ sản phẩm trong giỏ hàng.
     */
    public function clear(Cart $cart): Cart
    {
        return $this->handleTransaction(function () use ($cart) {
            Cart::where('id', $cart->id)->lockForUpdate()->first();

            $cart->items()->delete();
            $cart->update(['coupon_id' => null]);
            $cart->touchActivity();

            return $cart->fresh(['items', 'coupon']);
        });
    }

    /**
     * Áp dụng mã giảm giá cho giỏ hàng.
     */
    public function applyCoupon(Cart $cart, ApplyCouponDTO $dto, ?int $userId = null): Cart
    {
        return $this->handleTransaction(function () use ($cart, $dto, $userId) {
            Cart::where('id', $cart->id)->lockForUpdate()->first();

            // Tính toán tạm tính để validate coupon
            $summary = $this->pricingService->summarize($cart);

            $subject = new CouponSubject(
                userId: $userId ?: $cart->user_id,
                guestEmail: $dto->email ?: $cart->guest_email
            );

            $appliedCoupon = $this->couponValidationService->validateForCart(
                code: $dto->code,
                cartTotal: $summary->subtotal,
                subject: $subject,
                hasSaleItems: $summary->hasSaleItems
            );

            $cart->update([
                'coupon_id'        => $appliedCoupon->coupon->id,
                'guest_email'      => $dto->email ?: $cart->guest_email,
                'last_activity_at' => now(),
            ]);

            return $cart->fresh(['items.product.translations', 'items.variant.attributeValues.attribute', 'coupon']);
        });
    }

    /**
     * Gỡ bỏ mã giảm giá khỏi giỏ hàng.
     */
    public function removeCoupon(Cart $cart): Cart
    {
        return $this->handleTransaction(function () use ($cart) {
            $cart->update(['coupon_id' => null]);
            $cart->touchActivity();

            return $cart->fresh(['items.product.translations', 'items.variant.attributeValues.attribute', 'coupon']);
        });
    }

    /**
     * Gộp giỏ hàng khách vãng lai vào tài khoản người dùng sau khi đăng nhập.
     */
    public function mergeGuestCart(string $guestToken, User $user): Cart
    {
        $guestCart = $this->cartRepository->findActiveByUuid($guestToken);

        if (!$guestCart || $guestCart->user_id !== null) {
            return $this->resolveCart(new CartOwner(userId: $user->id));
        }

        return $this->mergeAction->execute($guestCart, $user);
    }
}
