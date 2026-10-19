<?php

namespace Tests\Feature\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'sku'          => 'SP-TEST-001',
            'price'        => 150000,
            'is_active'    => true,
            'product_type' => 'simple',
        ]);

        $this->product->translations()->create([
            'locale' => 'vi',
            'name'   => 'Sản phẩm thử nghiệm A',
            'slug'   => 'san-pham-thu-nghiem-a',
        ]);
    }

    public function test_checkout_validation_errors(): void
    {
        $response = $this->postJson(route('api.v1.checkout'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'payment_method_code',
            'recipient_name',
            'phone',
            'address_line',
            'items',
        ]);
    }

    public function test_checkout_with_cod_success(): void
    {
        PaymentMethod::create([
            'code'      => 'cod',
            'name'      => 'Thanh toán COD',
            'is_active' => true,
        ]);

        $payload = [
            'payment_method_code' => 'cod',
            'recipient_name'      => 'Nguyễn Thị Hoa',
            'phone'               => '0909123456',
            'email'               => 'hoa@example.com',
            'address_line'        => '789 Nguyễn Huệ',
            'district'            => 'Quận 1',
            'province'            => 'TP.HCM',
            'notes'               => 'Giao trong ngày',
            'items'               => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 2,
                ],
            ],
        ];

        $response = $this->postJson(route('api.v1.checkout'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'total_amount'   => 300000,
                'currency'       => 'VND',
                'status'         => OrderStatus::PENDING->value,
                'is_redirect'    => false,
                'payment_status' => PaymentStatus::PENDING->value,
            ],
        ]);

        $order = Order::where('subtotal', 300000)->first();
        $this->assertNotNull($order);
        $this->assertDatabaseHas('shipping_addresses', [
            'order_id'       => $order->id,
            'recipient_name' => 'Nguyễn Thị Hoa',
            'phone'          => '0909123456',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'unit_price' => 150000,
            'quantity'   => 2,
            'subtotal'   => 300000,
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id'     => $order->id,
            'gateway_code' => 'cod',
            'amount'       => 300000,
            'status'       => PaymentStatus::PENDING->value,
        ]);
    }

    public function test_checkout_with_vietqr_generates_qr_url(): void
    {
        PaymentMethod::create([
            'code'      => 'bank_transfer',
            'name'      => 'Chuyển khoản VietQR',
            'is_active' => true,
            'config'    => [
                'bank_name'      => 'VCB',
                'account_number' => '0123456789',
                'account_holder' => 'CONG TY ABC',
            ],
        ]);

        $payload = [
            'payment_method_code' => 'bank_transfer',
            'recipient_name'      => 'Lê Văn C',
            'phone'               => '0933112233',
            'address_line'        => '12 Phan Chu Trinh',
            'items'               => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 1,
                ],
            ],
        ];

        $response = $this->postJson(route('api.v1.checkout'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'total_amount' => 150000,
            ],
        ]);

        $qrUrl = $response->json('data.qr_url');
        $this->assertNotNull($qrUrl);
        $this->assertStringContainsString('https://img.vietqr.io/image/VCB-0123456789-compact.png', $qrUrl);
    }

    public function test_checkout_return_callback_endpoint(): void
    {
        $response = $this->get(route('checkout.return', 'vnpay') . '?vnp_ResponseCode=00');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
