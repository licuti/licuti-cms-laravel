<?php

namespace App\Http\Controllers\Api\Payment;

use App\Core\Base\BaseController;
use App\Core\Payment\DTOs\PaymentResult;
use App\Core\Payment\PaymentGatewayRegistry;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\Payment\PaymentFailed;
use App\Events\Payment\PaymentSucceeded;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends BaseController
{
    public function __construct(
        private readonly PaymentGatewayRegistry $registry,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    public function handle(Request $request, string $gateway): JsonResponse
    {
        $gatewayCode = strtolower(trim($gateway));

        // 1. Kiểm tra driver cổng thanh toán có tồn tại trong registry không
        if (!$this->registry->has($gatewayCode)) {
            Log::warning("Payment webhook: Cổng không được hỗ trợ", ['gateway' => $gatewayCode]);
            return response()->json([
                'success' => false,
                'message' => __('Cổng thanh toán không tồn tại hoặc chưa được hỗ trợ.'),
            ], 404);
        }

        // 2. Lấy cấu hình của phương thức thanh toán trong Database
        /** @var PaymentMethod|null $paymentMethod */
        $paymentMethod = PaymentMethod::where('code', $gatewayCode)
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            Log::warning("Payment webhook: Phương thức đang bị tắt hoặc chưa cấu hình", ['gateway' => $gatewayCode]);
            return response()->json([
                'success' => false,
                'message' => __('Phương thức thanh toán đang bị vô hiệu hóa.'),
            ], 400);
        }

        try {
            // 3. Khởi tạo driver và cấu hình tương ứng
            $driver = $this->registry->get($gatewayCode, $paymentMethod->config ?? []);

            // 4. Xử lý webhook và xác thực chữ ký (Signature Verification)
            $result = $driver->handleWebhook($request);

            if (!$result->isSuccess()) {
                Log::warning("Payment webhook: Xác thực thất bại hoặc giao dịch không thành công", [
                    'gateway' => $gatewayCode,
                    'message' => $result->message,
                ]);

                event(new PaymentFailed(null, null, $result));

                return response()->json([
                    'success' => false,
                    'message' => $result->message ?? __('Xác thực webhook thất bại.'),
                ], 400);
            }

            // 5. Kiểm tra Idempotency để chống xử lý trùng lặp giao dịch
            $transactionId = $result->transactionId;
            if ($transactionId) {
                $existing = $this->paymentRepository->findByGatewayTransactionId($gatewayCode, $transactionId);
                if ($existing && $existing->isSuccess()) {
                    Log::info("Payment webhook: Giao dịch đã xử lý trước đó (Idempotent)", [
                        'gateway'        => $gatewayCode,
                        'transaction_id' => $transactionId,
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => __('Giao dịch đã được ghi nhận trước đó.'),
                    ], 200);
                }
            }

            // 6. Định vị đơn hàng từ dữ liệu webhook
            $order = $this->resolveOrder($gatewayCode, $result, $request);

            if (!$order) {
                Log::error("Payment webhook: Không tìm thấy đơn hàng cho giao dịch", [
                    'gateway'        => $gatewayCode,
                    'transaction_id' => $transactionId,
                    'raw'            => $result->rawData,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => __('Không thể xác định đơn hàng tương ứng.'),
                ], 404);
            }

            // 7. Cập nhật Payment và trạng thái đơn hàng trong Database Transaction
            $payment = DB::transaction(function () use ($order, $gatewayCode, $result, $transactionId, $paymentMethod) {
                // Ghi nhận bản ghi thanh toán
                $payment = $this->paymentRepository->recordPayment(
                    order: $order,
                    gatewayCode: $gatewayCode,
                    amount: (float) ($result->rawData['amount'] ?? $order->total_amount),
                    transactionId: $transactionId,
                    status: PaymentStatus::SUCCESS,
                    raw: $result->rawData
                );

                // Cập nhật trạng thái đơn hàng nếu đơn đang chờ
                if (in_array($order->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED])) {
                    $oldStatus = $order->status;
                    $order->update([
                        'status'            => OrderStatus::PROCESSING,
                        'paid_at'           => now(),
                        'payment_method_id' => $paymentMethod->id,
                    ]);

                    $order->statusHistories()->create([
                        'from_status'        => $oldStatus,
                        'to_status'          => OrderStatus::PROCESSING,
                        'note'               => __('Thanh toán thành công qua cổng :gateway. Mã GD: :txn', [
                            'gateway' => strtoupper($gatewayCode),
                            'txn'     => $transactionId ?? 'N/A',
                        ]),
                        'changed_by_user_id' => null,
                    ]);
                } elseif (is_null($order->paid_at)) {
                    $order->update(['paid_at' => now()]);
                }

                return $payment;
            });

            // 8. Kích hoạt Event thành công
            event(new PaymentSucceeded($order, $payment, $result));

            Log::info("Payment webhook: Xử lý thành công", [
                'order'          => $order->order_number,
                'gateway'        => $gatewayCode,
                'transaction_id' => $transactionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Xử lý Webhook thanh toán thành công.'),
                'data'    => [
                    'order_number'   => $order->order_number,
                    'transaction_id' => $transactionId,
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error("Payment webhook exception", [
                'gateway' => $gatewayCode,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Đã xảy ra lỗi máy chủ trong quá trình xử lý webhook.'),
            ], 500);
        }
    }

    /**
     * Tự động nhận diện Đơn hàng từ payload của các cổng thanh toán khác nhau.
     */
    private function resolveOrder(string $gatewayCode, PaymentResult $result, Request $request): ?Order
    {
        $raw = $result->rawData;

        // 1. Tìm theo order_number trực tiếp trong payload
        $orderNumber = $raw['order_number'] ?? $raw['orderNumber'] ?? null;
        if ($orderNumber) {
            $order = $this->orderRepository->findByOrderNumber((string) $orderNumber);
            if ($order) {
                return $order;
            }
        }

        // 2. Tìm theo order_id trực tiếp
        $orderId = $raw['order_id'] ?? $raw['orderId'] ?? null;
        if ($orderId && is_numeric($orderId)) {
            $order = Order::find((int) $orderId);
            if ($order) {
                return $order;
            }
        }

        // 3. Theo từng cổng cụ thể
        return match ($gatewayCode) {
            'vnpay' => isset($raw['vnp_TxnRef']) 
                ? $this->orderRepository->findByOrderNumber((string) $raw['vnp_TxnRef']) 
                : null,

            'sepay' => $this->resolveOrderFromText(
                (string) ($raw['content'] ?? $raw['description'] ?? '')
            ),

            'payos' => (function() use ($raw) {
                $orderCode = $raw['data']['orderCode'] ?? $raw['orderCode'] ?? null;
                if ($orderCode) {
                    $found = Order::find((int) $orderCode) ?? $this->orderRepository->findByOrderNumber((string) $orderCode);
                    if ($found) return $found;
                }
                $desc = (string) ($raw['data']['description'] ?? $raw['description'] ?? '');
                return $this->resolveOrderFromText($desc);
            })(),

            'momo' => isset($raw['orderId'])
                ? ($this->orderRepository->findByOrderNumber((string) $raw['orderId']) ?? Order::where('order_number', 'like', "%{$raw['orderId']}%")->first())
                : null,

            'stripe' => (function() use ($raw) {
                $obj = $raw['data']['object'] ?? $raw;
                $ref = $obj['client_reference_id'] ?? $obj['metadata']['order_number'] ?? null;
                if ($ref) {
                    return $this->orderRepository->findByOrderNumber((string) $ref) ?? Order::where('uuid', $ref)->first();
                }
                return null;
            })(),

            'paypal' => (function() use ($raw) {
                $resource = $raw['resource'] ?? $raw;
                $ref = $resource['purchase_units'][0]['reference_id'] ?? $resource['custom_id'] ?? null;
                if ($ref) {
                    return $this->orderRepository->findByOrderNumber((string) $ref) ?? Order::where('uuid', $ref)->first();
                }
                return null;
            })(),

            default => null,
        };
    }

    /**
     * Trích xuất mã đơn hàng dạng ORD-YYYYMMDD-XXXXXX từ nội dung chuyển khoản.
     */
    private function resolveOrderFromText(string $text): ?Order
    {
        if (empty($text)) {
            return null;
        }

        if (preg_match('/ORD[-_]\d{8}[-_][A-Z0-9]+/i', $text, $matches)) {
            $orderNumber = str_replace('_', '-', strtoupper($matches[0]));
            $order = $this->orderRepository->findByOrderNumber($orderNumber);
            if ($order) {
                return $order;
            }
        }

        return null;
    }
}
