# 🗺️ Kế hoạch Phát triển Tiếp theo — Licuti CMS

> **Khôi phục ngày 2026-11-03.** File này từng tồn tại (thêm ở `58126cf`, 13/10/2026) và **bị xóa nhầm ở `97a144a`** (20/10/2026)
> khi triển khai Order/Payment. Bản dựng lại gồm: (A) trạng thái 5 giai đoạn cũ đã đối chiếu code thật,
> (B) kế hoạch các giai đoạn tiếp theo theo thứ tự phụ thuộc.
> Nguồn sự thật cho trạng thái từng module: [`docs/modules/00-module-status.md`](docs/modules/00-module-status.md).

## Tổng quan Thứ tự Phụ thuộc

```
[Seed payment_methods + RBAC module tiền] ──> [Notification + Listener] ──> [FlashSale Phase 2] ──> [Reports]
        (mở khóa end-to-end)                        (mở khóa email)              (cần business rule)      (cần dữ liệu đơn)

[Warehouse schema] ──> [Inventory + inventory_histories] ──> [ActivityLog (cuối cùng)]
```

---

## A. 5 giai đoạn của roadmap cũ — trạng thái đã xác minh 2026-11-03

| Giai đoạn | Nội dung | Trạng thái | Bằng chứng |
|---|---|---|---|
| 1 — 🏗️ Orders schema | `OrderStatus` enum, `orders` + `order_items` + `shipping_addresses` + `order_status_histories`, model/DTO/service/views/test | ✅ Xong | `orders` 23 cột; state machine `OrderService::updateStatus`; `OrderCrudTest` 8 + `OrderCancelRestoresStockTest` 1 |
| 2 — ⚙️ Payments schema | `PaymentStatus` enum, `payments`, model, repository/service | ✅ Xong | `payments` 13 cột; enum 6 trạng thái |
| 3 — 🔗 Webhook Controller | route + `PaymentWebhookController` + **Events & Listeners** + test | 🟡 **Còn 1 task** | `PaymentWebhookTest` 7/7 PASS; **Task 3.3 Events & Listeners CHƯA làm** — `event:list` cho thấy 4 event dispatch nhưng **0 listener** |
| 4 — 🛒 Checkout flow | `CheckoutService`, place order API, price drift/assert expected_total | 🟡 **Còn 1 task** | `CheckoutTest` 8/8 PASS (đã xử lý price drift ở `215c604`); **`CheckoutController::handleReturn` mới là stub** (chỉ echo payload, chưa xác thực + redirect) |
| 5 — 🧪 Fix unit test (`Http::fake`) | Unit test không gọi gateway thật | ✅ Xong | 15/15 unit PASS; toàn suite 316 test / 1091 assertion PASS |

---

## B. Các giai đoạn tiếp theo

### Giai đoạn 6 — 🩹 P0: 3 trang admin 500 + tài liệu lệch code
**Phụ thuộc:** không — ✅ **HOÀN THÀNH 2026-11-03**
- Nguyên nhân: controller `compact()` biến viết hoa (`Payments`/`Warehouses`/`Inventories`) trong khi view dùng biến chữ thường.
- Đã sửa: `PaymentController` / `WarehouseController` / `InventoryController` (đổi tên biến), viết lại 3 view index
  (`x-admin.page-header` + `x-admin.table` + `x-admin.badge` theo chuẩn admin-ui hiện có), ẩn khối menu "Kho hàng"
  trong `resources/views/layouts/admin.blade.php` (bọc `@if(false)`), thêm `tests/Feature/Admin/PaymentAndShellIndexTest.php` (5 test / 19 assertion).
- Đã đồng bộ: `docs/modules/00-module-status.md`, `docs/03-database-details.md`, `docs/modules/coupon.md`, `docs/06-implementation-checklist.md`, và file này.

### Giai đoạn 7 — 🔓 Mở khóa checkout thật + phân quyền module tiền
**Phụ thuộc:** không. ✅ **HOÀN THÀNH 2 phần / 3 task — 2026-11-03**
- [x] **Task 7.1** Seed `payment_methods`: `database/seeders/PaymentMethodSeeder.php` tạo 8 cổng khớp `PaymentGatewayRegistry`
  (cod, bank_transfer, vnpay, sepay, payos, momo, stripe, paypal), COD + VietQR bật `is_active`, các cổng cần credential để tắt.
  Đã đăng ký trong `DatabaseSeeder` và **đã chạy trên dev DB**: `payment_methods=8`, `active=2`.
- [x] **Task 7.2** Permission `orders.delete` + `payments.*` + `payment-methods.*` + `warehouses.*` + `inventories.*`
  trong `RolePermissionSeeder`; gắn `can:` cho routes `orders`, `payments`, `payment-methods`, `warehouses`, `inventories`
  trong `routes/web.php` (theo đúng convention sẵn có: tách resource theo nhóm verb như `coupons`/`flash-sales`).
  Verify: `MoneyModuleAuthorizationAndIdempotencyTest` 6 test — role thiếu quyền nhận **403**, `is_admin` vẫn **200** (`Gate::before`).
  Tài liệu: [`docs/04-permissions.md`](docs/04-permissions.md).
- [ ] **Task 7.3** Gỡ/seed các permission "ma" (`orders.refund`, `orders.export`, `reports.*`, `logs.*`, `inventory.*`)
  hoặc làm tính năng tương ứng — UI phân quyền đang gây hiểu nhầm. **Chưa làm (cần quyết định sản phẩm).**

### Giai đoạn 8 — Notification dùng chung (phạm vi chốt 2026-11-10)
**Phụ thuộc:** nghiệp vụ ecommerce hiện có. Bản đầu gồm hộp admin cá nhân và email giao dịch tiếng Việt cố định; CMS/tài khoản nối thêm sau.
- [x] Migration `notifications`, `notification_deliveries`; kiểm thử MySQL và áp dụng riêng hai migration trên local.
- [x] Listener cho `OrderPlaced`, `PaymentSucceeded`, `OrderCancelled`, `ReviewApproved`, `LowStockThresholdReached`; ghi ý định trong transaction và xử lý sau commit.
- [x] Hàng đợi, lease, retry hữu hạn, chống phát lại, recovery/purge; mẫu email an toàn.
- [x] Chuông + hộp cá nhân, phân quyền hiện tại và owner scope bắt buộc, kể cả super-admin.
- [x] Kiểm thử rollback, webhook lặp, cạnh tranh worker, thu hồi quyền và trình duyệt thật (admin + nhân viên, light/dark/mobile). Toàn suite 406 test / 1755 assertion PASS.
- [ ] Nghiệm thu gửi SMTP tới hộp thư thử được cho phép; mail array/log không chứng minh thư đã tới inbox. **(còn mở — chưa có SMTP thật)**
- **Hoãn:** `PaymentFailed` (chưa xác định được thất bại đã xác thực và người nhận), SMS, CRUD template, giao hàng, marketing, WebSocket/push.
- Chi tiết vận hành và giới hạn chống trùng: [Notification](docs/modules/notification.md).

### Giai đoạn 9 — ⚡ FlashSale Phase 2 (giá flash vào giỏ/checkout)
**Phụ thuộc:** không (làm độc lập với Notification). ✅ **HOÀN THÀNH 2026-11-03** — chi tiết: [`docs/modules/flash-sale.md`](docs/modules/flash-sale.md) §5
- [x] **Task 9.0 — business rule đã chốt** (chủ dự án chọn 2026-11-03): **(a)** giá flash **KHÔNG** cộng dồn coupon;
  **(b)** giá **tính lại lúc checkout**; **(c)** **reserve khi đặt hàng**, release khi hủy/lỗi cổng;
  **(d)** hỗ trợ cả variant lẫn sản phẩm cha (row có `product_variant_id` thắng); **(e)** repo không có khách sỉ/giá riêng.
- [x] Lookup O(1): `FlashSalePricingContext` (scoped/request) — `CartPricingService` nạp 1 query cho cả giỏ,
  tra memo `v:{variantId}` / `p:{productId}`; không cache cross-request vì quota đổi realtime (cache = oversell).
- [x] `ReserveFlashSaleQuotaAction` (`lockForUpdate`, chặn vượt `quantity_limit` + `limit_per_user`, ghi redemption `reserved`)
  và `ReleaseFlashSaleQuotaAction` (idempotent) gắn vào listener `ReleaseOrderStockAndCoupon`.
- [x] Áp giá: `FlashSalePriceResolver` decorates `DefaultPriceResolver`, bind trong `AppServiceProvider`;
  `order_items.flash_sale_product_id` ghi qua `CheckoutService` → `OrderService::create`. `expected_total` giữ nguyên.
- [x] API storefront `GET /api/v1/flash-sales/active` (`throttle:60,1`) + cờ `is_flash_sale` trong `CartItemResource`.
- [x] Test `tests/Feature/Api/FlashSaleCheckoutTest.php`: 12 test / 51 assertion — giá vào đơn, reserve + redemption,
  oversell bị chặn & rollback tồn kho, `limit_per_user`, coupon chỉ tính hàng thường (15.000đ chứ không 27.000đ),
  giỏ toàn flash không giảm, chiến dịch kết thúc → giá thường, hủy đơn → hoàn suất, sold out → giá thường, API active.
- [ ] **Còn mở:** job auto-hủy đơn `pending` quá hạn để hoàn suất flash; listener `PaymentFailed` (Giai đoạn 8);
  Redis cache tầng campaign nếu traffic flash sale lớn.

### Giai đoạn 10 — 📦 Warehouse → Inventory
**Phụ thuộc:** 7 (permission). **Ước lượng:** Warehouse ~4h, Inventory ~8h ( Inventory phụ thuộc Warehouse )
- [ ] `warehouses`: tên kho, địa chỉ, phone, is_default, `uuid` (bảng hiện chỉ có `id`/`timestamps` → model `$guarded=[]`, form rỗng, edit theo uuid sẽ lỗi).
- [ ] `inventories` + `inventory_histories`: kho, sản phẩm/variant, số lượng, loại nhập-xuất-điều chỉnh, lý do; đồng bộ với `StockService` và luồng restock khi hủy đơn.
- [ ] Bỏ `@if(false)` ở sidebar khi 2 module xong; chuyển 🚧 → ✅ trong `00-module-status.md`.

### Giai đoạn 11 — 📊 Reports & Analytics
**Phụ thuộc:** 8, 9 (cần dữ liệu đơn/giao dịch thật). **Ước lượng:** ~10h
- [ ] `WidgetRegistry`/`ExportRegistry`/`SearchRegistry` hiện **còn TODO** dù đã bind singleton — quyết định làm thật hay gỡ.
- [ ] Doanh thu theo khung ngày/tháng, đơn theo trạng thái, sản phẩm bán chạy, cổng thanh toán, mã giảm giá đã dùng; export CSV.

### Giai đoạn 12 — 🔐 Hardening & hạ tầng production
**Phụ thuộc:** deploy. **Ước lượng:** ~4h
- [x] ~~Unique index DB-level cho `(gateway_code, gateway_transaction_id)`~~ — 🟢 **xong 2026-11-03**
  (`database/migrations/2026_11_03_000001_make_payments_gateway_index_unique.php`). Index cũ
  `payments_gateway_code_gateway_transaction_id_index` (non-unique) → `payments_gateway_code_gateway_transaction_id_unique`.
  Migration từ chối chạy nếu phát hiện dữ liệu trùng. Verify: `test_duplicate_gateway_transaction_id_is_rejected_by_database`
  và `test_null_gateway_transaction_id_is_still_allowed` (MySQL cho phép nhiều NULL nên bản ghi pending chưa có mã gateway vẫn insert được).
- [ ] `CheckoutController::handleReturn` thật: xác thực chữ ký/param, map đúng đơn, redirect có điều kiện.
- [ ] Redis/Memcached, queue worker, rate-limit cho `/checkout` + webhook (hiện chỉ cart được throttle).
- [ ] Xác nhận MySQL: môi trường dev đang **5.7.44** trong khi `docs/06` yêu cầu 8.0+/MariaDB 10.4+.

### Giai đoạn 13 — 🧷 Việc nhỏ còn tồn
- [ ] Page tests: 30 case theo `docs/architecture/15-testing.md` §6 (code Page đã xong, chưa có test).
- [ ] LoginHistory (bảng + ghi đăng nhập; nhỏ).
- [ ] ActivityLog: observer phủ mọi module — **làm cuối cùng**.
- [ ] Bảng legacy `product_variant_attributes` vẫn còn trong dev DB dù tracker nói đã bỏ → xác nhận rồi drop.
- [ ] `docs/07-development-process.md` §1.6: không tạo file trước khi schema sẵn sàng (nguyên nhân khiến Warehouse/Inventory thành shell có UI).
