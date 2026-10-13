<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\DTOs\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayOSGateway extends AbstractPaymentGateway
{
    public function getCode(): string
    {
        return 'payos';
    }

    public function getName(): string
    {
        return __('Cổng thanh toán PayOS (Napas247 VietQR)');
    }

    public function getDescription(): string
    {
        return __('Cổng thanh toán QR Code liên ngân hàng Napas247, tự động khớp lệnh và xác nhận đơn hàng qua Webhook.');
    }

    public function getConfigFields(): array
    {
        return [
            'client_id' => [
                'type'        => 'text',
                'label'       => __('Client ID'),
                'placeholder' => __('Nhập Client ID do PayOS cung cấp'),
                'required'    => true,
            ],
            'api_key' => [
                'type'        => 'password',
                'label'       => __('API Key'),
                'placeholder' => __('Nhập API Key do PayOS cung cấp'),
                'required'    => true,
            ],
            'checksum_key' => [
                'type'        => 'password',
                'label'       => __('Checksum Key'),
                'placeholder' => __('Khóa bảo mật tạo và kiểm tra chữ ký Webhook'),
                'required'    => true,
            ],
        ];
    }

    public function charge(object $order, array $paymentData = []): PaymentResult
    {
        $clientId = (string) $this->getConfig('client_id');
        $apiKey = (string) $this->getConfig('api_key');
        $checksumKey = (string) $this->getConfig('checksum_key');

        if (empty($clientId) || empty($apiKey) || empty($checksumKey)) {
            return PaymentResult::failed(__('Cổng PayOS chưa được cấu hình đầy đủ Client ID, API Key hoặc Checksum Key.'));
        }

        // PayOS orderCode bắt buộc là số nguyên dương (tối đa 9007199254740991)
        $cleanNumber = preg_replace('/\D/', '', (string) ($order->order_number ?? $order->code ?? ''));
        $orderCode = !empty($cleanNumber) ? (int) substr($cleanNumber, -9) : (int) (time() % 1000000000);
        $amount = (int) round($order->total ?? $order->grand_total ?? 0);
        $description = 'DH ' . substr((string) ($order->order_number ?? $orderCode), -15);

        $returnUrl = (string) ($paymentData['return_url'] ?? url('/checkout/payment/payos-return'));
        $cancelUrl = (string) ($paymentData['cancel_url'] ?? url('/checkout/payment/payos-cancel'));

        $body = [
            'orderCode'   => $orderCode,
            'amount'      => $amount,
            'description' => $description,
            'returnUrl'   => $returnUrl,
            'cancelUrl'   => $cancelUrl,
        ];

        // Sắp xếp theo alphabet và tạo chữ ký HMAC-SHA256 theo tài liệu PayOS
        $signData = "amount={$body['amount']}&cancelUrl={$body['cancelUrl']}&description={$body['description']}&orderCode={$body['orderCode']}&returnUrl={$body['returnUrl']}";
        $body['signature'] = hash_hmac('sha256', $signData, $checksumKey);

        $response = Http::withHeaders([
            'x-client-id' => $clientId,
            'x-api-key'   => $apiKey,
        ])->post('https://api-merchant.payos.vn/v2/payment-requests', $body);

        if ($response->successful() && $response->json('code') === '00') {
            $data = $response->json('data') ?: [];
            return PaymentResult::pending(
                transactionId: (string) ($data['paymentLinkId'] ?? $orderCode),
                redirectUrl: $data['checkoutUrl'] ?? null,
                message: __('Chuyển hướng đến cổng thanh toán PayOS...'),
                rawData: $data
            );
        }

        $errorMessage = $response->json('desc') ?: __('Không thể tạo link thanh toán PayOS.');
        return PaymentResult::failed($errorMessage, $response->json() ?: []);
    }

    public function handleWebhook(Request $request): PaymentResult
    {
        $checksumKey = (string) $this->getConfig('checksum_key');
        $data = $request->input('data', []);
        $signature = (string) $request->input('signature');

        if (!empty($checksumKey) && !empty($signature) && is_array($data)) {
            ksort($data);
            $signData = [];
            foreach ($data as $k => $v) {
                if ($v !== null && $v !== '') {
                    $signData[] = "{$k}={$v}";
                }
            }
            $calculatedSignature = hash_hmac('sha256', implode('&', $signData), $checksumKey);

            if (!hash_equals($calculatedSignature, $signature)) {
                return PaymentResult::failed(__('Chữ ký xác thực Webhook PayOS không hợp lệ.'));
            }
        }

        $orderCode = (string) ($data['orderCode'] ?? $request->input('orderCode') ?? '');
        $paymentLinkId = (string) ($data['paymentLinkId'] ?? $request->input('paymentLinkId') ?? $orderCode);

        return PaymentResult::success(
            transactionId: $paymentLinkId,
            message: __('Thanh toán PayOS thành công.'),
            rawData: $request->all()
        );
    }
}
