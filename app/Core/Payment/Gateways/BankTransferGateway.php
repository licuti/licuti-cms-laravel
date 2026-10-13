<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\DTOs\PaymentResult;
use Illuminate\Http\Request;

class BankTransferGateway extends AbstractPaymentGateway
{
    public function getCode(): string
    {
        return 'bank_transfer';
    }

    public function getName(): string
    {
        return __('Chuyển khoản Ngân hàng (VietQR)');
    }

    public function getDescription(): string
    {
        return __('Khách hàng chuyển khoản Internet Banking hoặc quét mã VietQR tự động.');
    }

    public function getConfigFields(): array
    {
        return [
            'bank_name' => [
                'type'        => 'text',
                'label'       => __('Tên ngân hàng (hoặc mã BIN)'),
                'placeholder' => __('Ví dụ: Vietcombank, MBBank, Techcombank...'),
                'required'    => true,
            ],
            'account_number' => [
                'type'        => 'text',
                'label'       => __('Số tài khoản ngân hàng'),
                'placeholder' => __('Ví dụ: 0123456789'),
                'required'    => true,
            ],
            'account_holder' => [
                'type'        => 'text',
                'label'       => __('Tên chủ tài khoản (In hoa không dấu)'),
                'placeholder' => __('Ví dụ: NGUYEN VAN A'),
                'required'    => true,
            ],
            'branch' => [
                'type'        => 'text',
                'label'       => __('Chi nhánh ngân hàng'),
                'placeholder' => __('Ví dụ: Chi nhánh Ba Đình, Hà Nội'),
                'required'    => false,
            ],
            'qr_template' => [
                'type'        => 'select',
                'label'       => __('Kiểu hiển thị mã VietQR'),
                'options'     => [
                    'compact'  => __('Mẫu thu gọn (Compact)'),
                    'qr_only'  => __('Chỉ hiển thị QR'),
                    'print'    => __('Mẫu in đầy đủ (Print)'),
                ],
                'default'     => 'compact',
            ],
        ];
    }

    public function charge(object $order, array $paymentData = []): PaymentResult
    {
        $code = $order->order_number ?? $order->code ?? ('ORD-' . time());
        $amount = (float) ($order->total ?? $order->grand_total ?? 0);
        $bank = (string) $this->getConfig('bank_name', 'Vietcombank');
        $acc = (string) $this->getConfig('account_number', '');
        $name = (string) $this->getConfig('account_holder', '');

        // Sinh link VietQR nhanh theo chuẩn Open API VietQR
        $qrUrl = "https://img.vietqr.io/image/{$bank}-{$acc}-compact.png?amount={$amount}&addInfo={$code}&accountName=" . urlencode($name);

        return PaymentResult::pending(
            transactionId: 'BT-' . $code,
            redirectUrl: null,
            message: __('Vui lòng quét mã QR hoặc chuyển khoản theo đúng cú pháp nội dung đơn hàng.'),
            rawData: [
                'gateway'        => 'bank_transfer',
                'bank_name'      => $bank,
                'account_number' => $acc,
                'account_holder' => $name,
                'qr_url'         => $qrUrl,
                'transfer_memo'  => $code,
            ]
        );
    }

    public function handleWebhook(Request $request): PaymentResult
    {
        $transactionId = (string) ($request->input('transaction_id') ?: $request->input('id') ?: ('BT-' . time()));
        return PaymentResult::success(
            transactionId: $transactionId,
            message: __('Xác nhận chuyển khoản thành công từ ngân hàng.'),
            rawData: $request->all()
        );
    }
}
