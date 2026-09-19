# Module: Notification

> ⬜ **Chưa bắt đầu** | Tầng 7 — Logging

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `notifications` | ❌ **không tồn tại** |
| Migration `email_templates`, `sms_templates` | ❌ **không tồn tại** |
| Model / Service / UI | ❌ |

## Kế hoạch

### 1. Migration
- `notifications`: id, uuid, `notifiable_type`/`notifiable_id` (morph), `type`, `data` (json), `read_at`, timestamps — dùng schema chuẩn Laravel notifications.
- `email_templates`: id, uuid, code unique, subject, body (blade string), placeholders (json), is_active.
- `sms_templates`: tương tự (nếu dùng SMS).

### 2. Service / Events
- Dùng Laravel `Notification` facade + Mailable.
- Lắng nghe `OrderPlaced`, `OrderShipped`, `UserRegistered` (đã có skeleton trong `app/Events/`) để gửi notification.
- Template render qua `EmailTemplateService::render(string $code, array $data)`.

### 3. Giao diện Admin
- `admin/notifications/index.blade.php` — danh sách thông báo của user đang đăng nhập, đánh dấu đã đọc.
- `admin/email-templates/` — CRUD template (code, subject, body TinyMCE, placeholders).
- UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Phụ thuộc
Nên làm sau khi module Order hoàn thiện (notification chủ yếu xoay quanh đơn hàng).
