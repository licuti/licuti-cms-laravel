# Module: FlashSale

> ✅ **Hoàn thành Admin CRUD (Phase 0 + Phase 1)** | Profile: Full | Tầng 5 — Giao dịch
> Route: `admin.flash-sales.*` | Views: `resources/views/admin/flash-sales/`
> Spec: [`07` §3.3](../07-development-process.md) (MODULE: Coupons & Flash Sales)

---

## 1. Hiện trạng & Cấu trúc Dữ liệu (Cập nhật 10/2026)

Hệ thống Flash Sale (chiến dịch giảm giá số lượng có hạn theo khung giờ) đã hoàn thành **Phase 0 (Hotfix Deadlock & Non-blocking Cart Pricing)** và **Phase 1 (Admin CRUD Full Profile + Modal Product Picker + Test Suite 10/10 PASS)** theo đúng kiến trúc **Service - Repository - DTO** và quy chuẩn [AGENTS.md](../../.agents/AGENTS.md).

### 1.1 Cơ sở dữ liệu (5 migrations đã chạy)

| Migration | Bảng / Thao tác | Chi tiết cột & khóa |
|---|---|---|
| `2026_10_28_000001` | `flash_sales` | `id`, `uuid` (unique), `starts_at` (datetime), `ends_at` (datetime), `is_active` (boolean, default 1), `created_by` (FK nullable), `deleted_at` (SoftDeletes), `timestamps`. Index: `(is_active, starts_at, ends_at)`. |
| `2026_10_28_000002` | `flash_sale_translations` | `id`, `flash_sale_id` (FK cascade), `locale` (varchar 5), `name` (varchar 255), `description` (text, nullable), `timestamps`. Unique: `(flash_sale_id, locale)`. |
| `2026_10_28_000003` | `flash_sale_products` | `id`, `flash_sale_id` (FK cascade), `product_id` (FK cascade), `product_variant_id` (FK cascade, nullable), `variant_key` (varchar 64), `flash_price` (decimal 15,2), `quantity_limit` (unsignedInt), `sold_quantity` (unsignedInt, default 0), `limit_per_user` (unsignedSmallInt, default 0), `sort_order` (unsignedInt, default 0), `timestamps`. Unique: `(flash_sale_id, variant_key)` chống duplicate SP/biến thể. |
| `2026_10_28_000004` | `flash_sale_redemptions` | `id`, `flash_sale_product_id` (FK cascade), `order_item_id` (FK nullable), `user_id` (FK nullable), `guest_email` (varchar 100, nullable), `quantity` (unsignedInt), `status` (varchar 20, default 'reserved'), `released_at` (datetime, nullable), `timestamps`. Index: `(flash_sale_product_id, user_id, status)`, `(flash_sale_product_id, guest_email, status)`. |
| `2026_10_28_000005` | `order_items` (alter) | Bổ sung cột `flash_sale_product_id` (FK nullable → `flash_sale_products.id` on delete set null) để truy vết nguồn gốc giảm giá Flash Sale trên từng dòng đơn hàng. |

---

## 2. Các Lớp Kiến trúc (Architecture Layers)

### 2.1 Enums
- **`app/Enums/FlashSaleStatus.php`**:
  - Các trạng thái tính toán động theo thời gian thực: `UPCOMING` (Sắp diễn ra), `RUNNING` (Đang diễn ra), `ENDED` (Đã kết thúc), `INACTIVE` (Tạm ngưng).
  - Helper `label()` đa ngôn ngữ qua `__()`.
  - Helper `badgeClass()` chuẩn Bootstrap 5.3 kèm hiệu ứng pulse animation cho chiến dịch đang chạy.
- **`app/Enums/RedemptionStatus.php`**:
  - `RESERVED`, `COMPLETED`, `CANCELLED`, `RELEASED`.

### 2.2 Models
- **`app/Models/FlashSale.php`**:
  - Trait: `HasFactory`, `HasUuid`.
  - Casts: `starts_at => datetime`, `ends_at => datetime`, `is_active => boolean`, `max_qty_per_user => integer`, `sort_order => integer`.
  - Quan hệ: `translations(): HasMany`, `products(): HasMany`, `redemptions(): HasMany`.
  - Scopes: `scopeActive()`, `scopeRunning()`, `scopeUpcoming()`, `scopeEnded()`, `scopeSearch()`.
  - Accessors: `name`, `description`, `computed_status` (trả về `FlashSaleStatus`).
  - Helper: `isRunning(): bool`.
- **`app/Models/FlashSaleTranslation.php`**: Model đa ngôn ngữ (`locale`, `name`, `description`).
- **`app/Models/FlashSaleProduct.php`**:
  - Casts: `original_price => decimal:2`, `flash_price => decimal:2`, `quantity_limit => integer`, `sold_quantity => integer`.
  - Helper: `makeVariantKey(?int $variantId): string`.
  - Accessor: `remaining_quantity`, `discount_percent`.
  - Quan hệ: `flashSale(): BelongsTo`, `product(): BelongsTo`, `variant(): BelongsTo`, `redemptions(): HasMany`.
- **`app/Models/FlashSaleRedemption.php`**: Lưu vết giữ chỗ và thanh toán quota.
- **`app/Models/OrderItem.php`**: Bổ sung quan hệ `flashSaleProduct(): BelongsTo`.

### 2.3 Hotfix Deadlock & Non-blocking Cart Pricing (Phase 0)
- **`app/Enums/CartIssue.php`**:
  - Chuyển `PRICE_CHANGED` thành non-blocking (`isBlocking() === false`), không chặn user tiếp tục thanh toán khi giá cập nhật.
- **`app/Exceptions/Cart/PriceChangedException.php`**:
  - Custom Exception (HTTP 409 Conflict) mang payload `{expected_total, current_total}`.
- **`app/Actions/Order/PlaceOrderAction.php`**:
  - Sắp xếp items A-Z theo `[product_id, variant_id]` trước khi chạy `lockForUpdate()` để triệt tiêu hoàn toàn nguy cơ Deadlock khi nhiều user cùng đặt hàng cùng lúc.
  - Thiết lập transaction retry `attempts: 3`.

### 2.4 DTO & FormRequests
- **`app/DTOs/FlashSale/FlashSaleDTO.php` & `FlashSaleProductItemDTO.php`**:
  - Strongly-typed properties, tự động validate và chuẩn hóa payload từ Request.
- **`app/Http/Requests/Admin/FlashSale/StoreFlashSaleRequest.php`**:
  - Validate đa ngôn ngữ, validate thời gian (`ends_at > starts_at >= now - 5m`), validate danh sách sản phẩm (tối thiểu 1 sản phẩm, `flash_price <= original_price`).
- **`app/Http/Requests/Admin/FlashSale/UpdateFlashSaleRequest.php`**:
  - Khóa không cho đổi `starts_at` đối với chiến dịch đã bắt đầu/đang chạy (bảo vệ tính toàn vẹn dữ liệu).
  - Tự động bổ sung `prepareForValidation()` để giữ nguyên `starts_at` khi input HTML ở trạng thái disabled.

### 2.5 Repository & Service
- **`app/Repositories/Interfaces/FlashSaleRepositoryInterface.php` & `FlashSaleRepository.php`**:
  - `getFilteredPaginated(array $filters, int $perPage)`.
  - `getStatusCounts()`: Thống kê số lượng chiến dịch theo từng trạng thái bằng 1 câu `GROUP BY` tối ưu O(1).
  - `findById(int $id)`, `findByUuid(string $uuid)`, `create()`, `update()`, `delete()`, `bulkDelete()`.
- **`app/Services/Admin/FlashSale/FlashSaleService.php`**:
  - Kế thừa `BaseService`.
  - Điều phối lưu trữ translations và danh sách `flash_sale_products` trong `DB::transaction()`.
  - Tự động đồng bộ `variant_key` và loại bỏ trùng lặp sản phẩm/biến thể.
  - Quản lý bộ đệm cache cho status counts.

---

## 3. Giao diện Admin (Bootstrap 5.3 — Tuân thủ /admin-ui)

| View file | Chuẩn thiết kế & tính năng |
|---|---|
| `resources/views/admin/flash-sales/index.blade.php` | - **Kiểu A (Trang danh sách chuẩn)**.<br>- Header chuẩn: Title, Subtitle, nút "Thêm chiến dịch" primary kèm icon `<i class="bi bi-plus-lg"></i>`.<br>- Tab đếm số lượng chiến dịch (`Tất cả`, `Đang diễn ra`, `Sắp diễn ra`, `Đã kết thúc`, `Tạm ngưng`) kèm badge số lượng.<br>- Bulk action toolbar (chọn tất cả checkbox, xóa nhiều bản ghi).<br>- Bộ lọc từ khóa tìm kiếm kèm nút `<x-admin.button size="sm">Lọc</x-admin.button>` (căn chuẩn hàng ngang 31px baseline).<br>- Bảng dữ liệu có cột chiến dịch (tên + thời gian), số lượng sản phẩm, badge trạng thái kèm chấm live pulse animation cho chiến dịch đang chạy, công tắc toggle bật/tắt nhanh, nút sửa/xóa.<br>- Empty state đẹp mắt khi không tìm thấy kết quả. |
| `resources/views/admin/flash-sales/form.blade.php` | - **Kiểu B (Trang form 2 cột 9-3 chuẩn)**.<br>- Cột chính (col-lg-9):<br>&nbsp;&nbsp;+ Card thông tin chiến dịch có tab chuyển đổi ngôn ngữ client-side (`<x-admin.lang-tabs>`) không reload trang.<br>&nbsp;&nbsp;+ Card danh sách sản phẩm tham gia Flash Sale: Nút "Chọn sản phẩm" kích hoạt Modal picker, bảng sản phẩm đã chọn (hình ảnh, tên, SKU, giá gốc, ô nhập giá sale tự tính % giảm, ô nhập số lượng bán kèm nút xóa từng dòng).<br>- Cột phụ (col-lg-3):<br>&nbsp;&nbsp;+ `<x-admin.publish-box>` cố định ở đầu sidebar: Trạng thái công tắc hoạt động, nút Hủy, nút Lưu (icon loading khi submit).<br>&nbsp;&nbsp;+ Card khung thời gian chạy: `starts_at` và `ends_at` (có cảnh báo tự động khóa khi chiến dịch đang chạy).<br>&nbsp;&nbsp;+ Card cấu hình nâng cao: Giới hạn số lượng mua/khách (`max_qty_per_user`), thứ tự ưu tiên (`sort_order`).<br>- **Modal chọn sản phẩm (`modal-lg` 800px)**: Hỗ trợ tìm kiếm realtime, chọn từng dòng có highlight `table-active`, checkbox chọn tất cả `#modal-check-all`, thanh footer hiển thị số lượng SP đã chọn động và tự động áp dụng mức giảm 20% mặc định. |

---

## 4. Kiểm thử Tự động & Thực tế (Testing)

### 4.1 Automated Tests (Feature Tests)
File test: `tests/Feature/Admin/FlashSaleAdminTest.php` (**10/10 test cases PASS**, 29 assertions):
1. `guest_cannot_access_flash_sales`: Chặn khách chưa đăng nhập.
2. `admin_can_view_flash_sales_index`: Admin truy cập danh sách chiến dịch thành công.
3. `admin_can_view_create_flash_sale_page`: Hiển thị form tạo mới.
4. `admin_can_store_flash_sale_with_translations_and_products`: Lưu chiến dịch mới, lưu translations song ngữ, đồng bộ sản phẩm và sinh `variant_key`.
5. `store_validation_fails_when_ends_at_is_before_starts_at`: Chặn lưu khi thời gian kết thúc trước thời gian bắt đầu.
6. `store_validation_fails_when_flash_price_exceeds_original_price`: Chặn lưu khi giá flash sale cao hơn giá gốc.
7. `admin_can_update_flash_sale`: Cập nhật thông tin chiến dịch thành công.
8. `admin_can_toggle_flash_sale_status`: Đổi trạng thái hoạt động nhanh qua toggle.
9. `admin_can_delete_flash_sale`: Xóa chiến dịch thành công.
10. `admin_can_bulk_delete_flash_sales`: Xóa hàng loạt nhiều chiến dịch.

Toàn bộ test suite dự án: **308 tests PASS (1063 assertions)**.

### 4.2 Kiểm thử giao diện thực tế (Live Browser Verification via Chrome DevTools MCP)
- Kiểm tra trực quan trang danh sách `http://licuti-cms-laravel.test/admin/flash-sales`: Layout chuẩn, nút `Lọc` thẳng hàng 31px với ô input tìm kiếm.
- Kiểm tra trực quan modal chọn sản phẩm trên form: Modal rộng `modal-lg` chuẩn Bootstrap 5.3 (800px), tìm kiếm sản phẩm mượt mà, chọn tất cả hoạt động chính xác, thêm sản phẩm vào bảng form mượt mà.
- Kiểm tra chuyển tab đa ngôn ngữ Tiếng Việt / English: Chuyển mượt mà, lưu chính xác bản dịch vào DB.

---

## 5. Kế hoạch Giai đoạn tiếp theo (Phase 2 — Storefront & Engine)

1. **Redis/Cache Storefront Lookup O(1)**: Lưu cache danh sách variant đang flash sale (`flash_sale:variant:{id}`) để Cart & Product Detail lookup trong `1ms`.
2. **Atomic Quota Reservation Action (`ReserveFlashSaleQuotaAction`)**: Giữ chỗ số lượng flash sale an toàn trong `PlaceOrderAction` (chống oversell tuyệt đối khi flash sale hot hàng nghìn request/giây).
3. **Idempotent Release Action (`ReleaseFlashSaleQuotaAction`)**: Tự động hoàn trả số lượng khi đơn hàng hết hạn thanh toán hoặc bị hủy.
4. **Storefront API (`/api/v1/flash-sales/active`)**: Endpoint công khai cho trang chủ & banner đếm ngược.
