<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\DTOs\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayPalGateway extends AbstractPaymentGateway
{
    public function getCode(): string
    {
        return 'paypal';
    }

    public function getName(): string
    {
        return __('Ví PayPal Quốc Tế');
    }

    public function getDescription(): string
    {
        return __('Cổng thanh toán toàn cầu PayPal hỗ trợ số dư ví PayPal và thẻ thanh toán quốc tế.');
    }

    public function getConfigFields(): array
    {
        return [
            'client_id' => [
                'type'        => 'text',
                'label'       => __('Client ID'),
                'placeholder' => 'Nhập Client ID do PayPal cung cấp',
                'required'    => true,
            ],
            'client_secret' => [
                'type'        => 'password',
                'label'       => __('Client Secret'),
                'placeholder' => 'Nhập Secret Key do PayPal cung cấp',
                'required'    => true,
            ],
            'environment' => [
                'type'        => 'select',
                'label'       => __('Môi trường kết nối'),
                'options'     => [
                    'sandbox' => __('Thử nghiệm (Sandbox)'),
                    'live'    => __('Thực tế (Live / Production)'),
                ],
                'default'     => 'sandbox',
            ],
            'exchange_rate_usd' => [
                'type'        => 'number',
                'label'       => __('Tỷ giá quy đổi sang USD (1 USD = ? VND)'),
                'placeholder' => '25400',
                'default'     => 25400,
                'description' => __('PayPal không hỗ trợ trực tiếp đơn vị VND. Hệ thống sẽ tự động quy đổi số tiền đơn hàng sang USD khi gửi sang PayPal.'),
                'required'    => false,
            ],
        ];
    }

    protected function getBaseUrl(): string
    {
        return $this->getConfig('environment') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function getAccessToken(): ?string
    {
        $clientId = (string) $this->getConfig('client_id');
        $secret   = (string) $this->getConfig('client_secret');

        if (empty($clientId) || empty($secret)) {
            return null;
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->post($this->getBaseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        return $response->successful() ? $response->json('access_token') : null;
    }

    public function charge(object $order, array $paymentData = []): PaymentResult
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return PaymentResult::failed(__('Không thể xác thực thông tin tài khoản PayPal (Client ID / Secret không đúng).'));
        }

        $code = (string) ($order->order_number ?? $order->code ?? ('ORD-' . time()));
        $rawAmount = (float) ($order->total ?? $order->grand_total ?? 0);

        // Quy đổi VND sang USD nếu cần (vì PayPal không chấp nhận tiền VND)
        $rate = (float) $this->getConfig('exchange_rate_usd', 25400);
        $usdAmount = $rate > 0 ? round($rawAmount / $rate, 2) : round($rawAmount, 2);
        if ($usdAmount < 0.01) {
            $usdAmount = 0.01;
        }

        $returnUrl = (string) ($paymentData['return_url'] ?? url('/checkout/payment/paypal-return'));
        $cancelUrl = (string) ($paymentData['cancel_url'] ?? url('/checkout/payment/paypal-cancel'));

        $body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $code,
                    'description'  => 'Đơn hàng #' . $code,
                    'amount'       => [
                        'currency_code' => 'USD',
                        'value'         => number_format($usdAmount, 2, '.', ''),
                    ],
                ],
            ],
            'application_context' => [
                'return_url'          => $returnUrl,
                'cancel_url'          => $cancelUrl,
                'brand_name'          => config('app.name', 'Licuti Store'),
                'landing_page'        => 'BILLING',
                'user_action'         => 'PAY_NOW',
            ],
        ];

        $response = Http::withToken($accessToken)
            ->post($this->getBaseUrl() . '/v2/checkout/orders', $body);

        if ($response->successful()) {
            $data = $response->json();
            $approveLink = null;
            foreach ($data['links'] ?? [] as $link) {
                if (($link['rel'] ?? '') === 'approve') {
                    $approveLink = $link['href'];
                    break;
                }
            }

            return PaymentResult::pending(
                transactionId: $data['id'] ?? ('PP-' . $code),
                redirectUrl: $approveLink,
                message: __('Đang chuyển hướng tới cổng thanh toán PayPal...'),
                rawData: $data
            );
        }

        $errorMessage = $response->json('message') ?: __('Không thể khởi tạo giao dịch PayPal.');
        return PaymentResult::failed($errorMessage, $response->json() ?: []);
    }

    public function handleWebhook(Request $request): PaymentResult
    {
        $eventType = (string) $request->input('event_type');
        $resource = $request->input('resource', []);
        $transactionId = (string) ($resource['id'] ?? $request->input('id') ?? ('PP-' . time()));

        if ($eventType === 'CHECKOUT.ORDER.APPROVED' || $eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            return PaymentResult::success(
                transactionId: $transactionId,
                message: __('Thanh toán PayPal được ghi nhận thành công.'),
                rawData: $request->all()
            );
        }

        return PaymentResult::pending(
            transactionId: $transactionId,
            message: __('PayPal Webhook event: ' . $eventType),
            rawData: $request->all()
        );
    }
}
