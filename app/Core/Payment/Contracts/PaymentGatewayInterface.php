<?php

namespace App\Core\Payment\Contracts;

use App\Core\Payment\DTOs\PaymentResult;
use App\Core\Payment\DTOs\RefundResult;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Mã định danh cổng thanh toán (ví dụ: 'cod', 'bank_transfer', 'vnpay').
     */
    public function getCode(): string;

    /**
     * Tên hiển thị mặc định của cổng.
     */
    public function getName(): string;

    /**
     * Mô tả ngắn gọn về phương thức thanh toán.
     */
    public function getDescription(): string;

    /**
     * Thiết lập cấu hình kết nối (credentials, endpoints...).
     */
    public function setConfig(array $config): static;

    /**
     * Lấy cấu hình của cổng.
     */
    public function getConfig(?string $key = null, mixed $default = null): mixed;

    /**
     * Khai báo schema các trường cấu hình để giao diện Admin tự động render form.
     * 
     * Ví dụ:
     * [
     *     'tmn_code' => [
     *         'label' => 'Mã Website (TMN Code)',
     *         'type' => 'text',
     *         'required' => true,
     *         'placeholder' => 'ABC12345',
     *     ],
     *     'hash_secret' => [
     *         'label' => 'Chuỗi bí mật (Hash Secret)',
     *         'type' => 'password',
     *         'required' => true,
     *     ],
     * ]
     */
    public function getConfigFields(): array;

    /**
     * Khởi tạo giao dịch thanh toán cho đơn hàng.
     */
    public function charge(object $order, array $paymentData = []): PaymentResult;

    /**
     * Xử lý hoàn tiền cho giao dịch.
     */
    public function refund(object $payment, float $amount): RefundResult;

    /**
     * Xử lý callback / Webhook / IPN từ đối tác cổng thanh toán.
     */
    public function handleWebhook(Request $request): PaymentResult;
}
