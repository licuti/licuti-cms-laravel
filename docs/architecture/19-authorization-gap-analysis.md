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

✅ **Đã làm (2026-09-22).** Thêm permission chuyên biệt `admin.access` (migration
`2026_09_22_000000_add_admin_access_permission` + `RolePermissionSeeder` cấp cho
`super-admin` / `admin` / `editor`). Gate tập trung tại `User::canAccessAdmin()`:
`is_admin || can('admin.access')`, dùng trong cả `AdminMiddleware` và
`AuthController::authenticate()`.

> Không dùng "có bất kỳ permission nào" làm gate: dev DB từng có `customer` bị
> gán rác `banners.create` + `banners.delete` → customer suýt vào được admin.
> Permission chuyên biệt miễn dịch với trường hợp đó.

### Pha 2 — Khuôn mẫu

✅ **Đã làm.** Trait `app/Core/Traits/AuthorizesWithPermission.php`: class con
override `protected function permission(): ?string` trả tên permission (VD
`'posts.create'`), `authorize()` mặc định giải quyết qua `can()`. Trả `null`
= cho phép (backward compatible).

> Dùng **method** chứ không phải property: PHP 8.2 báo fatal khi class khai báo
> lại property của trait với default value khác (phát hiện khi test suite crash).

Trait tách khỏi `BaseRequest` vì §3.3 `09-base-classes.md`: admin Blade form phải
giữ hành vi redirect khi validation fail. Trait dùng được cho cả hai.

`AppServiceProvider::boot()` thêm `Gate::before` cấp toàn quyền cho `is_admin` —
giữ backward compatibility cho user/test tạo bằng cờ này.

### Pha 3 — Migration từng module nội dung

✅ **Đã làm.** Đã migrate 14 request thuộc 8 module: Page, Post, Category,
PostCategory, Tag, Banner, Menu (+ 7 request Phase 2: Role/User/Setting/Language).
Mỗi request 1 dòng override `permission()`.

Permission mới thêm (seeder + `docs/04-permissions.md`): `post-categories.*`,
`tags.*`, `menus.*`. Editor được cấp các quyền content; KHÔNG có `settings.*` /
`roles.*` / `users.*`.

E-commerce stub (`Cart`, `Payment`, `PaymentMethod`, `Warehouse`, `Inventory`,
`FlashSale`, `Coupon`, `Order`, `Product*`, `Brand`) vẫn `return true` — chờ
controller thật.

### Pha 4 — Tài liệu

✅ **Đã làm.** `docs/architecture/09-base-classes.md` §3.4 (quy ước + override
pattern), `docs/04-permissions.md` (thêm `admin.access`, `post-categories.*`,
`tags.*`, `menus.*`).

### Pha 5 — Test

✅ **Đã làm.** `tests/Feature/Admin/AdminAccessGateTest.php`: guest / có
admin.access / không có / legacy `is_admin`. Toàn suite 91 test pass sau migrate.

### Pha 6 — Bulk action authorization

✅ **Đã làm.** Trước Pha 6, `BulkActionRequest::authorize()` trả `return true` —
chỉ validate `bulk_module` + `action` đã đăng ký, **không check quyền**. Hệ quả:
role `editor` có `pages.create`/`pages.update` nhưng không có `pages.delete` vẫn
xóa hàng loạt trang được; quyền `.delete` đã seed là dead code. `CategoryController::bulk`
và `ProductController::bulk` còn tệ hơn: nhận `Request` thường + switch inline,
không qua FormRequest nào.

Giải pháp (single source of truth tại registry):

- `BulkActionRegistry::register(...)` thêm tham số tùy chọn `?string $permission`.
  `getPermission($module, $action)` resolve tên quyền; `getActionOptions($module)`
  lọc bỏ action mà user `can($permission) === false` → dropdown tự ẩn theo quyền.
- `BulkActionRequest::authorize()` resolve permission qua registry rồi `can()`;
  permission `null` (action chưa gắn) → cho qua (backward compatible).
- Convention map: `delete` → `{module}.delete`; đổi trạng thái (`status_*`,
  `activate`/`deactivate`, `publish`/`draft`/`archive`) → `{module}.update`.
- Đưa `CategoryController::bulk` + `ProductController::bulk` vào đúng pattern
  (`BulkActionRequest` + `$registry->dispatch(...)`); module `categories` /
  `products` đăng ký trong `BulkActionServiceProvider`.
- Test: `tests/Feature/Admin/BulkActionAuthorizationTest.php` (403 khi thiếu quyền,
  pass khi có quyền, `is_admin` qua `Gate::before`, `getActionOptions` lọc đúng).

**Không** thêm permission `.bulk` riêng: bulk gate bằng quyền của action nền tảng
(`.delete`/`.update`), tái sử dụng quyền đã seed.

---

## 19.5 Lưu ý

- Có sẵn comment `// TODO (Future): Enable policy check when permissions are
  implemented` ở 3 file — đội ngũ **đã cố ý hoãn** việc này, nên đây là nợ kỹ thuật
  có chủ đích, không phải sót.
- Nếu sản phẩm chỉ bao giờ dùng `super-admin` đơn lẻ, lớp permission enforcement
  là dead weight. Phải xác nhận có nhu cầu đa role thật trước khi đầu tư Pha 3.
