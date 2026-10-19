# Module: Payment

> ✅ **Hoàn thành** | Profile: Full | Tầng 6 — Đơn hàng & Thanh toán
> Route: `admin.payments.*` & API `api/v1/payments/*`
> Spec: [`07` §3.4](../07-development-process.md) (MODULE: Payments)

---

## 1. Hiện trạng & Cấu trúc Dữ liệu (Cập nhật 10/2026)

Hệ thống thanh toán đã được nâng cấp hoàn chỉnh kết nối với module **Orders**, **PaymentMethod (Pluggable Registry 8 drivers)**, hệ thống tiếp nhận Webhook đa cổng và luồng Checkout API cho khách hàng.

### 1.1 Cơ sở dữ liệu

- **Bảng `payments`**:
  - `id`: Khóa chính bigIncrements.
  - `uuid`: Mã định danh duy nhất (UUID v4).
  - `order_id`: Khóa ngoại liên kết bảng `orders` (onDelete cascade).
  - `payment_method_id`: Khóa ngoại liên kết bảng `payment_methods` (nullable, set null).
  - `gateway_code`: Mã cổng thanh toán (ví dụ: `vnpay`, `sepay`, `payos`, `momo`, `stripe`, `paypal`, `cod`, `bacs`).
  - `gateway_transaction_id`: Mã giao dịch trả về từ cổng thanh toán.
  - `status`: Enum string (`pending`, `processing`, `success`, `failed`, `refunded`, `cancelled`).
  - `amount`: Số tiền giao dịch (decimal 15,2).
  - `currency`: Đơn vị tiền tệ (mặc định `'VND'`).
  - `gateway_response`: Dữ liệu phản hồi gốc từ cổng thanh toán (JSON).
  - `paid_at`: Thời điểm thanh toán thành công (timestamp, nullable).
  - `timestamps`.
  - **Index**: Unique composite `(order_id, gateway_transaction_id)` để chống trùng lặp giao dịch (Idempotency).

---

## 2. Các Lớp Kiến trúc (Architecture Layers)

### 2.1 Enums
- **`app/Enums/PaymentStatus.php`**:
  - Các trạng thái: `PENDING`, `PROCESSING`, `SUCCESS`, `FAILED`, `REFUNDED`, `CANCELLED`.
  - Helper `label()` đa ngôn ngữ qua `__()`.
  - Helper `badgeClass()` chuẩn Bootstrap 5.3.

### 2.2 Model & Repository
- **`app/Models/Payment.php`**:
  - Trait: `HasFactory`, `HasUuid`.
  - Casts: `status => PaymentStatus::class`, `amount => decimal:2`, `gateway_response => array`, `paid_at => datetime`.
  - Quan hệ: `order()`, `paymentMethod()`.
  - Helper methods: `markAsSuccess(string $transactionId, ?array $response)`, `markAsFailed(?string $reason)`.
- **`app/Repositories/Interfaces/PaymentRepositoryInterface.php`**:
  - `findByGatewayTransactionId(string $transactionId): ?Payment`
  - `recordPayment(Order $order, PaymentResult $result, ?int $paymentMethodId): Payment`
- **`app/Repositories/Eloquent/PaymentRepository.php`**: Triển khai interface với cơ chế chống ghi đè dữ liệu.

### 2.3 Webhook Controller & Sự kiện (Events)
- **`app/Http/Controllers/Api/Payment/PaymentWebhookController.php`**:
  - Endpoint công khai tiếp nhận callback: `POST /api/v1/payments/webhook/{gateway}`.
  - Tìm driver cổng thanh toán qua `PaymentGatewayRegistry::get($gateway)`.
  - Nạp cấu hình bảo mật được giải mã từ `PaymentMethod` tương ứng.
  - Gọi `$driver->handleWebhook($request)` để xác thực chữ ký số (Checksum/HMAC/Webhook signature).
  - **Idempotency Protection**: Kiểm tra `gateway_transaction_id` qua `PaymentRepository`. Nếu giao dịch đã được xử lý thành công trước đó, ngay lập tức trả về HTTP 200 JSON để cổng không gửi lại.
  - Cập nhật trạng thái đơn hàng sang `PROCESSING`, ghi nhận mốc `paid_at` và thêm log vào `OrderStatusHistory`.
  - Bắn sự kiện `PaymentSucceeded` hoặc `PaymentFailed`.
- **Events**:
  - `app/Events/Payment/PaymentSucceeded.php` (chứa instance `$order`, `$payment`, `$result`).
  - `app/Events/Payment/PaymentFailed.php`.

### 2.4 Luồng Checkout (Checkout Flow)
- **`app/DTOs/Checkout/CheckoutDTO.php`**:
  - DTO định kiểu cho toàn bộ dữ liệu đặt hàng của khách hàng: `userId`, `customerName`, `customerPhone`, `customerEmail`, `shippingAddress`, `paymentMethodCode`, `items`, `couponCode`, `notes`.
  - Tự động map mảng dữ liệu với `fromArray()`.
- **`app/Services/Checkout/CheckoutService.php`**:
  - Anti-fraud: Xác thực đơn giá sản phẩm trực tiếp từ CSDL (bảng `products` / `product_variants`), không tin tưởng giá gửi lên từ frontend.
  - Kiểm tra điều kiện khả dụng của cổng thanh toán qua `$driver->isAvailableFor($amount, $currency)`.
  - Bọc toàn bộ quá trình tạo Order, OrderItems, ShippingAddress và Payment trong `DB::transaction()`.
  - Kích hoạt cổng thanh toán qua `$driver->charge($order, $paymentData)`.
  - Xử lý các luồng: Return URL khi khách thanh toán xong quay về (`handleReturn`) và Cancel URL (`handleCancel`).
- **`app/Http/Controllers/Api/CheckoutController.php`**:
  - Endpoint: `POST /api/v1/checkout`
  - Endpoint callback return: `GET /checkout/return/{gateway}`
  - Endpoint callback cancel: `GET /checkout/cancel/{gateway}`

---

## 3. Kiểm thử Tự động (Automated Tests)

1. **`tests/Feature/Api/PaymentWebhookTest.php`** (**7/7 test cases PASS**):
   - Chặn gateway không hợp lệ (404 Not Found).
   - Chặn khi phương thức thanh toán bị vô hiệu hóa (400 Bad Request).
   - Kiểm tra Webhook VNPay chuyển đơn hàng sang `PROCESSING` và ghi nhận `paid_at`.
   - Kiểm tra Webhook SePay xử lý giao dịch chuyển khoản VietQR tự động.
   - Kiểm tra Webhook Stripe và PayPal xác thực thành công.
   - Kiểm tra tính **Idempotent**: gửi webhook trùng lặp không xử lý lại 2 lần.

2. **`tests/Feature/Api/CheckoutTest.php`** (**4/4 test cases PASS**):
   - Bắt lỗi validate khi thiếu trường dữ liệu checkout.
   - Đặt hàng thành công với phương thức COD (Tiền mặt khi nhận hàng).
   - Đặt hàng thành công với VietQR (sinh mã thanh toán ngân hàng).
   - Xử lý thành công luồng Return URL sau khi khách hoàn tất trên cổng.

3. **`tests/Unit/Payment/PaymentGatewayRegistryTest.php`** (**15/15 test cases PASS**):
   - Kiểm tra toàn bộ 8 drivers: COD, Bank Transfer, VNPay, SePay, PayOS, MoMo, Stripe, PayPal.
   - Khởi tạo giao dịch độc lập offline với `Http::fake()` cho PayOS, MoMo, PayPal.
