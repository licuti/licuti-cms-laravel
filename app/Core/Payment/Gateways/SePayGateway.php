<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\DTOs\PaymentResult;
use Illuminate\Http\Request;

class SePayGateway extends AbstractPaymentGateway
{
    public function getCode(): string
    {
        return 'sepay';
    }

    public function getName(): string
    {
        return __('Chuyển khoản Tự động SePay (VietQR)');
    }

    public function getDescription(): string
    {
        return __('Tự động khớp lệnh và xác nhận đơn hàng ngay khi khách hàng quét mã VietQR chuyển khoản.');
    }

    public function getConfigFields(): array
    {
        return [
            'api_key' => [
                'type'        => 'password',
                'label'       => __('SePay API Token / Secret'),
                'placeholder' => __('Nhập API Key do SePay cung cấp để xác thực Webhook'),
                'required'    => true,
            ],
            'bank_account' => [
                'type'        => 'text',
                'label'       => __('Số tài khoản ngân hàng nhận tiền'),
                'placeholder' => __('Ví dụ: 0123456789'),
                'required'    => true,
            ],
            'bank_brand' => [
                'type'        => 'text',
                'label'       => __('Tên viết tắt ngân hàng (VCB, MB, TCB, ACB...)'),
                'placeholder' => __('Ví dụ: MB'),
                'required'    => true,
            ],
            'acc_name' => [
                'type'        => 'text',
                'label'       => __('Tên chủ tài khoản (In hoa không dấu)'),
                'placeholder' => __('Ví dụ: NGUYEN VAN A'),
                'required'    => true,
            ],
            'template' => [
                'type'        => 'select',
                'label'       => __('Kiểu hiển thị mã VietQR SePay'),
                'options'     => [
                    'compact' => __('Mẫu thu gọn (Compact)'),
                    'qr_only' => __('Chỉ hiển thị QR'),
                    'print'   => __('Mẫu in đầy đủ (Print)'),
                ],
                'default'     => 'compact',
            ],
        ];
    }

    public function charge(object $order, array $paymentData = []): PaymentResult
    {
        $code = (string) ($order->order_number ?? $order->code ?? ('ORD-' . time()));
        $amount = (float) ($order->total ?? $order->grand_total ?? 0);
        $bank = (string) $this->getConfig('bank_brand', 'MB');
        $acc = (string) $this->getConfig('bank_account', '');
        $name = (string) $this->getConfig('acc_name', '');
        $template = (string) $this->getConfig('template', 'compact');

        // Tạo link ảnh VietQR SePay theo chuẩn Open API
        $qrUrl = "https://qr.sepay.vn/img?bank={$bank}&acc={$acc}&template={$template}&amount={$amount}&des=" . urlencode($code);

        return PaymentResult::pending(
            transactionId: 'SEPAY-' . $code,
            redirectUrl: null,
            message: __('Vui lòng quét mã VietQR để thanh toán. Hệ thống sẽ tự động duyệt đơn khi nhận được tiền.'),
            rawData: [
                'gateway'      => 'sepay',
                'qr_url'       => $qrUrl,
                'memo'         => $code,
                'bank_brand'   => $bank,
                'bank_account' => $acc,
                'acc_name'     => $name,
                'amount'       => $amount,
            ]
        );
    }

    public function handleWebhook(Request $request): PaymentResult
    {
        $apiKey = (string) $this->getConfig('api_key');
        if (!empty($apiKey)) {
            $authHeader = (string) $request->header('Authorization');
            $expectedToken = 'Apikey ' . $apiKey;

            if ($authHeader !== $expectedToken && $request->input('apiKey') !== $apiKey) {
                return PaymentResult::failed(__('Xác thực Webhook SePay thất bại: API Key không khớp.'));
            }
        }

        $transactionId = (string) ($request->input('id') ?: $request->input('transaction_id') ?: ('SEPAY-' . time()));
        $orderCode = (string) ($request->input('content') ?: $request->input('code') ?: '');

        return PaymentResult::success(
            transactionId: $transactionId,
            message: __('Giao dịch SePay đã được ghi nhận tự động thành công.'),
            rawData: array_merge($request->all(), ['matched_order' => $orderCode])
        );
    }
}
