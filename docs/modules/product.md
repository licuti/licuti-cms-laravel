# Module: Product

> ✅ **Hoàn thành (CRUD + Biến thể)** | Profile: Full | Tầng 4 — Sản phẩm
> Route: `admin.products.*` | Views: `resources/views/admin/products/`
> Spec: [`07` §3.2](../07-development-process.md) (MODULE: Products) — **module phức tạp nhất, build cuối nhóm Catalog**

## Hiện trạng (audit 09/2026, cập nhật sau Product Form Revamp P0–P2)

| Hạng mục | Trạng thái |
|---|---|
| Migration `products` / `product_translations` | ✅ Đầy đủ (uuid, category_id, brand_id, sku, barcode, price, compare_price, cost_price, stock_quantity, track_inventory, weight, dimensions, **product_type, length/width/height, is_free_shipping, shipping_fee, tax_rate, is_tax_inclusive, allow_backorder, low_stock_threshold, min/max_order_quantity, sold_individually, primary_image**, is_featured, is_active, status, published_at, soft-delete) |
| Migration `product_variants`, pivot `product_attribute` / `product_attribute_value` / `product_variant_attribute_values` | ✅ Đầy đủ + logic service (variant có thêm barcode, cost_price, image) |
| Bảng `product_tag` (pivot product ↔ tag) | ✅ Unique composite, cascade delete |
| Migration `product_images` | ✅ |
| Models (`Product`, `ProductTranslation`, `ProductImage`, `ProductVariant`, `ProductReview`, `ProductAttribute*`) | ✅ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ |
| Test | ✅ `ProductCrudTest` 20 case + `ProductAttributeVariantTest` 18 case (CRUD, tab filter, slug unique, SKU unique, authorization, image gallery, sinh/dỡ biến thể, custom attribute, guard nổ tổ hợp, variant detail fields, P2 fields, tags) |

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

## Đã làm trong đợt Product Form Revamp (09/2026)

> Theo kế hoạch `.kilo/plans/1790392522834-product-form-revamp.md` — làm lại
> toàn bộ form thêm/sửa sản phẩm theo chuẩn Shopify/WooCommerce. **Đã hoàn
> thành toàn bộ phase (P0 → P2).**

### P0 — Sửa bug

- **Dropdown "Thêm thuộc tính" bị sidebar đè** (`product-attributes.blade.php`):
  bỏ `dropdown-menu-end`, đặt `--bs-dropdown-zindex: 1060` (sidebar là 1040).
- **Tách "Ảnh đại diện" thành trường riêng** (`products.primary_image`):
  migration `2026_09_26_060000` + backfill từ `product_images.is_primary`;
  model accessor `getPrimaryImageUrlAttribute()` ưu tiên cột mới, fallback
  legacy; DTO bỏ `primary_index`, gallery giờ chỉ là album; component
  `image-gallery` thêm prop `showPrimary` (default true). Form dùng
  `x-admin.image-upload name="primary_image"` tách biệt album.

### P1 — UX thuộc tính & biến thể

- **tom-select chọn giá trị thuộc tính** (P1.1): thay chip phẳng bằng
  `<select multiple>` + TomSelect (`remove_button`, `clear_button`,
  `create: true`, `createOnBlur`). Native select submit raw text → server tự
  phân loại id số vs text mới (`ProductDTO::parseAttributes`).
- **Tạo thuộc tính/giá trị mới tại trang create** (P1.2): custom attribute
  submit cùng form chính qua `new_attributes[key][name|type|is_variation|
  values[]]`; `ProductService::createNewAttributes()` tạo attribute scoped
  `product_id` sau khi model save, trả về matrix có id thật để
  `generateVariants()` sinh tổ hợp ngay trong cùng transaction. Label đổi
  thành "Dùng làm trục biến thể (sinh tổ hợp)".
- **Nâng cấp bảng biến thể** (P1.3): mở khóa cột `barcode`, `cost_price`,
  `image` đã có trên DB (migration `2026_09_28_000000` chính thức hóa);
  toolbar "Sinh SKU tự động" (prefix từ `config('products.sku_prefix')`) /
  "Áp dụng giá" / "Áp dụng tồn kho" hàng loạt; bảng scroll `max-height: 480px`
  + sticky thead; thumbnail ảnh biến thể; modal "Chi tiết biến thể"
  (`x-admin.modal` + `x-admin.image-upload`) dataBinding 2 chiều qua hidden
  input `data-key`.

### P2 — Mở rộng schema sản phẩm

- **Migration** `2026_09_28_010000_enhance_products_for_shipping_taxonomy`:
  13 cột mới — `product_type` (physical/virtual/digital), `length`/`width`/
  `height` tách riêng (backfill từ `dimensions` "20x15x10" bằng regex, log
  row lỗi không fail), `is_free_shipping`, `shipping_fee`, `tax_rate`,
  `is_tax_inclusive`, `allow_backorder`, `low_stock_threshold`,
  `min_order_quantity`, `max_order_quantity`, `sold_individually`. Bảng mới
  `product_tag` (unique composite, cascade delete).
- **Backend**: `Product::$fillable`/`$casts` + relation `tags()`;
  `Tag::products()`; `ProductDTO` thêm 13 property + `tagIds` +
  `parseTagIds()`; `ProductService::syncTags()` tạo tag mới (slug, trùng thì
  dùng tag có) + sync pivot; `BaseProductRequest` rules P2 + `tags.*`.
- **UI**: `form.blade.php` chuyển từ 5 card dọc sang **tabs ngang**
  (Bootstrap `nav-tabs`): "Thông tin chung" (lang-tabs + tên/slug/mô tả +
  SEO), "Giá & Tồn kho" (+ nhóm chính sách tồn kho/khối lượng đặt hàng),
  "Vận chuyển & Thuế" (ẩn nhóm kích thước khi virtual/digital bằng JS),
  "Thuộc tính", "Biến thể". Sidebar thêm card "Thẻ tag" — tom-select
  multiple + create, config qua `options`/`items`, giữ `old()` cả id lẫn
  text mới.

### Kiểm thử

- `php artisan test` toàn bộ → **143 passed** (481 assertions).
- Test mới: 3 test P1.3 (persistence barcode/cost_price/image qua HTTP
  endpoint + preserve khi re-render tổ hợp), 5 test P2 (render tabs/fields,
  persist P2 fields, reject product_type sai, link tag có sẵn + tạo tag mới,
  sync tag khi update không trùng lặp).
- `npm run build` PASS (145 modules, 108KB gzip JS; bundle +~40KB do
  tom-select).

## Việc cần làm

- [x] **Thư viện ảnh nhiều ảnh**: component `x-admin.image-gallery` (render item có sẵn + clone qua `<template>` khi click "Thêm ảnh"); ảnh đại diện giờ là trường riêng `primary_image` (P0.2); DTO đọc theo convention `images[i][image]_uuid` / `_remove`.
- [x] **Biến thể sản phẩm**: `ProductService::generateVariants` + `syncAttributes` + UI dynamic JS trong form (xem component `x-admin.product-attributes` / `x-admin.product-variants`).
- [x] **Ảnh riêng cho variant**: cột `product_variants.image` + modal chi tiết (P1.3).
- [x] **Thuộc tính & giá trị tạo mới tại trang create** (P1.2) + tom-select (P1.1).
- [x] **Schema mở rộng**: product_type, kích thước tách riêng, vận chuyển/thuế, chính sách tồn kho, tags (P2).
- [ ] Bổ sung `ProductRepository::searchBySku`, `getOutOfStock` (theo spec `07` §3.2) nếu cần cho phần báo cáo / import.
- [ ] Frontend storefront hiển thị/chọn biến thể (chưa có module front).
- [ ] Tích hợp Cart/Order/Inventory với `product_variants` (đang là shell, FK variant chưa dùng).
- [ ] Frontend storefront dùng `is_filterable` làm bộ lọc tìm kiếm sản phẩm.

## Phụ thuộc

`Category` ✅ → `Brand` ✅ → `ProductAttribute` ✅ → `Product` ✅ (biến thể ✅).

