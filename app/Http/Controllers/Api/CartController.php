<?php

namespace App\Http\Controllers\Api;

use App\Core\Base\BaseController;
use App\DTOs\Cart\AddCartItemDTO;
use App\DTOs\Cart\ApplyCouponDTO;
use App\DTOs\Cart\CartOwner;
use App\DTOs\Cart\CartSummary;
use App\Http\Requests\Api\Cart\AddCartItemRequest;
use App\Http\Requests\Api\Cart\ApplyCouponRequest;
use App\Http\Requests\Api\Cart\MergeCartRequest;
use App\Http\Requests\Api\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Services\Shared\Cart\CartPricingService;
use App\Services\Shared\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends BaseController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CartPricingService $pricingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $summary = $this->pricingService->summarize($cart);

        return $this->cartResponse($cart, $summary, __('Lấy thông tin giỏ hàng thành công.'));
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $cart = $this->getCart($request);

        $dto = new AddCartItemDTO(
            productUuid: (string) $request->input('product_uuid'),
            variantUuid: $request->input('variant_uuid') ? (string) $request->input('variant_uuid') : null,
            quantity: (int) $request->input('quantity', 1),
            meta: $request->input('meta')
        );

        $cart = $this->cartService->addItem($cart, $dto);
        $summary = $this->pricingService->summarize($cart);

        return $this->cartResponse($cart, $summary, __('Thêm sản phẩm vào giỏ hàng thành công.'), 201);
    }

    public function updateItem(UpdateCartItemRequest $request, string $itemUuid): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart = $this->cartService->updateItemQuantity($cart, $itemUuid, (int) $request->input('quantity'));
        $summary = $this->pricingService->summarize($cart);

        return $this->cartResponse($cart, $summary, __('Cập nhật số lượng sản phẩm thành công.'));
    }

    public function removeItem(Request $request, string $itemUuid): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart = $this->cartService->removeItem($cart, $itemUuid);
        $summary = $this->pricingService->summarize($cart);

        return $this->cartResponse($cart, $summary, __('Đã xóa sản phẩm khỏi giỏ hàng.'));
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart = $this->cartService->clear($cart);
        $summary = $this->pricingService->summarize($cart);

        return $this->cartResponse($cart, $summary, __('Đã xóa sạch giỏ hàng.'));
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $cart = $this->getCart($request);

        $dto = new ApplyCouponDTO(
            code: (string) $request->input('code'),
            email: $request->input('email') ?: $request->input('guest_email')
        );

        $cart = $this->cartService->applyCoupon($cart, $dto, $request->user('sanctum')?->id);
        $summary = $this->pricingService->summarize($cart);

        return $this->cartResponse($cart, $summary, __('Áp dụng mã giảm giá thành công.'));
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart = $this->cartService->removeCoupon($cart);
        $summary = $this->pricingService->summarize($cart);

        return $this->cartResponse($cart, $summary, __('Đã gỡ mã giảm giá khỏi giỏ hàng.'));
    }

    public function merge(MergeCartRequest $request): JsonResponse
    {
        $user = $request->user('sanctum') ?: $request->user();
        $cart = $this->cartService->mergeGuestCart((string) $request->input('guest_cart_token'), $user);
        $summary = $this->pricingService->summarize($cart);

        return $this->cartResponse($cart, $summary, __('Gộp giỏ hàng thành công.'));
    }

    private function getCart(Request $request): Cart
    {
        $token = $request->header('X-Cart-Token');
        $userId = $request->user('sanctum')?->id ?: $request->user()?->id;

        return $this->cartService->resolveCart(new CartOwner(userId: $userId, token: $token));
    }

    private function cartResponse(Cart $cart, CartSummary $summary, string $message, int $status = 200): JsonResponse
    {
        $resource = new CartResource(['cart' => $cart, 'summary' => $summary]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $resource->toArray(request()),
        ], $status)->header('X-Cart-Token', $cart->uuid);
    }
}
