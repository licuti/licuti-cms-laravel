<?php

namespace App\Services\Checkout;

use App\Core\Base\BaseService;
use App\Core\Payment\DTOs\PaymentResult;
use App\Core\Payment\PaymentGatewayRegistry;
use App\DTOs\Checkout\CheckoutDTO;
use App\DTOs\Order\OrderDTO;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Services\Admin\Order\OrderService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CheckoutService extends BaseService
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentGatewayRegistry $gatewayRegistry,
        private readonly PaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * Khởi tạo quá trình Checkout: kiểm tra giá, tạo đơn hàng và gọi cổng thanh toán.
     *
     * @return array{order: Order, payment: Payment, result: PaymentResult}
     */
    public function processCheckout(CheckoutDTO $dto): array
    {
        // 1. Kiểm tra phương thức thanh toán
        /** @var PaymentMethod|null $paymentMethod */
        $paymentMethod = PaymentMethod::where('code', $dto->paymentMethodCode)
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            throw new InvalidArgumentException(__('Phương thức thanh toán không tồn tại hoặc đang bị tạm dừng.'));
        }

        if (!$this->gatewayRegistry->has($dto->paymentMethodCode)) {
            throw new InvalidArgumentException(__('Cổng thanh toán chưa được tích hợp trên hệ thống.'));
        }

        // 2. Snapshot giá và kiểm tra tồn tại sản phẩm từ Database
        $orderItems = [];
        $subtotal = 0.0;

        foreach ($dto->items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $variantId = !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null;
            $qty = max(1, (int) ($item['quantity'] ?? 1));

            /** @var Product $product */
            $product = Product::with('translations')->findOrFail($productId);
            $productName = $product->name ?? "Product #{$productId}";
            $sku = $product->sku ?? null;
            $unitPrice = (float) $product->price;

            // Nếu có biến thể sản phẩm, lấy giá và SKU theo biến thể
            if ($variantId) {
                /** @var ProductVariant|null $variant */
                $variant = ProductVariant::find($variantId);
                if ($variant) {
                    $unitPrice = (float) ($variant->price ?: $unitPrice);
                    $sku = $variant->sku ?: $sku;
                }
            }

            $itemSubtotal = $unitPrice * $qty;
            $subtotal += $itemSubtotal;

            $orderItems[] = [
                'product_id'         => $productId,
                'product_variant_id' => $variantId,
                'product_name'       => $productName,
                'product_sku'        => $sku,
                'unit_price'         => $unitPrice,
                'quantity'           => $qty,
                'discount_amount'    => 0,
                'subtotal'           => $itemSubtotal,
                'meta'               => $item['meta'] ?? null,
            ];
        }

        if (empty($orderItems)) {
            throw new InvalidArgumentException(__('Giỏ hàng không có sản phẩm nào hợp lệ.'));
        }

        $totalAmount = max(0, $subtotal + $dto->shippingFee - $dto->discountAmount);

        // 3. Kiểm tra hạn mức thanh toán của phương thức
        if (!$paymentMethod->isAvailableFor($totalAmount, $dto->currency)) {
            throw new InvalidArgumentException(__('Số tiền đơn hàng không nằm trong hạn mức cho phép của phương thức :name.', [
                'name' => $paymentMethod->name,
            ]));
        }

        // 4. Tạo Order qua DB Transaction
        return DB::transaction(function () use ($dto, $paymentMethod, $orderItems, $subtotal, $totalAmount) {
            $orderDTO = new OrderDTO(
                userId: $dto->userId,
                paymentMethodId: $paymentMethod->id,
                shippingAddress: $dto->shippingAddress,
                items: $orderItems,
                subtotal: $subtotal,
                discountAmount: $dto->discountAmount,
                shippingFee: $dto->shippingFee,
                totalAmount: $totalAmount,
                notes: $dto->notes,
                status: OrderStatus::PENDING,
                currency: $dto->currency
            );

            $order = $this->orderService->create($orderDTO);

            // 5. Khởi tạo Gateway driver và gửi yêu cầu charge()
            $gateway = $this->gatewayRegistry->get($paymentMethod->code, $paymentMethod->config ?? []);

            $paymentData = [
                'return_url' => url("/checkout/return/{$paymentMethod->code}"),
                'cancel_url' => url("/checkout/cancel/{$paymentMethod->code}"),
            ];

            // Chuyển đối tượng order sang dạng tương thích với Gateway driver
            $orderPayload = (object)[
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'total'        => $order->total_amount,
                'grand_total'  => $order->total_amount,
                'currency'     => $order->currency,
            ];

            $paymentResult = $gateway->charge($orderPayload, $paymentData);

            // 6. Ghi nhận bản ghi thanh toán ban đầu
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
        });
    }
}
