<?php

namespace Tests\Feature\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_gateway_returns_404(): void
    {
        $response = $this->postJson(route('api.v1.payments.webhook', 'unsupported_gateway'));

        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    public function test_disabled_payment_method_returns_400(): void
    {
        PaymentMethod::create([
            'code'      => 'vnpay',
            'name'      => 'VNPAY',
            'is_active' => false,
        ]);

        $response = $this->postJson(route('api.v1.payments.webhook', 'vnpay'));

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_vnpay_valid_webhook_marks_order_as_processing(): void
    {
        $hashSecret = 'TEST_VNPAY_HASH_SECRET_123';
        $tmnCode = 'TEST_TMN';

        PaymentMethod::create([
            'code'      => 'vnpay',
            'name'      => 'VNPAY',
            'is_active' => true,
            'config'    => [
                'tmn_code'    => $tmnCode,
                'hash_secret' => $hashSecret,
            ],
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20261019-VNPAY1',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 500000,
            'total_amount' => 500000,
        ]);

        $vnpParams = [
            'vnp_TmnCode'        => $tmnCode,
            'vnp_Amount'         => 50000000, // 500,000 * 100
            'vnp_ResponseCode'   => '00',
            'vnp_TransactionNo'  => '9876543210',
            'vnp_TxnRef'         => $order->order_number,
            'vnp_PayDate'        => '20261019120000',
            'vnp_OrderInfo'      => 'Thanh toan don hang',
            'vnp_TransactionStatus' => '00',
        ];

        ksort($vnpParams);
        $signData = http_build_query($vnpParams);
        $vnpParams['vnp_SecureHash'] = hash_hmac('sha512', $signData, $hashSecret);

        $response = $this->postJson(route('api.v1.payments.webhook', 'vnpay'), $vnpParams);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshOrder = $order->fresh();
        $this->assertSame(OrderStatus::PROCESSING, $freshOrder->status);
        $this->assertNotNull($freshOrder->paid_at);

        $this->assertDatabaseHas('payments', [
            'order_id'               => $order->id,
            'gateway_code'           => 'vnpay',
            'gateway_transaction_id' => '9876543210',
            'status'                 => PaymentStatus::SUCCESS->value,
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id'  => $order->id,
            'to_status' => OrderStatus::PROCESSING->value,
        ]);
    }

    public function test_sepay_valid_webhook_marks_order_as_processing(): void
    {
        $apiKey = 'SECRET_SEPAY_API_KEY_123';

        PaymentMethod::create([
            'code'      => 'sepay',
            'name'      => 'SePay',
            'is_active' => true,
            'config'    => [
                'api_key' => $apiKey,
            ],
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20261019-SEPAY1',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 250000,
            'total_amount' => 250000,
        ]);

        $payload = [
            'id'            => 112233,
            'gateway'       => 'Vietcombank',
            'transactionDate' => '2026-10-19 14:00:00',
            'accountNumber' => '0123456789',
            'transferType'  => 'in',
            'transferAmount' => 250000,
            'content'       => "Chuyen khoan thanh toan don {$order->order_number}",
            'referenceCode' => 'REF9988',
        ];

        $response = $this->postJson(
            route('api.v1.payments.webhook', 'sepay'),
            $payload,
            ['Authorization' => "Apikey {$apiKey}"]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshOrder = $order->fresh();
        $this->assertSame(OrderStatus::PROCESSING, $freshOrder->status);
        $this->assertNotNull($freshOrder->paid_at);

        $this->assertDatabaseHas('payments', [
            'order_id'               => $order->id,
            'gateway_code'           => 'sepay',
            'gateway_transaction_id' => '112233',
            'status'                 => PaymentStatus::SUCCESS->value,
        ]);
    }

    public function test_webhook_is_idempotent(): void
    {
        $apiKey = 'SECRET_SEPAY_API_KEY_123';

        PaymentMethod::create([
            'code'      => 'sepay',
            'name'      => 'SePay',
            'is_active' => true,
            'config'    => ['api_key' => $apiKey],
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20261019-IDEMP1',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 100000,
            'total_amount' => 100000,
        ]);

        $payload = [
            'id'             => 778899,
            'transferAmount' => 100000,
            'content'        => "Thanh toan don {$order->order_number}",
        ];

        // Lần 1: Thành công
        $res1 = $this->postJson(
            route('api.v1.payments.webhook', 'sepay'),
            $payload,
            ['Authorization' => "Apikey {$apiKey}"]
        );
        $res1->assertStatus(200);

        // Lần 2: Cùng transaction ID bắn lại -> Phải trả về 200 mà không tạo thêm bản ghi Payment trùng
        $res2 = $this->postJson(
            route('api.v1.payments.webhook', 'sepay'),
            $payload,
            ['Authorization' => "Apikey {$apiKey}"]
        );
        $res2->assertStatus(200);

        // Kiểm tra chỉ duy nhất 1 bản ghi Payment được tạo
        $count = Payment::where('gateway_code', 'sepay')
            ->where('gateway_transaction_id', '778899')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_stripe_valid_webhook_marks_order_as_processing(): void
    {
        $webhookSecret = 'whsec_stripe_test_123';

        PaymentMethod::create([
            'code'      => 'stripe',
            'name'      => 'Stripe',
            'is_active' => true,
            'config'    => [
                'secret_key'     => 'sk_test_123',
                'webhook_secret' => $webhookSecret,
            ],
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20261019-STRIPE1',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 500000,
            'total_amount' => 500000,
        ]);

        $payloadArray = [
            'id'   => 'evt_stripe_test_001',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id'                  => 'cs_test_session_12345',
                    'client_reference_id' => $order->order_number,
                    'amount_total'        => 500000,
                    'payment_status'      => 'paid',
                ],
            ],
        ];

        $payload = json_encode($payloadArray);
        $timestamp = time();
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $webhookSecret);
        $sigHeader = "t={$timestamp},v1={$signature}";

        $response = $this->call(
            'POST',
            route('api.v1.payments.webhook', 'stripe'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE'           => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $sigHeader,
            ],
            $payload
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshOrder = $order->fresh();
        $this->assertSame(OrderStatus::PROCESSING, $freshOrder->status);
        $this->assertNotNull($freshOrder->paid_at);

        $this->assertDatabaseHas('payments', [
            'order_id'               => $order->id,
            'gateway_code'           => 'stripe',
            'gateway_transaction_id' => 'cs_test_session_12345',
            'status'                 => PaymentStatus::SUCCESS->value,
        ]);
    }

    public function test_paypal_valid_webhook_marks_order_as_processing(): void
    {
        PaymentMethod::create([
            'code'      => 'paypal',
            'name'      => 'PayPal',
            'is_active' => true,
            'config'    => [
                'client_id'     => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20261019-PP01',
            'status'       => OrderStatus::PENDING,
            'subtotal'     => 250000,
            'total_amount' => 250000,
        ]);

        $payload = [
            'id'         => 'WH-PP-123456',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource'   => [
                'id'             => 'CAPTURE-998877',
                'purchase_units' => [
                    [
                        'reference_id' => $order->order_number,
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('api.v1.payments.webhook', 'paypal'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshOrder = $order->fresh();
        $this->assertSame(OrderStatus::PROCESSING, $freshOrder->status);
        $this->assertNotNull($freshOrder->paid_at);

        $this->assertDatabaseHas('payments', [
            'order_id'               => $order->id,
            'gateway_code'           => 'paypal',
            'gateway_transaction_id' => 'CAPTURE-998877',
            'status'                 => PaymentStatus::SUCCESS->value,
        ]);
    }
}

