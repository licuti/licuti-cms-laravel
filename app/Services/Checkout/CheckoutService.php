<?php

namespace App\Services\Checkout;

use App\Actions\Order\PlaceOrderAction;
use App\Contracts\Pricing\PriceResolverInterface;
use App\Contracts\Shipping\ShippingFeeCalculatorInterface;
use App\Core\Base\BaseService;
use App\Core\Payment\DTOs\PaymentResult;
use App\Core\Payment\PaymentGatewayRegistry;
use App\DTOs\Cart\CartLineSummary;
use App\DTOs\Checkout\CheckoutContext;
use App\DTOs\Checkout\CheckoutDTO;
use App\DTOs\Coupon\CouponSubject;
use App\Enums\PaymentStatus;
use App\Exceptions\Cart\EmptyCartException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Services\Admin\Coupon\CouponValidationService;
use App\Services\Admin\Order\OrderService;
use App\Services\Shared\Cart\CartItemGuard;
use App\Services\Shared\Cart\CartPricingService;
use InvalidArgumentException;

class CheckoutService extends BaseService
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentGatewayRegistry $gatewayRegistry,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartPricingService $cartPricingService,
        private readonly PriceResolverInterface $priceResolver,
        private readonly ShippingFeeCalculatorInterface $shippingCalculator,
        private readonly CouponValidationService $couponValidationService,
        private readonly PlaceOrderAction $placeOrderAction,
        private readonly CartItemGuard $cartItemGuard
    ) {}

    /**
     * Khởi tạo quá trình Checkout: kiểm tra giá, tạo đơn hàng và gọi cổng thanh toán.
     *
     * @return array{order: Order, payment: Payment, payment_result: PaymentResult}
     */
    public function processCheckout(CheckoutDTO $dto): array
    {
        $paymentMethod = $this->assertPaymentMethodUsable($dto->paymentMethodCode);
        $context = $this->resolveCheckoutContext($dto);

        if (!$paymentMethod->isAvailableFor($context->totalAmount, $context->currency)) {
            throw new InvalidArgumentException(__('Số tiền đơn hàng không nằm trong hạn mức cho phép của phương thức :name.', [
                'name' => $paymentMethod->name,
            ]));
        }

        // 1. Tạo đơn hàng trong Transaction (trừ kho, trừ coupon, convert cart)
        $order = $this->placeOrderAction->execute($context, $paymentMethod);

        // 2. Gọi cổng thanh toán NGOÀI Transaction để tránh giữ lock DB lâu
        $paymentResult = $this->chargeOutsideTransaction($order, $paymentMethod);

        // 3. Ghi nhận giao dịch
        $paymentStatus = $paymentResult->isSuccess() ? PaymentStatus::SUCCESS : PaymentStatus::PENDING;
        $payment = $this->paymentRepository->recordPayment(
            order: $order,
            gatewayCode: $paymentMethod->code,
            amount: $order->total_amount,
            transactionId: $paymentResult->transactionId,
            status: $paymentStatus,
            raw: $paymentResult->rawData
        );

        return [
            'order'          => $order,
            'payment'        => $payment,
            'payment_result' => $paymentResult,
        ];
    }

    private function assertPaymentMethodUsable(string $code): PaymentMethod
    {
        /** @var PaymentMethod|null $paymentMethod */
        $paymentMethod = PaymentMethod::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            throw new InvalidArgumentException(__('Phương thức thanh toán không tồn tại hoặc đang bị tạm dừng.'));
        }

        if (!$this->gatewayRegistry->has($code)) {
            throw new InvalidArgumentException(__('Cổng thanh toán chưa được tích hợp trên hệ thống.'));
        }

        return $paymentMethod;
    }

    private function resolveCheckoutContext(CheckoutDTO $dto): CheckoutContext
    {
        if (!empty($dto->cartToken)) {
            return $this->resolveContextFromCart($dto);
        }

        return $this->resolveContextFromDirectItems($dto);
    }

    private function resolveContextFromCart(CheckoutDTO $dto): CheckoutContext
    {
        /** @var Cart|null $cart */
        $cart = $this->cartRepository->findByUuidWithItems($dto->cartToken);

        if (!$cart || $cart->items->isEmpty()) {
            throw new EmptyCartException(__('Giỏ hàng đang trống hoặc không tồn tại.'));
        }

        $email = $dto->shippingAddress['email'] ?? null;
        $subject = new CouponSubject(userId: $dto->userId, guestEmail: $email);
        $summary = $this->cartPricingService->summarize($cart, $subject);

        if ($summary->hasIssues) {
            throw new InvalidArgumentException(__('Một số sản phẩm trong giỏ hàng đã thay đổi tồn kho hoặc ngừng bán. Vui lòng kiểm tra lại giỏ hàng.'));
        }

        $orderItems = [];
        foreach ($summary->lines as $line) {
            $orderItems[] = [
                'product_id'         => (int) $line->product->id,
                'product_variant_id' => $line->variant?->id,
                'product_name'       => $line->name,
                'product_sku'        => $line->sku,
                'unit_price'         => $line->unitPrice,
                'quantity'           => $line->quantity,
                'discount_amount'    => 0,
                'subtotal'           => $line->lineTotal,
                'meta'               => null,
            ];
        }

        return new CheckoutContext(
            userId: $dto->userId,
            paymentMethodCode: $dto->paymentMethodCode,
            shippingAddress: $dto->shippingAddress,
            orderItems: $orderItems,
            subtotal: $summary->subtotal,
            discountAmount: $summary->discountAmount,
            shippingFee: $summary->shippingFee,
            totalAmount: $summary->grandTotal,
            cart: $cart,
            coupon: $summary->coupon?->coupon,
            couponCode: $summary->coupon?->code,
            notes: $dto->notes,
            currency: $dto->currency,
            guestEmail: $email
        );
    }

    private function resolveContextFromDirectItems(CheckoutDTO $dto): CheckoutContext
    {
        if (empty($dto->items)) {
            throw new InvalidArgumentException(__('Vui lòng chọn sản phẩm cần mua.'));
        }

        $orderItems = [];
        $subtotal = 0.0;
        $lineSummaries = collect();

        foreach ($dto->items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $variantId = !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null;
            $qty = max(1, (int) ($item['quantity'] ?? 1));

            /** @var Product $product */
            $product = Product::with('translations')->findOrFail($productId);
            /** @var ProductVariant|null $variant */
            $variant = $variantId ? ProductVariant::find($variantId) : null;

            // Kiểm tra khả dụng, số lượng tối thiểu/tối đa, mua lẻ và tồn kho
            $this->cartItemGuard->assertPurchasable($product, $variant);
            $this->cartItemGuard->assertQuantityAllowed($product, $qty);
            $this->cartItemGuard->assertInStock($product, $variant, $qty);

            $priceInfo = $this->priceResolver->resolve($product, $variant);
            $itemSubtotal = round($priceInfo->unitPrice * $qty, 2);
            $subtotal += $itemSubtotal;

            $orderItems[] = [
                'product_id'         => $productId,
                'product_variant_id' => $variantId,
                'product_name'       => $product->name ?: "Product #{$productId}",
                'product_sku'        => $variant?->sku ?: $product->sku,
                'unit_price'         => $priceInfo->unitPrice,
                'quantity'           => $qty,
                'discount_amount'    => 0,
                'subtotal'           => $itemSubtotal,
                'meta'               => $item['meta'] ?? null,
            ];

            $lineSummaries->push(new CartLineSummary(
                itemUuid: '',
                productUuid: $product->uuid,
                variantUuid: $variant?->uuid,
                name: $product->name,
                variantName: $variant?->name,
                sku: $variant?->sku ?: $product->sku,
                imageUrl: null,
                quantity: $qty,
                unitPrice: $priceInfo->unitPrice,
                comparePrice: $priceInfo->comparePrice,
                snapshotPrice: $priceInfo->unitPrice,
                lineTotal: $itemSubtotal,
                priceChanged: false,
                isOnSale: $priceInfo->isOnSale,
                maxQuantity: 999,
                issues: [],
                product: $product,
                variant: $variant
            ));
        }

        $discountAmount = 0.0;
        $appliedCoupon = null;
        $isFreeShipping = false;
        $email = $dto->shippingAddress['email'] ?? null;

        if (!empty($dto->couponCode)) {
            $subject = new CouponSubject(userId: $dto->userId, guestEmail: $email);
            $hasSaleItems = $lineSummaries->contains(fn ($l) => $l->isOnSale);
            $appliedCoupon = $this->couponValidationService->validateForCart($dto->couponCode, $subtotal, $subject, $hasSaleItems);
            $discountAmount = $appliedCoupon->discountAmount;
            $isFreeShipping = $appliedCoupon->freeShipping;
        }

        $shippingFee = $this->shippingCalculator->calculate($lineSummaries, $isFreeShipping);
        $totalAmount = max(0.0, round($subtotal - $discountAmount + $shippingFee, 2));

        return new CheckoutContext(
            userId: $dto->userId,
            paymentMethodCode: $dto->paymentMethodCode,
            shippingAddress: $dto->shippingAddress,
            orderItems: $orderItems,
            subtotal: round($subtotal, 2),
            discountAmount: round($discountAmount, 2),
            shippingFee: round($shippingFee, 2),
            totalAmount: $totalAmount,
            cart: null,
            coupon: $appliedCoupon?->coupon,
            couponCode: $appliedCoupon?->code,
            notes: $dto->notes,
            currency: $dto->currency,
            guestEmail: $email
        );
    }

    private function chargeOutsideTransaction(Order $order, PaymentMethod $paymentMethod): PaymentResult
    {
        $gateway = $this->gatewayRegistry->get($paymentMethod->code, $paymentMethod->config ?? []);

        $paymentData = [
            'return_url' => url("/checkout/return/{$paymentMethod->code}"),
            'cancel_url' => url("/checkout/cancel/{$paymentMethod->code}"),
        ];

        $orderPayload = (object)[
            'id'           => $order->id,
            'order_number' => $order->order_number,
            'total'        => $order->total_amount,
            'grand_total'  => $order->total_amount,
            'currency'     => $order->currency,
        ];

        try {
            return $gateway->charge($orderPayload, $paymentData);
        } catch (\Throwable $e) {
            // Compensation: Hủy đơn để hoàn kho và coupon ngay lập tức
            $this->orderService->cancel(
                $order->uuid,
                __('Lỗi kết nối cổng thanh toán: :msg', ['msg' => $e->getMessage()])
            );
            throw $e;
        }
    }
}
