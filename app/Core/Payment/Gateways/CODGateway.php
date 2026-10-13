<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\DTOs\PaymentResult;
use Illuminate\Http\Request;

class CODGateway extends AbstractPaymentGateway
{
    public function getCode(): string
    {
        return 'cod';
    }

    public function getName(): string
    {
        return __('Thanh toán khi nhận hàng (COD)');
    }

    public function getDescription(): string
    {
        return __('Khách hàng thanh toán bằng tiền mặt trực tiếp cho nhân viên giao hàng.');
    }

    public function getConfigFields(): array
    {
        return [
            'allow_inspection' => [
                'type'        => 'switch',
                'label'       => __('Cho phép kiểm tra hàng trước khi thanh toán'),
                'description' => __('Bật để cho phép shipper mở kiện hàng cho khách đồng kiểm.'),
                'default'     => true,
            ],
            'shipper_note' => [
                'type'        => 'text',
                'label'       => __('Ghi chú mặc định cho nhân viên giao hàng'),
                'placeholder' => __('Ví dụ: Cho xem hàng, không cho thử.'),
                'required'    => false,
            ],
        ];
    }

    public function charge(object $order, array $paymentData = []): PaymentResult
    {
        $code = $order->order_number ?? $order->code ?? ('ORD-' . time());
        return PaymentResult::pending(
            transactionId: 'COD-' . $code,
            redirectUrl: null,
            message: __('Đơn hàng đã được ghi nhận thanh toán khi nhận hàng (COD).'),
            rawData: ['gateway' => 'cod', 'allow_inspection' => $this->getConfig('allow_inspection', true)]
        );
    }

    public function handleWebhook(Request $request): PaymentResult
    {
        return PaymentResult::success(
            transactionId: (string) ($request->input('transaction_id') ?: 'COD-' . time()),
            message: __('Đã xác nhận thu tiền COD.')
        );
    }
}
