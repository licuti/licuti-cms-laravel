# Module: LoginHistory

> ⬜ **Chưa bắt đầu** | Tầng 7 — Logging

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `login_histories` | ❌ **không tồn tại** |
| Model / Service / UI | ❌ |

## Kế hoạch

### 1. Migration
```php
Schema::create('login_histories', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('ip_address', 45);
    $table->string('user_agent')->nullable();
    $table->string('event', 20);         // login / logout / failed
    $table->timestamp('occurred_at');
    $table->timestamps();
});
```

### 2. Tích hợp
- Ghi qua listener của event `Login` / `Logout` / `Failed` của Laravel (đăng ký trong `EventServiceProvider`), hoặc trực tiếp trong `AuthController::authenticate`.
- Đã có sẵn `UserRepository::updateLastLogin()` — kết hợp.

### 3. Giao diện Admin
- Tab trong trang chi tiết user, hoặc `admin/login-histories/index.blade.php` — bảng: user, thời gian, IP, device (parse user_agent), event badge.
- UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
