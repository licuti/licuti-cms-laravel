<?php

namespace Tests\Unit\Payment;

use App\Core\Payment\Contracts\PaymentGatewayInterface;
use App\Core\Payment\DTOs\PaymentResult;
use App\Core\Payment\Gateways\AbstractPaymentGateway;
use App\Core\Payment\Gateways\BankTransferGateway;
use App\Core\Payment\Gateways\CODGateway;
use App\Core\Payment\Gateways\VNPayGateway;
use App\Core\Payment\PaymentGatewayRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewayRegistryTest extends TestCase
{
    use RefreshDatabase;

    private PaymentGatewayRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = app(PaymentGatewayRegistry::class);
    }

    public function test_default_gateways_are_registered(): void
    {
        $this->assertTrue($this->registry->has('cod'));
        $this->assertTrue($this->registry->has('bank_transfer'));
        $this->assertTrue($this->registry->has('vnpay'));
        $this->assertTrue($this->registry->has('stripe'));
        $this->assertTrue($this->registry->has('paypal'));
        $this->assertTrue($this->registry->has('sepay'));
        $this->assertTrue($this->registry->has('payos'));
        $this->assertTrue($this->registry->has('momo'));

        $this->assertInstanceOf(CODGateway::class, $this->registry->get('cod'));
        $this->assertInstanceOf(BankTransferGateway::class, $this->registry->get('bank_transfer'));
        $this->assertInstanceOf(VNPayGateway::class, $this->registry->get('vnpay'));
        $this->assertInstanceOf(\App\Core\Payment\Gateways\StripeGateway::class, $this->registry->get('stripe'));
        $this->assertInstanceOf(\App\Core\Payment\Gateways\PayPalGateway::class, $this->registry->get('paypal'));
        $this->assertInstanceOf(\App\Core\Payment\Gateways\SePayGateway::class, $this->registry->get('sepay'));
        $this->assertInstanceOf(\App\Core\Payment\Gateways\PayOSGateway::class, $this->registry->get('payos'));
        $this->assertInstanceOf(\App\Core\Payment\Gateways\MoMoGateway::class, $this->registry->get('momo'));
    }

    public function test_can_register_custom_gateway(): void
    {
        $customGateway = new class extends AbstractPaymentGateway {
            public function getCode(): string { return 'mock_custom'; }
            public function getName(): string { return 'Custom Gateway'; }
            public function charge(object $order, array $paymentData = []): PaymentResult {
                return PaymentResult::success('REF123', 'Custom Success');
            }
        };

        $this->registry->register('mock_custom', $customGateway);

        $this->assertTrue($this->registry->has('mock_custom'));
        $resolved = $this->registry->get('mock_custom');
        $this->assertInstanceOf(PaymentGatewayInterface::class, $resolved);
        $this->assertSame('mock_custom', $resolved->getCode());
        $this->assertSame('Custom Gateway', $resolved->getName());
    }

    public function test_get_options_returns_registered_gateways(): void
    {
        $options = $this->registry->getOptions();

        $this->assertArrayHasKey('cod', $options);
        $this->assertArrayHasKey('bank_transfer', $options);
        $this->assertArrayHasKey('vnpay', $options);
    }

    public function test_get_all_config_schemas_contains_configuration_fields(): void
    {
        $schemas = $this->registry->getAllConfigSchemas();

        $this->assertArrayHasKey('cod', $schemas);
        $this->assertArrayHasKey('bank_transfer', $schemas);
        $this->assertArrayHasKey('vnpay', $schemas);
        $this->assertArrayHasKey('stripe', $schemas);
        $this->assertArrayHasKey('paypal', $schemas);
        $this->assertArrayHasKey('sepay', $schemas);
        $this->assertArrayHasKey('payos', $schemas);
        $this->assertArrayHasKey('momo', $schemas);

        // SePay schema check
        $sepayFields = array_keys($schemas['sepay']['fields']);
        $this->assertContains('api_key', $sepayFields);
        $this->assertContains('bank_account', $sepayFields);

        // PayOS schema check
        $payosFields = array_keys($schemas['payos']['fields']);
        $this->assertContains('client_id', $payosFields);
        $this->assertContains('api_key', $payosFields);
        $this->assertContains('checksum_key', $payosFields);

        // MoMo schema check
        $momoFields = array_keys($schemas['momo']['fields']);
        $this->assertContains('partner_code', $momoFields);
        $this->assertContains('secret_key', $momoFields);

        // Stripe schema check
        $stripeFields = array_keys($schemas['stripe']['fields']);
        $this->assertContains('publishable_key', $stripeFields);
        $this->assertContains('secret_key', $stripeFields);

        // PayPal schema check
        $paypalFields = array_keys($schemas['paypal']['fields']);
        $this->assertContains('client_id', $paypalFields);
        $this->assertContains('client_secret', $paypalFields);

        // Bank transfer schema check
        $bankFields = array_keys($schemas['bank_transfer']['fields']);
        $this->assertContains('bank_name', $bankFields);
        $this->assertContains('account_number', $bankFields);
        $this->assertContains('account_holder', $bankFields);

        // VNPay schema check
        $vnpayFields = array_keys($schemas['vnpay']['fields']);
        $this->assertContains('tmn_code', $vnpayFields);
        $this->assertContains('hash_secret', $vnpayFields);
        $this->assertContains('url', $vnpayFields);
    }

    public function test_cod_gateway_process_payment(): void
    {
        $order = (object)[
            'order_number' => 'ORD-COD-1',
            'total' => 500000,
        ];

        $gateway = $this->registry->get('cod');
        $result = $gateway->charge($order);

        $this->assertTrue($result->isSuccessful());
        $this->assertSame('pending', $result->status);
        $this->assertFalse($result->isRedirect());
    }

    public function test_bank_transfer_gateway_generates_vietqr(): void
    {
        $order = (object)[
            'order_number' => 'ORD-123456',
            'total' => 250000,
        ];

        $gateway = $this->registry->get('bank_transfer');
        $gateway->setConfig([
            'bank_name' => 'VCB',
            'account_number' => '987654321',
            'account_holder' => 'NGUYEN VAN A',
        ]);

        $result = $gateway->charge($order);

        $this->assertTrue($result->isSuccessful());
        $this->assertSame('pending', $result->status);
        $this->assertArrayHasKey('qr_url', $result->rawData);
        $this->assertStringContainsString('https://img.vietqr.io/image/VCB-987654321-compact.png', $result->rawData['qr_url']);
    }

    public function test_vnpay_gateway_generates_signed_payment_url_and_verifies_webhook(): void
    {
        $order = (object)[
            'order_number' => 'ORD-VNPAY-1',
            'total' => 100000,
        ];

        $gateway = $this->registry->get('vnpay');
        $gateway->setConfig([
            'tmn_code' => 'DEMOTMN01',
            'hash_secret' => 'DEMOSECRETKEY1234567890',
            'url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
        ]);

        $result = $gateway->charge($order, [
            'return_url' => 'http://localhost/checkout/vnpay/callback',
        ]);

        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($result->isRedirect());
        $this->assertNotNull($result->redirectUrl);
        $this->assertStringContainsString('vnp_TmnCode=DEMOTMN01', $result->redirectUrl);
        $this->assertStringContainsString('vnp_SecureHash=', $result->redirectUrl);

        // Test webhook verification
        $hashSecret = 'DEMOSECRETKEY1234567890';
        $params = [
            'vnp_Amount' => '10000000',
            'vnp_BankCode' => 'NCB',
            'vnp_CardType' => 'ATM',
            'vnp_OrderInfo' => 'Thanh toan don hang #ORD-VNPAY-1',
            'vnp_PayDate' => '20261012120000',
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => 'DEMOTMN01',
            'vnp_TransactionNo' => '14123456',
            'vnp_TxnRef' => 'ORD-VNPAY-1_123',
        ];
        ksort($params);
        $hashData = '';
        $i = 0;
        foreach ($params as $k => $v) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($k) . '=' . urlencode((string)$v);
            } else {
                $hashData .= urlencode($k) . '=' . urlencode((string)$v);
                $i = 1;
            }
        }
        $params['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $hashSecret);

        $request = Request::create('/payment/vnpay/callback', 'GET', $params);
        $webhookResult = $gateway->handleWebhook($request);

        $this->assertTrue($webhookResult->isSuccess());
        $this->assertSame('14123456', $webhookResult->transactionId);
    }

    public function test_stripe_gateway_verifies_webhook(): void
    {
        $gateway = $this->registry->get('stripe');
        $webhookSecret = 'whsec_test_secret_123';
        $gateway->setConfig([
            'secret_key'     => 'sk_test_12345',
            'webhook_secret' => $webhookSecret,
        ]);

        $payload = json_encode([
            'id' => 'evt_12345',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_session_999',
                    'amount_total' => 5000,
                ],
            ],
        ]);

        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $webhookSecret);
        $sigHeader = "t={$timestamp},v1={$signature}";

        $request = Request::create('/payment/stripe/webhook', 'POST', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sigHeader,
        ], $payload);

        $result = $gateway->handleWebhook($request);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('cs_test_session_999', $result->transactionId);
    }

    public function test_paypal_gateway_webhook_success(): void
    {
        $gateway = $this->registry->get('paypal');
        $request = Request::create('/payment/paypal/webhook', 'POST', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE-PP-888',
                'status' => 'COMPLETED',
            ],
        ]);

        $result = $gateway->handleWebhook($request);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('CAPTURE-PP-888', $result->transactionId);
    }

    public function test_sepay_gateway_charge_and_webhook(): void
    {
        $gateway = $this->registry->get('sepay');
        $gateway->setConfig([
            'api_key'      => 'SECRET_SEPAY_API_KEY',
            'bank_brand'   => 'MB',
            'bank_account' => '0987654321',
            'acc_name'     => 'NGUYEN VAN A',
        ]);

        $order = (object)[
            'order_number' => 'ORD-SEPAY-01',
            'total' => 150000,
        ];

        $chargeResult = $gateway->charge($order);
        $this->assertTrue($chargeResult->isSuccessful());
        $this->assertArrayHasKey('qr_url', $chargeResult->rawData);
        $this->assertStringContainsString('https://qr.sepay.vn/img', $chargeResult->rawData['qr_url']);

        // Webhook verification
        $request = Request::create('/payment/sepay/webhook', 'POST', [
            'id' => 999123,
            'gateway' => 'MB',
            'amountIn' => 150000,
            'content' => 'ORD-SEPAY-01',
        ], [], [], [
            'HTTP_AUTHORIZATION' => 'Apikey SECRET_SEPAY_API_KEY',
        ]);

        $webhookResult = $gateway->handleWebhook($request);
        $this->assertTrue($webhookResult->isSuccess());
        $this->assertSame('999123', $webhookResult->transactionId);
    }

    public function test_payos_gateway_webhook_signature(): void
    {
        $gateway = $this->registry->get('payos');
        $checksumKey = 'CHECKSUM_SECRET_KEY_123';
        $gateway->setConfig([
            'checksum_key' => $checksumKey,
        ]);

        $data = [
            'amount' => 50000,
            'description' => 'DH ORD-1',
            'orderCode' => 123456,
            'paymentLinkId' => 'PLINK-99',
        ];

        ksort($data);
        $signData = [];
        foreach ($data as $k => $v) {
            $signData[] = "{$k}={$v}";
        }
        $signature = hash_hmac('sha256', implode('&', $signData), $checksumKey);

        $request = Request::create('/payment/payos/webhook', 'POST', [
            'data' => $data,
            'signature' => $signature,
        ]);

        $result = $gateway->handleWebhook($request);
        $this->assertTrue($result->isSuccess());
        $this->assertSame('PLINK-99', $result->transactionId);
    }

    public function test_momo_gateway_webhook_signature(): void
    {
        $gateway = $this->registry->get('momo');
        $secretKey = 'MOMO_SECRET_KEY_XYZ';
        $gateway->setConfig([
            'secret_key' => $secretKey,
        ]);

        $params = [
            'partnerCode' => 'MOMOBKUN20180529',
            'orderId' => 'MM_ORD_123',
            'requestId' => 'REQ_123',
            'amount' => 100000,
            'orderInfo' => 'Thanh toan don hang #ORD',
            'orderType' => 'momo_wallet',
            'transId' => '2345678901',
            'resultCode' => 0,
            'message' => 'Successful.',
            'payType' => 'qr',
            'responseTime' => 1670000000000,
            'extraData' => '',
            'accessKey' => 'ACCESS_KEY_123',
        ];

        $rawHash = "accessKey={$params['accessKey']}&amount={$params['amount']}&extraData={$params['extraData']}&message={$params['message']}&orderId={$params['orderId']}&orderInfo={$params['orderInfo']}&orderType={$params['orderType']}&partnerCode={$params['partnerCode']}&payType={$params['payType']}&requestId={$params['requestId']}&responseTime={$params['responseTime']}&resultCode={$params['resultCode']}&transId={$params['transId']}";
        $params['signature'] = hash_hmac('sha256', $rawHash, $secretKey);

        $request = Request::create('/payment/momo/webhook', 'POST', $params);
        $result = $gateway->handleWebhook($request);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('2345678901', $result->transactionId);
    }

    public function test_payos_gateway_charge_with_http_fake(): void
    {
        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests' => Http::response([
                'code' => '00',
                'desc' => 'Success',
                'data' => [
                    'paymentLinkId' => 'PLINK_FAKE_123',
                    'checkoutUrl'   => 'https://pay.payos.vn/web/test12345',
                ],
            ], 200),
        ]);

        $gateway = $this->registry->get('payos');
        $gateway->setConfig([
            'client_id'    => 'CLIENT_ID_123',
            'api_key'      => 'API_KEY_123',
            'checksum_key' => 'CHECKSUM_123',
        ]);

        $order = (object)[
            'order_number' => 'ORD-20261019-001',
            'total'        => 100000,
        ];

        $result = $gateway->charge($order);

        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($result->isRedirect());
        $this->assertSame('PLINK_FAKE_123', $result->transactionId);
        $this->assertSame('https://pay.payos.vn/web/test12345', $result->redirectUrl);
    }

    public function test_momo_gateway_charge_with_http_fake(): void
    {
        Http::fake([
            'https://test-payment.momo.vn/v2/gateway/api/create' => Http::response([
                'resultCode' => 0,
                'message'    => 'Success',
                'orderId'    => 'MOMO_ORD_99',
                'payUrl'     => 'https://payment.momo.vn/v2/gateway/pay/test123',
            ], 200),
        ]);

        $gateway = $this->registry->get('momo');
        $gateway->setConfig([
            'partner_code' => 'MOMOBKUN20180529',
            'access_key'   => 'ACCESS_KEY_123',
            'secret_key'   => 'SECRET_KEY_123',
            'environment'  => 'sandbox',
        ]);

        $order = (object)[
            'order_number' => 'ORD-MOMO-001',
            'total'        => 50000,
        ];

        $result = $gateway->charge($order);

        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($result->isRedirect());
        $this->assertSame('https://payment.momo.vn/v2/gateway/pay/test123', $result->redirectUrl);
    }

    public function test_paypal_gateway_charge_with_http_fake(): void
    {
        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'FAKE_ACCESS_TOKEN_123',
                'token_type'   => 'Bearer',
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id'     => 'PAYPAL_ORDER_789',
                'status' => 'CREATED',
                'links'  => [
                    [
                        'rel'  => 'approve',
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL_ORDER_789',
                    ],
                ],
            ], 201),
        ]);

        $gateway = $this->registry->get('paypal');
        $gateway->setConfig([
            'client_id'         => 'PAYPAL_CLIENT_ID',
            'client_secret'     => 'PAYPAL_SECRET',
            'environment'       => 'sandbox',
            'exchange_rate_usd' => 25000,
        ]);

        $order = (object)[
            'order_number' => 'ORD-PAYPAL-001',
            'total'        => 250000, // 10 USD
        ];

        $result = $gateway->charge($order);

        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($result->isRedirect());
        $this->assertSame('PAYPAL_ORDER_789', $result->transactionId);
        $this->assertSame('https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL_ORDER_789', $result->redirectUrl);
    }
}


