# Module: PaymentMethod

> 🚧 **Shell** | Profile: **Simple** | Tầng 6 — Đơn hàng & Thanh toán
> Route: `admin.payment-methods.*` | Views: `resources/views/admin/payment-methods/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `payment_methods` | ❌ id+timestamps — thiếu code/name/is_active/config |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

Ứng viên **Simple Profile** ([`07` §1.5](../07-development-process.md)) — bảng cấu hình cổng thanh toán, không cần DTO.

### 1. Migration
```php
Schema::create('payment_methods', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('code', 30)->unique();        // vnpay / stripe / cod
    $table->string('name');
    $table->string('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->json('config')->nullable();          // credentials, endpoint...
    $table->timestamps();
});
```

### 2. Model
- `PaymentMethod`: `$fillable`, `HasUuid`, `casts` (config array, is_active boolean).

### 3. Repository / Service
- Repository: `BaseRepository` + `getActiveCodes()`.
- Service: chỉ CRUD + bật/tắt (có thể bỏ Service theo Simple Profile).

### 4. Giao diện Admin
| Field | Component | name |
|---|---|---|
| Mã cổng | `x-admin.input` | `code` |
| Tên hiển thị | `x-admin.input` | `name` |
| Mô tả | `x-admin.textarea` | `description` |
| Cấu hình (JSON) | `x-admin.textarea` hoặc dynamic | `config` |
| Trạng thái | `x-admin.toggle` | `is_active` |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
