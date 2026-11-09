<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

/**
 * Seed danh mục cổng thanh toán khớp với PaymentGatewayRegistry
 * (cod, bank_transfer, vnpay, sepay, payos, momo, stripe, paypal).
 *
 * Lý do có seeder này: dev DB đang `payment_methods = 0`, vì vậy checkout thật
 * luôn fail ở bước `assertPaymentMethodUsable` — toàn bộ luồng Order/Payment trước giờ
 * chỉ được kiểm chứng qua test dùng Http::fake().
 *
 * Chỉ những cổng KHÔNG cần credential mới được bật is_active. Cổng cần credential
 * seed ở trạng thái tắt để admin vào `Cấu hình thanh toán` điền key rồi tự bật.
 */
class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code'        => 'cod',
                'name'        => 'Thanh toán khi nhận hàng (COD)',
                'description' => 'Khách trả tiền mặt cho nhân viên giao hàng.',
                'instructions'=> 'Đơn của bạn sẽ được giao trong 2–4 ngày làm việc. Vui lòng chuẩn bị đủ số tiền khi nhận hàng.',
                'is_active'   => true,
                'supported_currencies' => ['VND'],
                'order'       => 1,
            ],
            [
                'code'        => 'bank_transfer',
                'name'        => 'Chuyển khoản ngân hàng (VietQR)',
                'description' => 'Chuyển khoản tự động qua mã QR Nhà cung cấp.',
                'instructions'=> 'Quét mã QR trong email xác nhận và ghi đúng nội dung chuyển khoản để hệ thống tự đối soát.',
                'is_active'   => true,
                'supported_currencies' => ['VND'],
                'order'       => 2,
            ],
            [
                'code'        => 'vnpay',
                'name'        => 'VNPay',
                'description' => 'Cổng thanh toán nội địa, hỗ trợ thẻ ATM và ví.',
                'is_active'   => false,
                'supported_currencies' => ['VND'],
                'order'       => 3,
            ],
            [
                'code'        => 'sepay',
                'name'        => 'SePay',
                'description' => 'Chuyển khoản ngân hàng điện tử, đối soát tự động qua webhook.',
                'is_active'   => false,
                'supported_currencies' => ['VND'],
                'order'       => 4,
            ],
            [
                'code'        => 'payos',
                'name'        => 'PayOS',
                'description' => 'Ví điện tử và QR động.',
                'is_active'   => false,
                'supported_currencies' => ['VND'],
                'order'       => 5,
            ],
            [
                'code'        => 'momo',
                'name'        => 'MoMo Wallet',
                'description' => 'Thanh toán qua ví MoMo.',
                'is_active'   => false,
                'supported_currencies' => ['VND'],
                'order'       => 6,
            ],
            [
                'code'        => 'stripe',
                'name'        => 'Stripe (thẻ quốc tế)',
                'description' => 'Visa / Mastercard qua Stripe.',
                'supported_currencies' => ['VND', 'USD'],
                'is_active'   => false,
                'order'       => 7,
            ],
            [
                'code'        => 'paypal',
                'name'        => 'PayPal',
                'description' => 'Thanh toán qua tài khoản PayPal.',
                'is_active'   => false,
                'supported_currencies' => ['VND'],
                'order'       => 8,
            ],
        ];

        foreach ($methods as $method) {
            $order = $method['order'];
            unset($method['order']);

            PaymentMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                $method + ['display_order' => $order]
            );
        }

        $this->command?->info('✅ Payment methods seeded (COD + VietQR đang bật, các cổng cần credential để tắt).');
    }
}
