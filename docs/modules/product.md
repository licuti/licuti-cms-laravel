# Module: Product

> ✅ **Hoàn thành (CRUD + Biến thể)** | Profile: Full | Tầng 4 — Sản phẩm
> Route: `admin.products.*` | Views: `resources/views/admin/products/`
> Spec: [`07` §3.2](../07-development-process.md) (MODULE: Products) — **module phức tạp nhất, build cuối nhóm Catalog**

## Hiện trạng (audit 09/2026, cập nhật sau đợt thuộc tính & biến thể)

| Hạng mục | Trạng thái |
|---|---|
| Migration `products` / `product_translations` | ✅ Đầy đủ (uuid, category_id, brand_id, sku, barcode, price, compare_price, cost_price, stock_quantity, track_inventory, weight, dimensions, is_featured, is_active, status, published_at, soft-delete) |
| Migration `product_variants`, pivot `product_attribute` / `product_attribute_value` / `product_variant_attribute_values` | ✅ Đầy đủ + logic service |
| Migration `product_images` | ✅ |
| Models (`Product`, `ProductTranslation`, `ProductImage`, `ProductVariant`, `ProductReview`, `ProductAttribute*`) | ✅ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ |
| Test | ✅ `ProductCrudTest` 14 case + `ProductAttributeVariantTest` 10 case (CRUD, tab filter, slug unique, SKU unique, authorization, image gallery, sinh/dỡ biến thể, custom attribute, guard nổ tổ hợp) |

## Đã làm trong đợt thuộc tính & biến thể (Pha 7)

- **Thuộc tính trong form Product**: component `x-admin.product-attributes` — thêm thuộc tính từ catalog toàn cục (dropdown), tạo thuộc tính tùy chỉnh per-product qua AJAX (`POST admin/products/{uuid}/attributes`), chọn giá trị dạng chips (color swatch khi `type=color`), toggle "Dùng cho biến thể" (`is_variation`).
- **Biến thể tự sinh**: component `x-admin.product-variants` — cartesian product các attribute `is_variation`, render bảng `sku/price/compare_price/stock_quantity/is_active`; preserve input đã nhập theo combo key khi thêm/bỏ giá trị; server render sẵn variant hiện có khi edit.
- **Service**: `ProductService::syncAttributes()` (sync pivot `product_attribute` + `product_attribute_value`), `ProductService::generateVariants()` (preserve-by-combo, guard > 100 combos → `ValidationException`), `ProductService::createCustomAttribute()` (thuộc tính custom `product_id`-scoped).
- **Custom attribute scoping**: `product_attributes.product_id` nullable — `NULL` = catalog toàn cục, `= X` = custom của product X. `ProductAttributeRepository::getActivePaginated` + `getActiveWithValues` lọc `whereNull('product_id')`; `getAvailableForProduct(id)` lấy catalog + custom của product.
- **Xóa shell `admin.product-variants.*`** (route/controller/service/DTO/repo/views/binding) — hòa biến thể vào form Product như quyết định thiết kế.

## Đã làm trong đợt stabilize (Pha 6+)

- **Tab filter sửa**: `ProductRepository::getActivePaginated` lọc theo `tab` (trước đó lọc `status` — không khớp `x-admin.filter-tabs`, click tab không có tác dụng).
- **Slug uniqueness**: `ProductService` tạo/sửa dùng `generateUniqueSlug('product_translations', ...)` như Post/Page/Category (trước đó dùng `Str::slug()` thô, trùng tên → trùng slug).
- **Authorization**: `StoreProductRequest` → `products.create`, `UpdateProductRequest` → `products.update` (qua `AuthorizesWithPermission`); bulk action gate qua `BulkActionRegistry` (`delete` → `products.delete`, đổi trạng thái → `products.update`); custom attribute endpoint → `products.update`.

## Đã làm trong đợt sửa lỗi & nâng cấp (09/2026)

> Theo kế hoạch `.kilo/plans/1790318241202-product-module-fix-plan.md` — đánh giá 10 khía cạnh, fix 3 bug hoạt động + hardening.

**Bug nghiêm trọng:**

- **Update sản phẩm có biến thể bị reject** — `UpdateProductRequest::validateVariantSkus()` check unique SKU biến thể mà không ignore SKU hiện có của chính product đó → submit lại form edit không đổi SKU luôn báo "đã tồn tại". Fix: truyền danh sách SKU biến thể hiện có vào check (verify test `ProductModuleFixTest`).
- **Dropdown Category rỗng** — view dùng `$cat->name` nhưng model Category không có accessor `name` (chỉ có `translated_name`), lại không eager load `translations`. Fix: đổi `translated_name` + `all(['*'], ['translations'])` ở cả index filter, bảng danh sách và form.
- **Custom attribute trùng code → HTTP 500 rò rỉ SQL** — thiếu validate `unique:product_attributes,code`. Fix: thêm rule + message thân thiện (422).

**Hardening bảo mật & validation:**

- **XSS `color_code`** — JS `chipInner()` nối `color_code` thẳng vào HTML. Fix: `escapeHtml()` trong JS + rule `regex:/^#[0-9a-fA-F]{6}$/` ở `StoreCustomAttributeRequest`.
- **Ownership scoping** — product B từng attach custom attribute/value của product A được. Fix: validate trong `BaseProductRequest` — attribute phải là catalog hoặc thuộc product đang sửa; value phải thuộc đúng attribute.
- **Read authorization** — các route GET (`index`/`create`/`edit`) không check `products.view`. Fix: thêm middleware `can:products.view` trong `routes/web.php`.
- `destroy()` không còn đẩy `$e->getMessage()` ra response (log + message chung).

**DRY & refactor:**

- `StoreProductRequest` + `UpdateProductRequest` gộp phần chung vào abstract `BaseProductRequest` (`sharedRules`, `sharedMessages`, `validateVariantSkus`, `validateAttributeOwnership`, `defaultLocale`).
- `statuses()` + `getTabs()` dùng `ContentStatus` enum; `getTabs()` gộp 3 COUNT query thành 1 grouped query.
- `BulkActionServiceProvider` đăng ký product status action qua `ContentStatus::cases()`.
- Bỏ eager load trùng `primaryImage.media`; `getPrimaryImageUrlAttribute` đọc từ collection `images`.
- Bỏ `Str::uuid()` thừa (HasUuid tự sinh); SKU prefix + `max_combos` đưa vào `config/products.php`; JS lấy default locale từ data attribute thay hardcode `vi`.

**Database:**

- Migration `2026_09_25_000000_add_product_translation_indexes`: dedupe + unique `(product_id, locale)` và `(attribute_id, locale)`; index `slug`, `locale`, `products.status`, `products.is_featured`, `product_attribute_values.attribute_id`.

**Test & style:**

- Factory mới: `ProductFactory`, `ProductTranslationFactory`, `ProductVariantFactory`, `ProductAttributeFactory`, `ProductAttributeValueFactory`.
- Test mới: `ProductModuleFixTest` (12 case regression) + 2 case bulk product trong `BulkActionAuthorizationTest`.
- Pint fix trên toàn bộ file Product module; `composer test` chạy `pint --test --dirty` (chỉ check file đang đổi).

## Việc cần làm

- [x] **Thư viện ảnh nhiều ảnh**: component `x-admin.image-gallery` (render item có sẵn + clone qua `<template>` khi click "Thêm ảnh"); chọn ảnh đại diện bằng radio `primary_index`; DTO đọc theo convention `images[i][image]_uuid` / `_remove` (sửa luôn bug submit ảnh đơn không được lưu); service đảm bảo đúng 1 ảnh đại diện.
- [x] **Biến thể sản phẩm**: `ProductService::generateVariants` + `syncAttributes` + UI dynamic JS trong form (xem component `x-admin.product-attributes` / `x-admin.product-variants`).
- [ ] Bổ sung `ProductRepository::searchBySku`, `getOutOfStock` (theo spec `07` §3.2) nếu cần cho phần báo cáo / import.
- [ ] Frontend storefront hiển thị/chọn biến thể (chưa có module front).
- [ ] Tích hợp Cart/Order/Inventory với `product_variants` (đang là shell, FK variant chưa dùng).
- [ ] Ảnh riêng cho variant (hiện dùng gallery của product).

## Phụ thuộc

`Category` ✅ → `Brand` ✅ → `ProductAttribute` ✅ → `Product` ✅ (biến thể ✅).

