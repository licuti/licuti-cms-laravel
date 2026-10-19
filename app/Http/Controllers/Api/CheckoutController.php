<?php

namespace App\Http\Controllers\Api;

use App\Core\Base\BaseController;
use App\DTOs\Checkout\CheckoutDTO;
use App\Http\Requests\Api\CheckoutRequest;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CheckoutController extends BaseController
{
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $dto = CheckoutDTO::fromRequest($request);
            $result = $this->checkoutService->processCheckout($dto);

            $order = $result['order'];
            $paymentResult = $result['payment_result'];

            return response()->json([
                'success' => true,
                'message' => __('Khởi tạo đơn hàng thành công.'),
                'data'    => [
                    'order_number'   => $order->order_number,
                    'order_uuid'     => $order->uuid,
                    'total_amount'   => (float) $order->total_amount,
                    'currency'       => $order->currency,
                    'status'         => $order->status->value,
                    'is_redirect'    => $paymentResult->isRedirect(),
                    'redirect_url'   => $paymentResult->redirectUrl,
                    'qr_url'         => $paymentResult->rawData['qr_url'] ?? null,
                    'payment_status' => $paymentResult->status,
                ],
            ], 201);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Không thể hoàn tất đặt hàng. Vui lòng thử lại sau.'),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function handleReturn(Request $request, string $gateway): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('Đã nhận callback từ cổng thanh toán :gateway.', ['gateway' => strtoupper($gateway)]),
            'data'    => $request->all(),
        ]);
    }
}
