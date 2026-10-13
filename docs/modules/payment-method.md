# Module: PaymentMethod

> ✅ **Hoàn thành** | Profile: **Full Enterprise & Pluggable Gateway** | Tầng 6 — Đơn hàng & Thanh toán
> Route: `admin.payment-methods.*` | Views: `resources/views/admin/payment-methods/`
> Test: `tests/Feature/Admin/PaymentMethodCrudTest.php` (13/13 PASS) & `tests/Unit/Payment/PaymentGatewayRegistryTest.php` (12/12 PASS)

---

## 1. Tổng quan Kiến trúc

Module **PaymentMethod** đóng vai trò là nền tảng quản trị các phương thức thanh toán trong hệ thống Licuti CMS. Module được thiết kế theo mô hình **Pluggable Gateway Registry Architecture** tuân thủ nguyên tắc Open/Closed (OCP):
- Thêm mới bất kỳ cổng thanh toán nào chỉ cần tạo 1 file Gateway Driver và đăng ký qua 1 dòng lệnh tại ServiceProvider.
- Cấu hình API keys, Merchant Secret được mã hóa tự động **AES-256** trước khi ghi vào Database.
- Danh mục phương thức thanh toán đang kích hoạt được cache tự động (`CacheKey::PAYMENT_METHODS_ACTIVE`).
- Form cấu hình Admin tự động render các ô nhập liệu trực quan theo Schema do từng Gateway Driver khai báo (`getConfigFields()`).

---

## 2. Database Schema (`payment_methods`)

| Cột | Kiểu | Mô tả |
|---|---|---|
| `id` | `unsignedBigInteger` | Khóa chính tự tăng |
| `uuid` | `uuid` | Định danh an toàn trên route / URL (`HasUuid`) |
| `code` | `varchar(30)` | Mã cổng định danh (duy nhất: `cod`, `vnpay`, `sepay`, `payos`, `momo`, `stripe`, `paypal`...) |
| `name` | `varchar(255)` | Tên hiển thị của phương thức thanh toán |
| `description` | `varchar(255)` | Mô tả ngắn gọn cho khách hàng |
| `instructions` | `text` (nullable) | Hướng dẫn thanh toán chi tiết (STK ngân hàng, lưu ý khi nhận hàng...) |
| `is_active` | `boolean` (default: 1) | Bật / tắt phương thức |
| `display_order` | `unsignedInteger` (default: 0) | Thứ tự ưu tiên hiển thị khi Checkout |
| `min_order_amount` | `decimal(15,2)` (nullable) | Giá trị đơn hàng tối thiểu để áp dụng phương thức |
| `max_order_amount` | `decimal(15,2)` (nullable) | Giá trị đơn hàng tối đa để áp dụng phương thức |
| `supported_currencies` | `json` (nullable) | Danh sách mã tiền tệ hỗ trợ (ví dụ: `["VND"]`, `["USD"]`) |
| `config` | `longText` (nullable) | Thông số kết nối API, Merchant Key được **mã hóa AES-256** |
| `created_at` / `updated_at` | `timestamps` | Thời gian tạo và cập nhật |

**Indexes**:
- Unique: `uuid`, `code`.
- Composite Index: `pm_active_order_idx` trên `(is_active, display_order)`.

---

## 3. Các Lớp Thành phần (Layering)

```
Controller (PaymentMethodController)
    ↓
Service (PaymentMethodService)
    ↓
Repository (PaymentMethodRepository)
    ↓
Model (PaymentMethod)
```

- **Model [`app/Models/PaymentMethod.php`](file:///c:/laragon/www/licuti-cms-laravel/app/Models/PaymentMethod.php)**:
  - Trait: `HasFactory`, `HasUuid`.
  - Mutator `setConfigAttribute`: Tự động mã hóa AES-256 qua `Crypt::encryptString`.
  - Accessor `getConfigAttribute`: Tự động giải mã AES-256 qua `Crypt::decryptString`, có fallback đọc an toàn dữ liệu JSON plaintext cũ.
  - Scopes: `scopeActive`, `scopeOrdered`.
  - Helper: `isAvailableFor(?float $amount, ?string $currency): bool`.
- **Repository [`app/Repositories/Eloquent/PaymentMethodRepository.php`](file:///c:/laragon/www/licuti-cms-laravel/app/Repositories/Eloquent/PaymentMethodRepository.php)**:
  - `getActiveMethods()`: Cache danh sách phương thức (tự động nhận diện Cache Tags hoặc fallback remember).
  - `clearCache()`: Xóa cache khi cập nhật dữ liệu.
  - `reorder(array $orderedIds)`: Cập nhật thứ tự sắp xếp theo danh sách ID.
- **Service [`app/Services/Admin/PaymentMethod/PaymentMethodService.php`](file:///c:/laragon/www/licuti-cms-laravel/app/Services/Admin/PaymentMethod/PaymentMethodService.php)**:
  - Điều phối CRUD, toggle trạng thái, bulk actions, tự động xóa cache.
- **FormRequests**:
  - `StorePaymentMethodRequest`, `UpdatePaymentMethodRequest`: Validate đầy đủ các trường mở rộng và cấu hình.

---

## 4. Hệ thống Pluggable Gateway Drivers

Quản lý tập trung qua **`PaymentGatewayRegistry`** và khởi tạo tại **`PaymentServiceProvider`**.

### Danh sách 8 Cổng Đã Tích hợp Sẵn

1. **`CODGateway` (`cod`)**: Thanh toán tiền mặt khi nhận hàng (tùy chọn đồng kiểm, ghi chú shipper).
2. **`BankTransferGateway` (`bank_transfer`)**: Chuyển khoản ngân hàng thủ công (tự sinh mã VietQR chuẩn Napas).
3. **`VNPayGateway` (`vnpay`)**: Cổng thanh toán VNPAY (QR / ATM / Visa, ký chữ ký HMAC-SHA512).
4. **`SePayGateway` (`sepay`)**: Chuyển khoản ngân hàng tự động bắt biến động số dư VietQR qua SePay Webhook.
5. **`PayOSGateway` (`payos`)**: Cổng thanh toán thông minh Napas247 VietQR qua PayOS Open API (chữ ký HMAC-SHA256).
6. **`MoMoGateway` (`momo`)**: Ví điện tử MoMo All-In-One (QR & App Redirect, chữ ký HMAC-SHA256).
7. **`StripeGateway` (`stripe`)**: Cổng thanh toán quốc tế Stripe Checkout Session (Visa, Mastercard, Apple Pay, tự động xử lý zero-decimal currency).
8. **`PayPalGateway` (`paypal`)**: Ví điện tử quốc tế PayPal Checkout v2 Orders API (tự động quy đổi tỷ giá VND sang USD).

---

## 5. Giao diện Quản trị Admin

- **Trang danh sách (`resources/views/admin/payment-methods/index.blade.php`)**:
  - Hiển thị: Mã, Tên, Trạng thái, Thứ tự (`display_order`), Giới hạn số tiền (`min_order_amount` / `max_order_amount`), Badge bảo mật AES-256.
  - Thao tác hàng loạt: Kích hoạt, Vô hiệu hóa, Xóa hàng loạt qua `BulkActionRegistry`.
- **Trang biểu mẫu (`resources/views/admin/payment-methods/form.blade.php`)**:
  - Layout 2 cột chuẩn thiết kế CMS Licuti (Bootstrap 5.3 + SCSS).
  - Cột chính: Thông tin cơ bản + Panel Schema-Driven Form tự động đổi theo mã cổng được chọn + Toggle JSON nâng cao.
  - Cột phụ: Box Xuất bản (Publish Box) + Box Giới hạn đơn hàng (Min/Max amount) + Box Hướng dẫn khách hàng (`instructions`).

---

## 6. Kiểm thử Tự động (Testing)

- `tests/Feature/Admin/PaymentMethodCrudTest.php`: **13/13 PASS**
  - Phân quyền & Route access.
  - CRUD & Save-and-edit.
  - Toggle trạng thái & Bulk actions.
  - Kiểm tra lưu mã hóa AES-256 trong MySQL raw DB (không chứa chuỗi plaintext).
  - Đọc tương thích ngược cấu hình plaintext cũ.
  - Kiểm tra điều kiện khả dụng `isAvailableFor()`.
  - Reorder thứ tự hiển thị.
  - Render form có đầy đủ Gateway schemas.
- `tests/Unit/Payment/PaymentGatewayRegistryTest.php`: **12/12 PASS**
  - Đăng ký và nạp instance cả 8 cổng.
  - Đăng ký custom driver.
  - Trích xuất schemas cho Admin UI.
  - Khởi tạo giao dịch & xác thực chữ ký Webhook (VNPay, Stripe, PayPal, SePay, PayOS, MoMo).

---

## 7. Kế hoạch tiếp theo (Roadmap & Next Steps)

- [ ] 🏗️ **Module Orders**: Xây dựng migration và schema hoàn chỉnh cho `orders`, `order_items`, `order_status_histories`.
- [ ] 🔗 **Webhook Controller**: Xây dựng route và controller tiếp nhận callback Webhook từ các cổng (`SePay`, `PayOS`, `MoMo`, `Stripe`, `PayPal`), xác thực chữ ký và cập nhật trạng thái đơn hàng.
- [ ] 🛒 **Checkout Flow**: Tích hợp `PaymentGatewayRegistry` trực tiếp vào luồng thanh toán đặt hàng (`CheckoutService` / `OrderService`).
- [ ] 🧪 **Fix Unit Tests**: Bổ sung `Http::fake()` cho các driver gọi HTTP external API (`PayOSGateway`, `MoMoGateway`) để test có thể pass độc lập offline mà không phụ thuộc internet.
