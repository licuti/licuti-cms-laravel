<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\DTOs\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StripeGateway extends AbstractPaymentGateway
{
    public function getCode(): string
    {
        return 'stripe';
    }

    public function getName(): string
    {
        return __('Thẻ quốc tế Stripe (Visa / Mastercard / Apple Pay)');
    }

    public function getDescription(): string
    {
        return __('Cổng thanh toán quốc tế Stripe hỗ trợ Visa, Mastercard, JCB, Apple Pay và Google Pay.');
    }

    public function getConfigFields(): array
    {
        return [
            'publishable_key' => [
                'type'        => 'text',
                'label'       => __('Publishable Key (pk_...)'),
                'placeholder' => 'pk_live_... hoặc pk_test_...',
                'required'    => true,
            ],
            'secret_key' => [
                'type'        => 'password',
                'label'       => __('Secret Key (sk_...)'),
                'placeholder' => 'sk_live_... hoặc sk_test_...',
                'required'    => true,
            ],
            'webhook_secret' => [
                'type'        => 'password',
                'label'       => __('Webhook Signing Secret (whsec_...)'),
                'placeholder' => 'whsec_...',
                'required'    => false,
            ],
            'currency' => [
                'type'        => 'select',
                'label'       => __('Loại tiền tệ thanh toán mặc định'),
                'options'     => [
                    'usd' => 'USD - US Dollar',
                    'vnd' => 'VND - Vietnamese Dong',
                    'eur' => 'EUR - Euro',
                    'jpy' => 'JPY - Japanese Yen',
                ],
                'default'     => 'usd',
            ],
        ];
    }

    public function charge(object $order, array $paymentData = []): PaymentResult
    {
        $secretKey = (string) $this->getConfig('secret_key');
        if (empty($secretKey)) {
            return PaymentResult::failed(__('Cổng Stripe chưa được cấu hình Secret Key.'));
        }

        $code = (string) ($order->order_number ?? $order->code ?? ('ORD-' . time()));
        $rawAmount = (float) ($order->total ?? $order->grand_total ?? 0);
        $currency = strtolower((string) $this->getConfig('currency', 'usd'));

        // Stripe zero-decimal currencies: VND, JPY, KRW... không nhân 100
        $zeroDecimalCurrencies = ['vnd', 'jpy', 'krw', 'bif', 'clp', 'djf', 'gnf', 'kmf', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];
        $amountInSubunit = in_array($currency, $zeroDecimalCurrencies, true)
            ? (int) round($rawAmount)
            : (int) round($rawAmount * 100);

        $successUrl = (string) ($paymentData['return_url'] ?? url('/checkout/payment/stripe-success?session_id={CHECKOUT_SESSION_ID}'));
        $cancelUrl  = (string) ($paymentData['cancel_url'] ?? url('/checkout/payment/stripe-cancel'));

        $payload = [
            'mode'                   => 'payment',
            'client_reference_id'    => $code,
            'success_url'            => $successUrl,
            'cancel_url'             => $cancelUrl,
            'line_items[0][price_data][currency]'                     => $currency,
            'line_items[0][price_data][unit_amount]'                  => $amountInSubunit,
            'line_items[0][price_data][product_data][name]'           => 'Đơn hàng #' . $code,
            'line_items[0][quantity]'                                 => 1,
            'metadata[order_number]' => $code,
        ];

        $response = Http::asForm()
            ->withToken($secretKey)
            ->post('https://api.stripe.com/v1/checkout/sessions', $payload);

        if ($response->successful()) {
            $session = $response->json();
            return PaymentResult::pending(
                transactionId: $session['id'],
                redirectUrl: $session['url'] ?? null,
                message: __('Đang chuyển hướng tới cổng thanh toán Stripe Checkout...'),
                rawData: $session
            );
        }

        $errorMessage = $response->json('error.message') ?: __('Không thể khởi tạo phiên thanh toán Stripe.');
        return PaymentResult::failed($errorMessage, $response->json() ?: []);
    }

    public function handleWebhook(Request $request): PaymentResult
    {
        $payload = $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature');
        $webhookSecret = (string) $this->getConfig('webhook_secret');

        // Xác thực chữ ký Stripe nếu có cấu hình webhook secret
        if (!empty($webhookSecret) && !empty($sigHeader)) {
            $timestamp = null;
            $signature = null;

            foreach (explode(',', $sigHeader) as $pair) {
                $parts = explode('=', trim($pair), 2);
                if (count($parts) === 2) {
                    if ($parts[0] === 't') {
                        $timestamp = $parts[1];
                    } elseif ($parts[0] === 'v1') {
                        $signature = $parts[1];
                    }
                }
            }

            if ($timestamp && $signature) {
                $signedPayload = $timestamp . '.' . $payload;
                $expectedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

                if (!hash_equals($expectedSignature, $signature)) {
                    return PaymentResult::failed(__('Chữ ký Stripe Webhook không hợp lệ.'));
                }
            }
        }

        $event = json_decode($payload, true) ?: $request->all();
        $eventType = $event['type'] ?? '';
        $dataObject = $event['data']['object'] ?? [];

        if ($eventType === 'checkout.session.completed' || $eventType === 'payment_intent.succeeded') {
            $transactionId = (string) ($dataObject['id'] ?? $request->input('id') ?? 'STRIPE-' . time());
            return PaymentResult::success(
                transactionId: $transactionId,
                message: __('Xác nhận thanh toán Stripe thành công.'),
                rawData: $event
            );
        }

        return PaymentResult::pending(
            transactionId: (string) ($dataObject['id'] ?? ''),
            message: __('Stripe Webhook event: ' . $eventType),
            rawData: $event
        );
    }
}
