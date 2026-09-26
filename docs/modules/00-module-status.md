# 00 — Trạng thái các Module (Master Tracker)

> **Cập nhật lần cuối:** 09/2026 (sau audit toàn bộ codebase + DB dump)
> **Cách dùng:** file này là **bảng kê tổng**. Mỗi module có 1 file chi tiết trong thư mục
> `docs/modules/{module}.md` — chứa danh sách file, hàm cần có, yêu cầu giao diện, và task cụ thể.
>
> **Chú giải trạng thái:**
> - ✅ **Hoàn thành** — DB schema thật, đủ lớp, có route + view, chạy được.
> - 🚧 **Shell** — đã có Controller/Service/View nhưng **DB chỉ là stub** (chỉ `id` + `timestamps`): UI render được, nhưng create/update **sẽ lỗi**.
> - ⬜ **Chưa bắt đầu** — chưa có file gì.
>
> Cập nhật trạng thái tại đây **mỗi khi hoàn thành 1 module** (đổi 🚧/⬜ → ✅).

---

## Bảng kê tổng

### Tầng 0 — Core (Auth & RBAC)

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 1 | User | ✅ Hoàn thành | ✅ `users` (22 cột) | Có soft-delete + restore + force-delete, gán vai trò | [user.md](user.md) |
| 2 | Role & Permission | ✅ Hoàn thành | ✅ `roles`, `permissions` (spatie) | Quản lý qua modal; đã enforce qua FormRequest + `BulkActionRegistry` (Pha 1–6) | [role.md](role.md) |
| 3 | Media | ✅ Hoàn thành | ✅ `media` (14 cột) | Media Library tập trung, dùng cho mọi module | [media.md](media.md) |
| 4 | MediaFolder | ✅ Hoàn thành | ✅ `media_folders` (8 cột) | Thư mục Media | [media-folder.md](media-folder.md) |

### Tầng 1 — Nền tảng

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 5 | Language | ✅ Hoàn thành | ✅ `languages` (10 cột) | Có rule bảo vệ default language + cache | [language.md](language.md) |
| 6 | Setting | ✅ Hoàn thành | ✅ `settings` (10 cột) | Cấu hình theo group, đa ngôn ngữ | [setting.md](setting.md) |

### Tầng 2 — CMS

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 7 | PostCategory | ✅ Hoàn thành | ✅ `post_categories` + translations | Có test Unit + Feature | [post-category.md](post-category.md) |
| 8 | Post | ✅ Hoàn thành | ✅ `posts` + translations + `post_tag` | Có test; tag sync qua PostService | [post.md](post.md) |
| 9 | Page | ✅ Hoàn thành | ✅ `pages` + translations | Refactor 09/2026 xong; test đang hoãn (spec trong `15-testing` §6) | [page.md](page.md) |
| 10 | Tag | 🟡 Hoàn thành - Cần kiểm tra và điều chỉnh | ✅ `tags` + `post_tag` | Đã có migration enhance, Bootstrap 5.3 theo mẫu Post, Test Feature 5/5 PASS | [tag.md](tag.md) |
| 11 | Banner | 🟡 Hoàn thành - Cần kiểm tra và điều chỉnh | ✅ `banners` + `banner_translations` | Đã có migration enhance, Bootstrap 5.3 theo mẫu Post, Test Feature 5/5 PASS | [banner.md](banner.md) |
| 12 | Menu | 🟡 Hoàn thành - Cần kiểm tra và điều chỉnh | ✅ `menus` + `menu_items` | Đã có migration enhance, Bootstrap 5.3 theo mẫu Post, Test Feature 5/5 PASS | [menu.md](menu.md) |

### Tầng 3 — Danh mục sản phẩm

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 13 | Category (danh mục SP) | 🟡 Hoàn thành - Cần kiểm tra và điều chỉnh | ✅ `categories` + translations | Đã chuẩn hóa Bootstrap 5.3 theo mẫu Post; xóa orphan `categorys` | [category.md](category.md) |
| 14 | Brand | 🟡 Hoàn thành - Cần kiểm tra và điều chỉnh | ✅ `brands` + `brand_translations` | Đã có migration enhance, Bootstrap 5.3 theo mẫu Post, Test Feature 5/5 PASS | [brand.md](brand.md) |
| 15 | ProductAttribute | ✅ Hoàn thành | ✅ `product_attributes` (+ product_id) + translations + values | Catalog toàn cục + custom per-product; Test Feature 15 case PASS | [product-attribute.md](product-attribute.md) |

### Tầng 4 — Sản phẩm

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 16 | Product | ✅ Hoàn thành | ✅ `products` + translations + images + variants + pivots | CRUD + thuộc tính + biến thể xong; fix 09/2026: bug update variant SKU, dropdown Category rỗng, custom attr trùng code 500, XSS color_code, ownership scoping, read authz `products.view`; Test Feature 24 case PASS + 12 regression + 2 bulk | [product.md](product.md) |
| 17 | ProductVariant | ✅ Hòa vào Product | ✅ `product_variants` + pivots | Không còn module riêng — quản lý trong form Product (kiểu WP) | [product-variant.md](product-variant.md) |
| 18 | ProductReview | 🚧 Shell | ❌ `product_reviews` rỗng | | [product-review.md](product-review.md) |

### Tầng 5 — Giao dịch

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 19 | Cart | 🚧 Shell | ❌ `carts` rỗng; **thiếu luôn `cart_items`** | Không có UI Admin (chỉ Service/API) | [cart.md](cart.md) |
| 20 | Coupon | 🚧 Shell | ❌ `coupons` rỗng; **thiếu `coupon_users`** | | [coupon.md](coupon.md) |
| 21 | FlashSale | 🚧 Shell | ❌ `flash_sales` rỗng; **thiếu `flash_sale_products`** | | [flash-sale.md](flash-sale.md) |

### Tầng 6 — Đơn hàng & Thanh toán

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 22 | Order | 🚧 Shell | ❌ `orders` rỗng; **thiếu `order_items`, `order_status_histories`, `shipping_addresses`** | | [order.md](order.md) |
| 23 | PaymentMethod | 🚧 Shell | ❌ `payment_methods` rỗng | | [payment-method.md](payment-method.md) |
| 24 | Payment | 🚧 Shell | ❌ `payments` rỗng; **thiếu `transactions`** | Cổng thanh toán: VNPay/Stripe/COD | [payment.md](payment.md) |

### Tầng 7 — Logging & tồn kho

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 25 | Warehouse | 🚧 Shell | ❌ `warehouses` rỗng | | [warehouse.md](warehouse.md) |
| 26 | Inventory | 🚧 Shell | ❌ `inventories` rỗng; **thiếu `inventory_histories`** | | [inventory.md](inventory.md) |
| 27 | ActivityLog | ⬜ Chưa bắt đầu | ❌ thiếu `activity_logs` | Observer tự ghi, không cần UI | [activity-log.md](activity-log.md) |
| 28 | LoginHistory | ⬜ Chưa bắt đầu | ❌ thiếu `login_histories` | | [login-history.md](login-history.md) |
| 29 | Notification | ⬜ Chưa bắt đầu | ❌ thiếu `notifications` | | [notification.md](notification.md) |

**Tổng:** 10 module hoàn thành — 16 module shell — 3 module chưa bắt đầu.

---

## Lỗi cấu trúc cần sửa (phát hiện qua audit)

| # | Lỗi | Vị trí | Mức độ | Cách sửa |
|---|---|---|---|---|
| L1 | **Thư mục view viết sai chính tả** `categorys` (orphan — controller dùng `admin.categories.*`) | `resources/views/admin/categorys/` | 🟢 Đã sửa | Đã xóa thư mục `categorys/` (đã có `categories/` đúng) |
| L2 | **Model rỗng không `$fillable`** → mass-assignment mất dữ liệu / SQL error | `app/Models/Tag.php` (các module shell khác tương tự) | 🔴 Cao | Thêm `$fillable` + `HasUuid` khi hoàn thiện module |
| L3 | **Binding chết (comment)** trỏ tới class không tồn tại | `RepositoryServiceProvider:50-54` | 🟡 TB | Đã xóa trong đợt trước |
| L4 | **Service không kế thừa `BaseService`** + dùng raw `DB::beginTransaction()` thay vì `handleTransaction()` | `app/Services/Admin/Language/LanguageService.php` | 🟡 TB | Chuyển sang `BaseService` + `handleTransaction` |
| L5 | ~~Permission `pages.bulk` chưa seed / chưa enforce~~ | `docs/04-permissions.md` | 🟢 Đã sửa | Pha 6: bulk gate bằng quyền của action qua `BulkActionRegistry` (`delete` → `pages.delete`, đổi trạng thái → `pages.update`) — không cần permission `.bulk` riêng |
| L6 | **Module shell có UI nhưng DB rỗng** — người dùng mở trang sẽ gặp lỗi | 16 module 🚧 | 🔴 Cao | Ẩn menu sidebar các module shell cho đến khi DB sẵn sàng |

---

## Bảng migration còn thiếu (theo `docs/02-database-overview.md`)

`cart_items`, `order_items`, `order_status_histories`, `shipping_addresses`, `transactions`,
`coupon_users`, `flash_sale_products`, `product_variant_attributes`, `activity_logs`,
`login_histories`, `notifications`, `email_templates`, `sms_templates`.

---

## Thứ tự ưu tiên thực hiện

1. **Sửa lỗi cấu trúc L1, L6** (nhanh, giảm rủi ro ngay).
2. **Hoàn thiện Tầng 3** (Brand → ProductAttribute) → là phụ thuộc của Product.
3. **Tầng 4** (Product + Variant + Review).
4. **Tầng 5–6** (Cart → Order → Payment).
5. **Tầng 7** (ActivityLog — Observer, đặt cuối vì phụ thuộc tất cả).

> Quy trình build từng module: [`docs/architecture/11-module-tutorial.md`](../architecture/11-module-tutorial.md).
> Chọn Profile (Full/Simple): [`docs/07-development-process.md`](../07-development-process.md) §1.5.
> Không tạo file trước khi schema sẵn sàng: [`docs/07-development-process.md`](../07-development-process.md) §1.6.
