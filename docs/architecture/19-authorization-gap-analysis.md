# 19 — Phân tích khoảng trống Authorization & Kế hoạch khắc phục

> Ghi chú phân tích (2026-09-21). Dựa trên kiểm tra trực tiếp mã nguồn, không phải giả định.

---

## 19.1 Mục tiêu

Khắc phục khoảng cách giữa hệ thống permission đã seed (Spatie) và lớp controller
không enforce nó — cụ thể là các FormRequest trả về `true` / `auth()->check()`.

---

## 19.2 Thực trạng đã xác minh

### Thống kê (43 admin FormRequest trong `app/Http/Requests/Admin`)

| Trạng thái | Số lượng | Chi tiết |
|---|---|---|
| `return true` | 30 | Banner, Brand, Cart, Coupon, FlashSale, Inventory, Menu, Order, Payment, PaymentMethod, Product, ProductAttribute, ProductReview, ProductVariant, Warehouse (mỗi module Store + Update) |
| `return auth()->check()` | 3 | `Page/Store`, `Post/Store`, `Tag/Store` |
| Có comment `// TODO (Future): Enable policy check...` | 3 | `Category/Store`, `PostCategory/Store`, `PostCategory/Update` |
| Thực sự check `can()` | 7 | `Role` (create/update), `User` (create/update), `Setting` (update), `Language` (store/update — mượn `settings.update`) |

**=> 36 request gần như không enforce quyền, chỉ 7 làm đúng.**

Lớp **API V1** (`app/Http/Requests/Api/V1/Admin`) thì ngược lại: **toàn bộ 7 request
đã check `can()` đúng** (Role, User, Media, AssignRole). Có sự bất đối xứng sống
giữa API và web admin trên cùng một tập dữ liệu.

### Hai tiền đề sai cần sửa

1. **"`BaseRequest` chưa có"** — sai. `app/Core/Base/BaseRequest.php` đã tồn tại
   và đã có sẵn `failedAuthorization()` trả về JSON 403. Pha mở rộng chỉ ~15 dòng,
   không phải tạo mới.

2. **"Mọi user đã đăng nhập có thể gọi mọi admin endpoint"** — sai, và đây là điểm
   quan trọng nhất. `AdminMiddleware` (`app/Http/Middleware/AdminMiddleware.php:21`,
   áp dụng tại `routes/web.php:26`) chặn bất kỳ ai không có
   `is_admin || role('admin') || role('super-admin')`.

   Hệ quả: **role `editor` bị chặn ngay cửa vào**, không bao giờ tới được FormRequest.
   Mà trong `RolePermissionSeeder`, cả `admin` lẫn `super-admin` đều được
   `syncPermissions(toàn bộ)`. Do đó:

   > **Sửa `authorize()` mà không sửa `AdminMiddleware` = thay đổi bằng 0 về kiểm
   > soát truy cập thực tế**, vì mọi user tới được endpoint đều đã có toàn quyền.

---

## 19.3 Rủi ro & điểm mù

- **Thứ tự thực hiện sai sẽ làm công việc vô nghĩa** — middleware là chốt cửa,
  FormRequest chỉ có tác dụng khi `editor` vào được admin section.
- **Hai nguồn thật của quyền admin**: cột `is_admin` (boolean) và role Spatie.
  Middleware dùng OR để check cả hai — nếu chỉ dựa vào cột `is_admin` thì vẫn
  qua cửa, bypass toàn bộ Spatie.
- **`editor` hiện tại là role "dối"**: gán `editor` cho người dùng thì họ không vào
  được admin (bị middleware chặn); muốn vào thì phải thêm role `admin` — và họ có
  toàn bộ quyền. Không có trạng thái trung gian.
- **Bất đối xứng API vs web**: cùng dữ liệu, hai tiêu chuẩn enforcement.
- **`StoreLanguageRequest` mượn `settings.update`** để gate language CRUD — tiện
  nhưng ngữ nghĩa lười, nên tách permission riêng khi có cơ hội.

---

## 19.4 Kế hoạch đề xuất (đã sửa thứ tự)

### Pha 1 — Mở cửa đúng cách (điều kiện tiên quyết)

Sửa `AdminMiddleware`: chuyển từ "có role admin/super-admin" sang "có bất kỳ
permission admin nào" (hoặc permission `admin.access` chuyên biệt). Mục tiêu:
`customer` vẫn bị chặn, `editor` vào được nhưng bị giới hạn bởi các quyền đã seed.

### Pha 2 — Khuôn mẫu

Mở rộng `app/Core/Base/BaseRequest.php`: thêm `protected ?string $permission`
và `authorize()` mặc định giải quyết `can($this->permission)`; trả `true` khi
chưa set (backward compatible, không big-bang).

### Pha 3 — Migration từng module nội dung

`Page`, `Post`, `PostCategory`, `Category`, `Tag`, `Banner`, `Menu`, `Media` —
đúng nhóm `editor` có quyền theo `RolePermissionSeeder`. Sau khi migrate, login
với role `editor` để verify bị chặn đúng chỗ.

E-commerce stub (`Cart`, `Payment`, `PaymentMethod`, `Warehouse`, `Inventory`,
`FlashSale`, `Coupon`, `Order`) giữ `return true` đến khi có controller thật.

### Pha 4 — Tài liệu

Ghi quy ước `BaseRequest::$permission` vào `docs/architecture/09-base-classes.md`.

---

## 19.5 Lưu ý

- Có sẵn comment `// TODO (Future): Enable policy check when permissions are
  implemented` ở 3 file — đội ngũ **đã cố ý hoãn** việc này, nên đây là nợ kỹ thuật
  có chủ đích, không phải sót.
- Nếu sản phẩm chỉ bao giờ dùng `super-admin` đơn lẻ, lớp permission enforcement
  là dead weight. Phải xác nhận có nhu cầu đa role thật trước khi đầu tư Pha 3.
