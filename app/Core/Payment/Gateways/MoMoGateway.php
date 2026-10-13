<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\DTOs\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MoMoGateway extends AbstractPaymentGateway
{
    public function getCode(): string
    {
        return 'momo';
    }

    public function getName(): string
    {
        return __('Ví điện tử MoMo (App & QR MoMo)');
    }

    public function getDescription(): string
    {
        return __('Cổng thanh toán MoMo hỗ trợ quét mã QR, thanh toán qua ứng dụng MoMo trên điện thoại.');
    }

    public function getConfigFields(): array
    {
        return [
            'partner_code' => [
                'type'        => 'text',
                'label'       => __('Mã Đối tác (Partner Code)'),
                'placeholder' => 'MOMOBKUN20180529',
                'required'    => true,
            ],
            'access_key' => [
                'type'        => 'text',
                'label'       => __('Access Key'),
                'placeholder' => __('Nhập Access Key do MoMo cấp'),
                'required'    => true,
            ],
            'secret_key' => [
                'type'        => 'password',
                'label'       => __('Secret Key (Khóa bí mật tạo chữ ký)'),
                'placeholder' => __('Nhập Secret Key do MoMo cấp'),
                'required'    => true,
            ],
            'environment' => [
                'type'        => 'select',
                'label'       => __('Môi trường hoạt động'),
                'options'     => [
                    'sandbox'    => __('Thử nghiệm (Sandbox)'),
                    'production' => __('Thực tế (Production)'),
                ],
                'default'     => 'sandbox',
            ],
        ];
    }

    public function charge(object $order, array $paymentData = []): PaymentResult
    {
        $partnerCode = (string) $this->getConfig('partner_code');
        $accessKey   = (string) $this->getConfig('access_key');
        $secretKey   = (string) $this->getConfig('secret_key');

        if (empty($partnerCode) || empty($accessKey) || empty($secretKey)) {
            return PaymentResult::failed(__('Cổng MoMo chưa được cấu hình đầy đủ Partner Code, Access Key hoặc Secret Key.'));
        }

        $endpoint = $this->getConfig('environment') === 'production'
            ? 'https://payment.momo.vn/v2/gateway/api/create'
            : 'https://test-payment.momo.vn/v2/gateway/api/create';

        $orderCode = (string) ($order->order_number ?? $order->code ?? ('ORD' . time()));
        $orderId = $orderCode . '_' . time();
        $requestId = time() . '_' . rand(100, 999);
        $amount = (string) round($order->total ?? $order->grand_total ?? 0);
        $orderInfo = 'Thanh toán đơn hàng #' . $orderCode;
        $redirectUrl = (string) ($paymentData['return_url'] ?? url('/checkout/payment/momo-return'));
        $ipnUrl = (string) ($paymentData['ipn_url'] ?? url('/api/v1/payments/webhook/momo'));

        // Chuỗi hash HMAC-SHA256 chuẩn MoMo All-In-One (AIO)
        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData=&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType=captureWallet";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $response = Http::post($endpoint, [
            'partnerCode' => $partnerCode,
            'partnerName' => config('app.name', 'Licuti CMS'),
            'storeId'     => 'LicutiStore',
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => '',
            'requestType' => 'captureWallet',
            'signature'   => $signature,
        ]);

        if ($response->successful() && $response->json('resultCode') === 0) {
            return PaymentResult::pending(
                transactionId: $orderId,
                redirectUrl: $response->json('payUrl'),
                message: __('Đang chuyển hướng tới cổng thanh toán MoMo...'),
                rawData: $response->json()
            );
        }

        $message = $response->json('message') ?: __('Không thể khởi tạo giao dịch qua cổng MoMo.');
        return PaymentResult::failed($message, $response->json() ?: []);
    }

    public function handleWebhook(Request $request): PaymentResult
    {
        $secretKey = (string) $this->getConfig('secret_key');
        $signature = (string) $request->input('signature');

        if (!empty($secretKey) && !empty($signature)) {
            $rawHash = "accessKey={$request->input('accessKey')}&amount={$request->input('amount')}&extraData={$request->input('extraData')}&message={$request->input('message')}&orderId={$request->input('orderId')}&orderInfo={$request->input('orderInfo')}&orderType={$request->input('orderType')}&partnerCode={$request->input('partnerCode')}&payType={$request->input('payType')}&requestId={$request->input('requestId')}&responseTime={$request->input('responseTime')}&resultCode={$request->input('resultCode')}&transId={$request->input('transId')}";
            $calcSignature = hash_hmac('sha256', $rawHash, $secretKey);

            if (!hash_equals($calcSignature, $signature)) {
                return PaymentResult::failed(__('Chữ ký xác thực Webhook MoMo không hợp lệ.'));
            }
        }

        if ((int) $request->input('resultCode') === 0) {
            return PaymentResult::success(
                transactionId: (string) ($request->input('transId') ?: $request->input('orderId')),
                message: __('Giao dịch MoMo thanh toán thành công.'),
                rawData: $request->all()
            );
        }

        return PaymentResult::failed(
            message: __('Giao dịch MoMo thất bại hoặc bị hủy (Mã lỗi: :code)', ['code' => $request->input('resultCode')]),
            rawData: $request->all()
        );
    }
}
