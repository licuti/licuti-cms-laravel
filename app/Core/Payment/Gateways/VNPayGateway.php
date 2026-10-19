<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\DTOs\PaymentResult;
use Illuminate\Http\Request;

class VNPayGateway extends AbstractPaymentGateway
{
    public function getCode(): string
    {
        return 'vnpay';
    }

    public function getName(): string
    {
        return __('Cổng thanh toán VNPAY (QR / ATM / Visa)');
    }

    public function getDescription(): string
    {
        return __('Cổng thanh toán trực tuyến qua VNPAY-QR, thẻ ngân hàng nội địa hoặc thẻ quốc tế.');
    }

    public function getConfigFields(): array
    {
        return [
            'tmn_code' => [
                'type'        => 'text',
                'label'       => __('Mã định danh Website (vnp_TmnCode)'),
                'placeholder' => __('Ví dụ: CODESANDBOX'),
                'required'    => true,
            ],
            'hash_secret' => [
                'type'        => 'password',
                'label'       => __('Khóa bí mật tạo chữ ký (vnp_HashSecret)'),
                'placeholder' => __('Nhập chuỗi bí mật do VNPay cung cấp'),
                'required'    => true,
            ],
            'url' => [
                'type'        => 'text',
                'label'       => __('Endpoint thanh toán VNPay'),
                'placeholder' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
                'default'     => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
                'required'    => true,
            ],
            'locale' => [
                'type'        => 'select',
                'label'       => __('Ngôn ngữ giao diện thanh toán'),
                'options'     => [
                    'vn' => __('Tiếng Việt'),
                    'en' => __('Tiếng Anh'),
                ],
                'default'     => 'vn',
            ],
        ];
    }

    public function charge(object $order, array $paymentData = []): PaymentResult
    {
        $tmnCode = (string) $this->getConfig('tmn_code', '');
        $hashSecret = (string) $this->getConfig('hash_secret', '');
        $vnpUrl = (string) $this->getConfig('url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');

        if (empty($tmnCode) || empty($hashSecret)) {
            return PaymentResult::failed(__('Cổng VNPay chưa được cấu hình đầy đủ mã website và khóa bí mật.'));
        }

        $code = (string) ($order->order_number ?? $order->code ?? ('ORD' . time()));
        $amount = (int) round(($order->total ?? $order->grand_total ?? 0) * 100); // VNPay tính theo đồng * 100
        $returnUrl = (string) ($paymentData['return_url'] ?? url('/checkout/payment/vnpay-return'));

        $inputData = [
            'vnp_Version'    => '2.1.0',
            'vnp_TmnCode'    => $tmnCode,
            'vnp_Amount'     => $amount,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => request()->ip() ?: '127.0.0.1',
            'vnp_Locale'     => $this->getConfig('locale', 'vn'),
            'vnp_OrderInfo'  => 'Thanh toan don hang #' . $code,
            'vnp_OrderType'  => 'billpayment',
            'vnp_ReturnUrl'  => $returnUrl,
            'vnp_TxnRef'     => $code . '_' . time(),
        ];

        ksort($inputData);
        $query = '';
        $i = 0;
        $hashData = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode((string) $value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode((string) $value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode((string) $value) . '&';
        }

        $vnpSecurityHash = hash_hmac('sha512', $hashData, $hashSecret);
        $redirectUrl = $vnpUrl . '?' . $query . 'vnp_SecureHash=' . $vnpSecurityHash;

        return PaymentResult::pending(
            transactionId: $inputData['vnp_TxnRef'],
            redirectUrl: $redirectUrl,
            message: __('Chuyển hướng đến cổng thanh toán VNPAY...'),
            rawData: $inputData
        );
    }

    public function handleWebhook(Request $request): PaymentResult
    {
        $hashSecret = (string) $this->getConfig('hash_secret', '');
        $vnpSecureHash = (string) $request->input('vnp_SecureHash');

        $inputData = [];
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'vnp_') && $key !== 'vnp_SecureHash') {
                $inputData[$key] = $value;
            }
        }

        ksort($inputData);
        $hashData = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode((string) $value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode((string) $value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);

        if ($secureHash !== $vnpSecureHash) {
            return PaymentResult::failed(__('Chữ ký xác thực VNPay không hợp lệ (Invalid checksum).'));
        }

        $responseCode = $request->input('vnp_ResponseCode');
        $txnRef = (string) $request->input('vnp_TxnRef');
        $transactionNo = (string) ($request->input('vnp_TransactionNo') ?: $txnRef);

        if ($responseCode === '00') {
            return PaymentResult::success(
                transactionId: $transactionNo,
                message: __('Giao dịch VNPay thành công.'),
                rawData: $request->all()
            );
        }

        return PaymentResult::failed(
            message: __('Giao dịch VNPay thất bại, mã lỗi: :code', ['code' => $responseCode]),
            rawData: $request->all()
        );
    }
}
