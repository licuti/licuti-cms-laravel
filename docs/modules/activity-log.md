# Module: ActivityLog

> ⬜ **Chưa bắt đầu** | Tầng 7 — Logging
> Spec: [`07` §3.5`](../07-development-process.md) (MODULE: Activity Logs)

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `activity_logs` | ❌ **không tồn tại** |
| Model / Observer / Service | ❌ chưa có |
| UI | ❌ (chỉ xem, không cần form) |

## Kế hoạch (theo spec)

### 1. Migration
```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('log_name', 50)->index();          // posts, products, orders...
    $table->text('description');
    $table->nullableMorphs('subject');                // model bị tác động
    $table->nullableMorphs('causer');                 // user thực hiện
    $table->json('properties')->nullable();           // before/after diff
    $table->timestamps();
});
```

### 2. Observer (tự động — không gọi thủ công)
- `ProductObserver`, `OrderObserver`, `UserObserver`... — ghi log khi `created/updated/deleted`.
- Đăng ký trong `EventServiceProvider`.

### 3. Service / Repository
- `ActivityLogService::log(string $logName, string $description, Model $subject, ?User $causer): void`
- Repository: `getFiltered(array $filters)` — lọc theo log_name, causer, date range.

### 4. Giao diện Admin
- `index.blade.php` — bảng nhật ký: thời gian, người dùng, hành động, đối tượng (link sang record), diff (properties JSON render dạng before/after).
- Chỉ xem + xóa (có `logs.clear` permission).
- UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Lưu ý
Build **sau cùng** vì observer phụ thuộc mọi module khác đã ổn định.
