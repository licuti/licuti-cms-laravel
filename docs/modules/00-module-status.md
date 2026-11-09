# 00 — Trạng thái các Module (Master Tracker)

> **Cập nhật lần cuối:** 2026-11-03 (kiểm kê tiến độ: đối chiếu tracker với code thật + smoke test 3 trang admin + chạy `php artisan test`)
> **Cách dùng:** file này là **bảng kê tổng**. Mỗi module có 1 file chi tiết trong thư mục
> `docs/modules/{module}.md` — chứa danh sách file, hàm cần có, yêu cầu giao diện, và task cụ thể.
>
> **Chú giải trạng thái:**
> - ✅ **Hoàn thành** — DB schema thật, đủ lớp, có route + view, chạy được.
> - 🚧 **Shell** — đã có Controller/Service/View nhưng **DB chỉ là stub** (chỉ `id` + `timestamps`): UI render được, nhưng create/update **sẽ lỗi**.
> - ⬜ **Chưa bắt đầu** — chưa có file gì.
>
> Cập nhật trạng thái tại đây **mỗi khi hoàn thành 1 module** (đổi 🚧/⬜ → ✅).
>
> ⚠️ **Quy ước mới 11/2026:** cột ghi chú chỉ nêu test **đếm được thực tế trong `tests/`**. Những con số kiểu
> "180 case", "11/11" không tái kiểm chứng được đã bị bỏ. Trạng thái suite: **334 test / 1168 assertion PASS**, 0 fail.
>
> 🆕 **Đợt 2026-11-03 (đã kiểm chứng trên dev DB):** sửa 3 trang admin 500 + viết index Payments thật + ẩn menu shell;
> siết **unique index** `(gateway_code, gateway_transaction_id)`; thêm permission và `can:` cho nhóm tiền & kho
> (`orders.delete`, `payments.*`, `payment-methods.*`, `warehouses.*`, `inventories.*` — 118 permission web guard);
> seed 8 cổng thanh toán (COD + VietQR bật); **FlashSale Phase 2** tích hợp giá flash vào giỏ/checkout + quota reserve/release + API storefront.
> Smoke thật: dashboard/orders/payments/payment-methods/warehouses/inventories đều **200**.

---

## Bảng kê tổng

### Tầng 0 — Core (Auth & RBAC)

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 1 | User | ✅ Hoàn thành | ✅ `users` (22 cột) | Có soft-delete + restore + force-delete, gán vai trò. Chưa có Feature test CRUD riêng | [user.md](user.md) |
| 2 | Role & Permission | ✅ Hoàn thành | ✅ `roles`, `permissions` (spatie) | Quản lý qua modal; đã enforce qua FormRequest + `BulkActionRegistry` (Pha 1–6); test `AdminAccessGateTest`, `BulkActionAuthorizationTest` | [role.md](role.md) |
| 3 | Media | ✅ Hoàn thành | ✅ `media` (14 cột) | Media Library tập trung, dùng cho mọi module; `MediaPickerTest` | [media.md](media.md) |
| 4 | MediaFolder | ✅ Hoàn thành | ✅ `media_folders` (8 cột) | Thư mục Media | [media-folder.md](media-folder.md) |

### Tầng 1 — Nền tảng

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 5 | Language | ✅ Hoàn thành | ✅ `languages` (10 cột) | Có rule bảo vệ default language + cache; smoke 200; chưa có test riêng | [language.md](language.md) |
| 6 | Setting | ✅ Hoàn thành | ✅ `settings` (10 cột) | Cấu hình theo group, đa ngôn ngữ; `SettingEncodingTest` 2/2 | [setting.md](setting.md) |

### Tầng 2 — CMS

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 7 | PostCategory | ✅ Hoàn thành | ✅ `post_categories` + translations | Có test Unit + Feature | [post-category.md](post-category.md) |
| 8 | Post | ✅ Hoàn thành | ✅ `posts` + translations + `post_tag` | `PostCrudTest` + `PostServiceTest` + `PostRepositoryTest` | [post.md](post.md) |
| 9 | Page | ✅ Hoàn thành (code) | ✅ `pages` + translations | **Vẫn chưa có test** — spec 30 case trong `15-testing` §6, hạng mục còn mở | [page.md](page.md) |
| 10 | Tag | ✅ Hoàn thành | ✅ `tags` + `post_tag` + **`product_tag`** | Dùng chung cho Post + Product (P2); `TagCrudTest` 5/5 | [tag.md](tag.md) |
| 11 | Banner | ✅ Hoàn thành | ✅ `banners` + `banner_translations` | Bootstrap 5.3 theo mẫu Post; `BannerCrudTest` 5/5 (nâng từ 🟡 sau kiểm kê 11/2026) | [banner.md](banner.md) |
| 12 | Menu | ✅ Hoàn thành | ✅ `menus` + `menu_items` | Bootstrap 5.3 theo mẫu Post; `MenuCrudTest` 5/5 (nâng từ 🟡 sau kiểm kê 11/2026) | [menu.md](menu.md) |

### Tầng 3 — Danh mục sản phẩm

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 13 | Category (danh mục SP) | ✅ Hoàn thành | ✅ `categories` + translations | `CategoryCrudTest` + `RegressionFixesTest`; đã xóa orphan `categorys` | [category.md](category.md) |
| 14 | Brand | ✅ Hoàn thành | ✅ `brands` + `brand_translations` | `BrandCrudTest` 5/5 (nâng từ 🟡 sau kiểm kê 11/2026) | [brand.md](brand.md) |
| 15 | ProductAttribute | ✅ Hoàn thành | ✅ `product_attributes` (+ product_id) + translations + values | `ProductAttributeCrudTest` + `ProductAttributeValueTest` + `ProductAttributeVariantTest` | [product-attribute.md](product-attribute.md) |

### Tầng 4 — Sản phẩm

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 16 | Product | ✅ Hoàn thành | ✅ `products` (+13 cột P2: product_type, dims riêng, shipping/tax, inventory policy, primary_image; **đã drop cột `dimensions` legacy 10/2026**) + translations + images + variants + pivots + **`product_tag`** | CRUD + thuộc tính + biến thể + form revamp P0–P2 + nâng cấp kiến trúc P0–P3 10/2026; test: `ProductCrudTest`, `ProductModuleFixTest` (con số "180 case" cũ đã bỏ vì không đếm được) | [product.md](product.md) |
| 17 | ProductVariant | ✅ Hòa vào Product | ✅ `product_variants` (+ barcode, cost_price, image) + pivots | Không còn module riêng — quản lý trong form Product (kiểu WP); `ProductAttributeVariantTest` | [product-variant.md](product-variant.md) |
| 18 | ProductReview | ✅ Hoàn thành | ✅ `product_reviews` (uuid, rating, status, softDeletes) | `ProductReviewCrudTest` 6/6 PASS, enum `ReviewStatus` | [product-review.md](product-review.md) |

### Tầng 5 — Giao dịch

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 19 | Cart | ✅ Hoàn thành | ✅ `carts` (**11 cột**) + `cart_items` (**11 cột**, unique `(cart_id, line_key)`) | API v1 qua `X-Cart-Token`, CartPricingService realtime, Merge giỏ, Admin Read-Only UI, commands dọn dẹp; 27 test (8 admin + 10 API + 4 merge + 2 command + 3 pricing) | [cart.md](cart.md) |
| 20 | Coupon | ✅ Hoàn thành | ✅ `coupons` + `coupon_users` | Validation & rollback tự động khi hủy đơn; 16 test (`CouponCrudTest` 8 + `CouponValidationServiceTest` 8) | [coupon.md](coupon.md) |
| 21 | FlashSale | ✅ **Hoàn thành Phase 0–2** (Phase 2: 2026-11-03) | ✅ `flash_sales` + `flash_sale_translations` + `flash_sale_products` + `flash_sale_redemptions` (10 cột) + `order_items.flash_sale_product_id` | **Phase 2 đã tích hợp thật**: `FlashSalePriceResolver` áp giá flash vào `CartPricingService`/`CheckoutService`; `ReserveFlashSaleQuotaAction` + `ReleaseFlashSaleQuotaAction` ghi/hoàn `flash_sale_redemptions`; API `GET /api/v1/flash-sales/active`; cart API trả cờ `is_flash_sale`. Rule đã chốt: không cộng dồn coupon, giá tính lại lúc checkout, reserve khi đặt hàng. Test: Admin 13 + `FlashSaleCheckoutTest` 12. **Còn mở:** chưa có job auto-hủy đơn `pending` quá hạn → suất flash chỉ release khi đơn bị hủy | [flash-sale.md](flash-sale.md) |

### Tầng 6 — Đơn hàng & Thanh toán

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 22 | Order | ✅ Hoàn thành | ✅ `orders` (**23 cột**) + `order_items` + `shipping_addresses` + `order_status_histories` | State machine `OrderStatus` trong `OrderService::updateStatus`; `OrderCrudTest` 8 + `OrderCancelRestoresStockTest` 1 | [order.md](order.md) |
| 23 | PaymentMethod | ✅ Hoàn thành | ✅ `payment_methods` (**14 cột**, AES-256 mã hóa credentials, composite index) | Registry 8 drivers (COD, Bank Transfer, VNPay, SePay, PayOS, MoMo, Stripe, PayPal) + caching; `PaymentMethodCrudTest` 13 + Unit 15 | [payment-method.md](payment-method.md) |
| 24 | Payment | ✅ Engine / ✅ Admin index (sửa 11/2026) | ✅ `payments` (**13 cột**) — **UNIQUE index** `(gateway_code, gateway_transaction_id)` từ migration 2026_11_03_000001 | Webhook đa cổng + checkout flow: `PaymentWebhookTest` 7 + `CheckoutTest` 8 + `MoneyModuleAuthorizationAndIdempotencyTest` 6 PASS. **11/2026:** `/admin/payments` từng 500 → đã sửa + viết index thật theo chuẩn `x-admin`; idempotency đã siết xuống tầng DB; route đã gắn `can:payments.*`. **Còn mở:** `CheckoutController::handleReturn` mới là stub (chỉ echo payload) | [payment.md](payment.md) |
| 30 | SeoMetadata | ✅ Hoàn thành (mới ghi nhận 11/2026) | ✅ `seo_metadata` | Trước đây **không có dòng nào trong tracker**: model `SeoMetadata` + trait `HasSeo` + component `x-admin.seo-meta` (dùng ở 6 form) + `MigrateSeoDataCommand` | — |

### Tầng 7 — Logging & tồn kho

| # | Module | Trạng thái | DB schema | Ghi chú | Chi tiết |
|---|---|---|---|---|---|
| 25 | Warehouse | 🚧 Shell (menu đã ẩn) | ❌ `warehouses` = 3 cột (`id`, `timestamps`) | **11/2026:** `/admin/warehouses` từng 500 → đã hotfix, trang hiển thị cảnh báo "module chưa được triển khai"; link đã tạm ẩn ở sidebar. Create/Edit **chưa dùng được** (bảng không có `uuid`/field nghiệp vụ) | [warehouse.md](warehouse.md) |
| 26 | Inventory | 🚧 Shell (menu đã ẩn) | ❌ `inventories` = 3 cột; **thiếu `inventory_histories`** | Như Warehouse: hotfix 500 + ẩn menu. Phụ thuộc Warehouse (#25) làm trước | [inventory.md](inventory.md) |
| 27 | ActivityLog | ⬜ Chưa bắt đầu | ❌ thiếu `activity_logs` | Observer tự ghi, không cần UI; làm **cuối** vì phụ thuộc mọi module | [activity-log.md](activity-log.md) |
| 28 | LoginHistory | ⬜ Chưa bắt đầu | ❌ thiếu `login_histories` | Nhỏ, phụ thuộc Auth (đã xong) | [login-history.md](login-history.md) |
| 29 | Notification | ⬜ Chưa bắt đầu | ❌ thiếu `notifications`, `email_templates`, `sms_templates` | **4 event nghiệp vụ đang dispatch nhưng 0 listener** (`php artisan event:list`) → khách không nhận email khi thanh toán thành công/hủy đơn | [notification.md](notification.md) |

**Tổng (đợt 2026-11-03):** 24/29 module có code đầy đủ. **29/29 khai báo đã khớp thực tế**: 3 trang admin 500 đã sửa,
Payments có index thật, và FlashSale không còn "khai ✅ nhưng khách không mua được giá flash" (Phase 2 đã tích hợp).
2 module shell (Warehouse, Inventory) được đánh dấu 🚧 trung thực + ẩn menu; 3 module chưa bắt đầu (ActivityLog, LoginHistory, Notification).
Điểm còn overstated duy nhất: FlashSale **chưa có job auto-hủy đơn pending quá hạn**, nên suất flash bị giữ tới khi đơn bị hủy.

---

## Lỗi cấu trúc cần sửa (phát hiện qua audit)

| # | Lỗi | Vị trí | Mức độ | Cách sửa |
|---|---|---|---|---|
| L1 | **Thư mục view viết sai chính tả** `categorys` (orphan — controller dùng `admin.categories.*`) | `resources/views/admin/categorys/` | 🟢 Đã sửa | Đã xóa thư mục `categorys/` (đã có `categories/` đúng) |
| L2 | **Model rỗng không `$fillable`** → mass-assignment mất dữ liệu / SQL error | `app/Models/Tag.php` (các module shell khác tương tự) | 🟡 TB | `Tag` đã có `$fillable` + `HasUuid`; `Warehouse` vẫn `$guarded = []` và không có cột nghiệp vụ |
| L3 | **Binding chết (comment)** trỏ tới class không tồn tại | `RepositoryServiceProvider:50-54` | 🟢 Đã sửa | Đã xóa trong đợt trước |
| L4 | **Service không kế thừa `BaseService`** + dùng raw `DB::beginTransaction()` thay vì `handleTransaction()` | `app/Services/Admin/Language/LanguageService.php` | 🟡 TB | Chuyển sang `BaseService` + `handleTransaction` |
| L5 | ~~Permission `pages.bulk` chưa seed / chưa enforce~~ | `docs/04-permissions.md` | 🟢 Đã sửa | Pha 6: bulk gate bằng quyền của action qua `BulkActionRegistry` |
| L6 | **Module shell có UI nhưng DB rỗng** — người dùng mở trang sẽ gặp lỗi | 2 module 🚧 (Warehouse, Inventory) + 3 controller Admin | 🟢 **Đã sửa 2026-11-03** | Hotfix `compact()` sai hoa/thường ở `PaymentController`/`WarehouseController`/`InventoryController`, viết lại 3 view index, ẩn menu sidebar khối "Kho hàng" (`layouts/admin.blade.php`, bọc `@if(false)`), thêm `PaymentAndShellIndexTest` (5 test) |
| L7 | **Trang Payments index là placeholder** ("Dữ liệu đang được cập nhật...") dù bảng đã có dữ liệu | `resources/views/admin/payments/index.blade.php` | 🟢 Đã sửa 2026-11-03 | Viết lại bằng `x-admin.page-header` + `x-admin.table` + `x-admin.badge`, có đơn hàng/cổng/số tiền/trạng thái/thời gian |
| L8 | **4 registry lõi còn rỗng** (`ExportRegistry`, `SearchRegistry`, `WidgetRegistry`, `SidebarMenuRegistry`) — bind singleton nhưng TODO | `app/Core/...` | 🟡 TB | Chưa có trong tracker; cần quyết định làm thật hay gỡ binding |

---

## Bảng migration còn thiếu (theo `docs/02-database-overview.md`)

`transactions`, `activity_logs`,
`login_histories`, `notifications`, `inventory_histories`, `email_templates`, `sms_templates`, `product_wishlists`.

> `product_variant_attributes` tracker nói đã bỏ, nhưng **vẫn còn bảng này trong DB dev** (kiểm kê 11/2026) — cần xác nhận rồi mới drop.
> Thay bằng pivot `product_variant_attribute_values` (xem `03-database-details.md`).

---

## Thứ tự ưu tiên thực hiện (dựng lại 2026-11-03, theo dependency)

1. ~~Sửa L6/L7 (3 trang admin 500)~~ — 🟢 xong 2026-11-03 (test `PaymentAndShellIndexTest` 5/5, smoke dev DB 200).
2. ~~**Seed `payment_methods` + dữ liệu demo**~~ — 🟢 xong 2026-11-03: `PaymentMethodSeeder` tạo 8 cổng khớp registry, COD + VietQR `is_active = true`; đã chạy trên dev DB (`payment_methods=8`, `active=2`).
3. ~~**RBAC cho module tiền**~~ — 🟢 xong 2026-11-03: thêm permission `orders.delete`, `payments.*`, `payment-methods.*`, `warehouses.*`, `inventories.*` + gắn `can:` cho 5 nhóm route trong `routes/web.php`. Super admin không bị khóa (`Gate::before` cho `is_admin`). **Chưa làm:** nhóm 7.3 — permission "ma" `orders.refund`, `orders.export`, `reports.*`, `logs.*`, `inventory.*`.
4. **Notification + email/SMS templates + listener** cho `PaymentSucceeded`, `PaymentFailed`, `OrderCancelled`, `ReviewApproved`, `LowStockThresholdReached` — **module nên làm tiếp theo**.
5. ~~**FlashSale Phase 2**~~ — 🟢 **xong 2026-11-03** sau khi chủ dự án chốt 3 rule: áp giá flash vào `CartPricingService`/`CheckoutService`
   (`FlashSalePriceResolver`), `ReserveFlashSaleQuotaAction` / `ReleaseFlashSaleQuotaAction` (ghi + hoàn `flash_sale_redemptions`),
   API `GET /api/v1/flash-sales/active`. **Còn mở:** job auto-hủy đơn `pending` quá hạn để hoàn suất flash, và listener cho `PaymentFailed`.
6. **Warehouse → Inventory** (schema thật + `inventory_histories` + đồng bộ `StockService`), chạy tuần tự vì Inventory phụ thuộc Warehouse.
7. **Còn lại của hardening:** ~~unique index DB-level cho `(gateway_code, gateway_transaction_id)`~~ (🟢 xong 2026-11-03) — **`handleReturn`** thật (xác thực + redirect) vẫn mở.
8. **Reports & analytics** (permission `reports.*` đã seed nhưng chưa có code) — để sau khi có dữ liệu đơn thật.
9. **Page tests** (30 case) và **LoginHistory** (nhỏ).
10. **ActivityLog** — Observer phủ mọi module nên làm **cuối cùng**.
11. Hạ tầng production: Redis/Memcached, queue worker, rate limit cho `/checkout` + webhook (hiện chỉ cart được throttle).

> Roadmap chi tiết theo từng Pha: [`roadmap_next_steps.md`](../../roadmap_next_steps.md) (khôi phục 11/2026).
> Quy trình build từng module: [`docs/architecture/11-module-tutorial.md`](../architecture/11-module-tutorial.md).
> Chọn Profile (Full/Simple): [`docs/07-development-process.md`](../07-development-process.md) §1.5.
> Không tạo file trước khi schema sẵn sàng: [`docs/07-development-process.md`](../07-development-process.md) §1.6.
