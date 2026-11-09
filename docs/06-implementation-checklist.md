# PHẦN 7: CHECKLIST TRIỂN KHAI (LEGACY)

> ⚠️ **TÀI LIỆU LEGACY (v1.0 — 2024 / Cập nhật trạng thái 10/2026).**
> Checklist này theo dõi tiến độ theo **phase** của bản thiết kế ban đầu.
> **Bảng theo dõi tiến độ chuẩn hiện tại của dự án theo 8 tầng phụ thuộc:** [`modules/00-module-status.md`](modules/00-module-status.md).
>
> Khi làm việc thực tế:
> - Quy trình & thứ tự build module: [`07-development-process.md`](07-development-process.md) (Phần 2 — Build Order theo tầng phụ thuộc).
> - Checklist trước khi hoàn thành: [`architecture/14-checklist.md`](architecture/14-checklist.md).

### PHASE 1 - CORE SYSTEM:
- [x] Tạo migrations cho `users`, `roles`, `permissions`, pivots
- [x] Tạo models & relationships
- [x] Tạo `HasPermissions` trait / Spatie permission
- [x] Tạo middleware `CheckPermission` / `AdminMiddleware` + `admin.access` gate
- [x] Seed permissions & roles
- [x] Authentication (login, register, reset password)
- [x] Admin dashboard cơ bản

### PHASE 2 - PRODUCT & CATEGORY:
- [x] categories + translations
- [x] brands + translations
- [x] products + translations
- [x] product images, variants, attributes
- [x] Admin CRUD cho products (Revamp Form P0–P2, Architecture upgrade P0–P3)

### PHASE 3 - ORDER & CART:
- [x] carts, cart_items (Hoàn thành 10/2026: Cart, CartItem, unique line_key, CartPricingService, CartService, X-Cart-Token, Admin UI, Cleanup commands)
- [x] shipping_addresses (Hoàn thành 10/2026: migration, Model HasUuid, accessor full_address)
- [x] orders, order_items (Hoàn thành 10/2026: OrderStatus enum state machine, snapshot items, admin CRUD, test 8/8 PASS)
- [x] order_status_histories (Hoàn thành 10/2026: ghi vết thay đổi trạng thái kèm người thao tác)
- [x] Checkout flow (Hoàn thành 10/2026: CheckoutService, CheckoutDTO, CheckoutController, anti-fraud giá DB)

### PHASE 4 - PAYMENT:
- [x] payment_methods (Hoàn thành 10/2026: **14 cột**, mã hóa AES-256 credentials, caching layer, dynamic schema UI; `PaymentMethodCrudTest` 13 + Unit 15)
- [x] payments (Hoàn thành 10/2026: **13 cột**, PaymentStatus enum, Idempotency — lưu ý index `(gateway_code, gateway_transaction_id)` hiện **non-unique**, idempotency mới ở tầng ứng dụng)
- [x] Payment gateway integration (Pluggable Registry với 8 driver: COD, Bank Transfer/VietQR, VNPay, SePay, PayOS, MoMo, Stripe, PayPal)
- [x] Webhook Controller (Hoàn thành 10/2026: xác thực chữ ký đa cổng VNPay, SePay, PayOS, MoMo, Stripe, PayPal, idempotency chống trùng, test 7/7 PASS)
- [ ] Notification listener cho `PaymentSucceeded` / `PaymentFailed` (event đã dispatch, **chưa có listener nào** — `php artisan event:list`)
- [ ] `CheckoutController::handleReturn` bản thật (hiện mới echo payload)
- [x] Seed `payment_methods` cho dev/demo DB (Hoàn thành 11/2026: `PaymentMethodSeeder` — 8 cổng khớp registry, COD + VietQR bật; dev DB `payment_methods=8`, `active=2`)

### PHASE 5 - CMS:
- [x] posts + translations
- [x] pages + translations
- [x] banners + translations
- [x] menus, menu_items
- [x] tags (dùng chung cho Post + Product)

### PHASE 6 - ADVANCED FEATURES:
- [ ] Warehouse management (schema thật: tên kho, địa chỉ, uuid — `warehouses` mới có 3 cột `id`/`timestamps`)
- [ ] Inventory management (phụ thuộc Warehouse; `inventories` mới có 3 cột, **thiếu `inventory_histories`**; StockService groundwork đã xong). Menu 2 module này **đã tạm ẩn** từ 11/2026.
- [x] Coupons & promotions (Hoàn thành 10/2026: coupons, coupon_users, CouponValidationService, auto restore stock & coupon on cancellation; 16 test)
- [x] Flash sales Phase 0 & 1 (Hoàn thành 10/2026: 5 migrations, models, enums, CRUD Admin Kiểu A & B, Modal picker, Hotfix checkout deadlock, **13 test PASS**)
- [x] **Flash sales Phase 2** (Hoàn thành 2026-11-03: `FlashSalePriceResolver` áp giá flash vào `CartPricingService`/`CheckoutService`, `ReserveFlashSaleQuotaAction` + `ReleaseFlashSaleQuotaAction` ghi/hoàn `flash_sale_redemptions`, API `GET /api/v1/flash-sales/active`, cart API trả `is_flash_sale`. Rule đã chốt: không cộng dồn coupon, tính lại giá lúc checkout, reserve khi đặt hàng. Test `FlashSaleCheckoutTest` 12/12)
- [ ] Job auto-hủy đơn `pending` quá hạn thanh toán (để hoàn suất flash tự động) + listener `PaymentFailed`
- [ ] Reports & analytics
- [ ] Activity logs

### PHASE 7 - OPTIMIZATION & HARDENING:
- [x] Database indexes (slug, locale, category_id, brand_id, product_translation indexes...)
- [x] Security hardening (admin.access gate, AuthorizesWithPermission, BulkActionRegistry)
- [x] Eager loading & N+1 fix (Product list, Media picker)
- [x] Catalog cache & Observers invalidation
- [ ] Full Redis / Memcached production setup
- [ ] API rate limiting production tuning

### PHASE 8 - KIỂM KẾ TIẾN ĐỘ & ỔN ĐỊNH (2026-11-03):
- [x] 3 trang admin 500 (`/admin/payments`, `/admin/warehouses`, `/admin/inventories`) — sửa lệch tên biến `compact()`/view, viết lại view index theo chuẩn `x-admin` (L6, L7 trong Master Tracker)
- [x] Ẩn menu sidebar khối "Kho hàng" (module shell) — `layouts/admin.blade.php`, bọc `@if(false)` kèm TODO để mở lại khi module xong
- [x] Regression test `tests/Feature/Admin/PaymentAndShellIndexTest.php` (5 test / 19 assertion) — smoke 3 trang + khách bị redirect + sidebar không còn link shell
- [x] Permission + `can:` cho nhóm tiền & kho: `orders.delete`, `payments.*`, `payment-methods.*`, `warehouses.*`, `inventories.*`; 118 permission web-guard sau seed
- [x] `PaymentMethodSeeder` (8 cổng) + đăng ký trong `DatabaseSeeder`
- [x] Unique index DB-level `(gateway_code, gateway_transaction_id)` + guard báo lỗi nếu dữ liệu hiện hữu trùng
- [x] Test `tests/Feature/Admin/MoneyModuleAuthorizationAndIdempotencyTest.php` (6 test / 26 assertion) — 403 với role thiếu quyền, 200 với `is_admin`, duplicate txn bị DB từ chối, NULL vẫn insert được
- [x] Đồng bộ tài liệu lệch code: `docs/modules/00-module-status.md`, `docs/03-database-details.md`, `docs/modules/coupon.md`, `docs/04-permissions.md`, file này
- [x] Khôi phục `roadmap_next_steps.md` (bị xóa nhầm ở `97a144a`)
- [x] Smoke thật trên dev DB: dashboard/orders/payments/payment-methods/warehouses/inventories đều **200**; suite **322 test / 1117 assertion PASS**
- [ ] Việc còn mở: Notification + listener, FlashSale Phase 2, Warehouse→Inventory schema, `handleReturn`, permission "ma" (Task 7.3), Page tests

---

## DOCUMENT INFO
- **Version**: 1.3 (Đồng bộ theo Master Tracker 2026-11-03)
- **Last Updated**: 10/2026
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: MySQL 8.0+ / MariaDB 10.4+
