# Module: Product

> ✅ **Hoàn thành (CRUD + Biến thể)** | Profile: Full | Tầng 4 — Sản phẩm
> Route: `admin.products.*` | Views: `resources/views/admin/products/`
> Spec: [`07` §3.2](../07-development-process.md) (MODULE: Products) — **module phức tạp nhất, build cuối nhóm Catalog**

## Hiện trạng (audit 09/2026, cập nhật sau Product Form Revamp P0–P2)

| Hạng mục | Trạng thái |
|---|---|
| Migration `products` / `product_translations` | ✅ Đầy đủ (uuid, category_id, brand_id, sku, barcode, price, compare_price, cost_price, stock_quantity, track_inventory, weight, **product_type, length/width/height, is_free_shipping, shipping_fee, tax_rate, is_tax_inclusive, allow_backorder, low_stock_threshold, min/max_order_quantity, sold_individually, primary_image**, is_featured, is_active, status, published_at, soft-delete; cột `dimensions` legacy đã drop 10/2026) |
| Migration `product_variants`, pivot `product_attribute` / `product_attribute_value` / `product_variant_attribute_values` | ✅ Đầy đủ + logic service (variant có thêm barcode, cost_price, image) |
| Bảng `product_tag` (pivot product ↔ tag) | ✅ Unique composite, cascade delete |
| Migration `product_images` | ✅ |
| Models (`Product`, `ProductTranslation`, `ProductImage`, `ProductVariant`, `ProductReview`, `ProductAttribute*`) | ✅ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ |
| Test | ✅ `ProductCrudTest` 26 case + `ProductAttributeVariantTest` 21 case + `ProductModuleFixTest` 12 case + unit `ProductServiceTest`/`StockServiceTest`/`ProductObserverTest`/`ProductAttributeRepositoryTest` (CRUD, tab filter, slug unique, SKU unique, authorization, image gallery, sinh/dỡ biến thể, custom attribute, guard nổ tổ hợp, variant detail fields, P2 fields, tags, combo key interleave, N+1 media, enum, cache, stock atomic) |

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

## Đã làm trong đợt nâng cấp chuẩn kiến trúc (10/2026)

> Theo kế hoạch `.kilo/plans/1790933375790-product-module-upgrade-p0-p3.md` —
> đưa module Product về 100% chuẩn kiến trúc docs + đặt groundwork scalability
> cho Tầng 5–6 (Cart/Order/Inventory). **Hoàn thành toàn bộ P0 → P3.**

### P0 — Sửa bug dữ liệu

- **P0.1 Canonicalize variant combo key** (`ProductService::generateVariants()`):
  combo key PHP chưa sort numeric khi value id của 2 attribute đan xen →
  trượt key với phía form (JS `combo.sort()`) và phía `existingByKey`
  (collection `sort()`) → mất sku/price/stock đã nhập + variant bị xóa-tạo
  lại. Fix: sort numeric tăng dần trước khi implode. Regression test:
  `test_variant_data_preserved_when_attribute_value_ids_interleave` +
  `test_variant_key_collision_does_not_duplicate`.
- **P0.2 Eager load `primaryImageMedia`** (`ProductRepository::getActivePaginated()`):
  index gọi `$product->primary_image_url` → accessor chạy 1 query media/row
  (N+1). Fix: thêm relation vào `with[]`; accessor ưu tiên
  `$this->primaryImageMedia?->getUrl()` khi relation đã load, fallback query
  cho caller ngoài listing. Test: `test_products_index_does_not_n_plus_one_media`.

### P1 — Chuẩn hóa lớp theo docs

- **P1.1**: `getTabs()` + tag query đẩy về Service/Repository — thêm
  `ProductRepository::countByStatus()/deleteByIds()/updateStatusByIds()`,
  `ProductService::getTabs()`, `TagRepository::getActiveOrdered()`;
  controller không còn DB query trực tiếp.
- **P1.2**: `ProductService` không query model trực tiếp — `Tag::where/create`
  → `TagRepository`; `ProductAttributeValue::where/create` →
  `ProductAttributeRepository::findValueByText()/createValue()`;
  `Language::where` → `LanguageResolver` (cached); 2 FormRequest cũng đổi sang
  `LanguageResolver`.
- **P1.3**: 4 chỗ `DB::transaction()` trong `ProductService` →
  `$this->handleTransaction()` của `BaseService`.
- **P1.4**: Bulk delete dùng `deleteByIds()` (1 query thay vì N lần
  `findByUuidWithRelations` + delete, kèm clear cache tay vì observer không
  fire với bulk query); bulk status dùng `ProductService::updateStatusByIds()`.

### P2 — Enum hóa & dọn dẹp

- **P2.1**: `App\Core\Enums\ProductType` (physical/virtual/digital + `label()`
  + `needsShipping()`); `BaseProductRequest` validate qua `new Enum(...)`;
  `ProductDTO` dùng `ProductType` + `ContentStatus` (xóa `Rule::in` chuỗi);
  controller `productTypes()` sinh từ enum. **Không cast enum ở model** (DB
  lưu chuỗi, blade form so `=== 'physical'` không bị phá).
- **P2.2**: `ProductDTO::toArray()` — `published_at` chỉ set khi user nhập
  hoặc status = published (trước đó draft cũng nhận `now()`).
- **P2.3**: Drop cột legacy `products.dimensions` (migration
  `2026_10_03_000000`, đã backfill sang `length`/`width`/`height` từ 09/2026);
  xóa khỏi `$fillable`, DTO, validation rule và block thông báo form.
- **P2.4**: Xóa file stale `resources/views/admin/products/module-analysis.md`
  (nội dung sai "No N+1" — chính là bug P0.2).

### P3 — Groundwork scalability (Cart/Order/Inventory)

- **P3.1 `ProductObserver`**: invalidate cache `product:{uuid}` +
  `products:featured` khi save/delete (key-based cache, CACHE_STORE=database
  không hỗ trợ tags). Đăng ký trong `AppServiceProvider::boot()`.
- **P3.2 `StockService`** (`app/Services/Shared/Product/`): `decrementStock()`
  / `incrementStock()` / `isLowStock()` — atomic qua `lockForUpdate()` +
  `decrement()`, trả `bool` không throw (caller quyết định rollback); chính
  sách tồn kho (`track_inventory`/`allow_backorder`/`low_stock_threshold`) nằm
  ở Product, variant chỉ giữ stock. Kèm `ProductVariantRepository` (mới, có
  binding) + event `App\Events\Product\LowStockThresholdReached` (chưa có
  listener — Notification Tầng 7 sẽ listen). **Sẵn sàng cho Cart/Order, chưa
  có route/UI nào gọi.**
- **P3.3 Cache 3 catalog lookup**: `ProductAttributeRepository::
  getActiveWithValues()` + `getAvailableForProduct()` (per-product, version
  stamp để invalidate hàng loạt vì database cache không có tags);
  `CategoryRepository::getForSelect()` + `BrandRepository::getForSelect()`
  (thay `all()` ở index/form). Invalidate qua observer mới
  (`ProductAttributeObserver`/`CategoryObserver`/`BrandObserver`) +
  `createValue()` clear tay. **Không cache** `getActivePaginated` /
  `findByUuidWithRelations` (filter/relations thay đổi liên tục).

### Kiểm thử

- `php artisan test` toàn bộ → **180 passed** (593 assertions); tăng từ 143
  (+37 test mới: combo key interleave, N+1 media, getTabs, syncTags, enum
  ProductType, published_at semantic, observer cache, StockService 10 case,
  catalog cache 7 case).
- `pint --test --dirty` clean; migration drop `dimensions` up/rollback OK.

## Việc cần làm

- [x] **Thư viện ảnh nhiều ảnh**: component `x-admin.image-gallery` (render item có sẵn + clone qua `<template>` khi click "Thêm ảnh"); ảnh đại diện giờ là trường riêng `primary_image` (P0.2); DTO đọc theo convention `images[i][image]_uuid` / `_remove`.
- [x] **Biến thể sản phẩm**: `ProductService::generateVariants` + `syncAttributes` + UI dynamic JS trong form (xem component `x-admin.product-attributes` / `x-admin.product-variants`).
- [x] **Ảnh riêng cho variant**: cột `product_variants.image` + modal chi tiết (P1.3).
- [x] **Thuộc tính & giá trị tạo mới tại trang create** (P1.2) + tom-select (P1.1).
- [x] **Schema mở rộng**: product_type, kích thước tách riêng, vận chuyển/thuế, chính sách tồn kho, tags (P2).
- [ ] Bổ sung `ProductRepository::searchBySku`, `getOutOfStock` (theo spec `07` §3.2) nếu cần cho phần báo cáo / import.
- [ ] Frontend storefront hiển thị/chọn biến thể (chưa có module front).
- [ ] Tích hợp Cart/Order/Inventory với `product_variants` — **`StockService` (P3.2) đã sẵn sàng**: atomic decrement/increment + `lockForUpdate`, event `LowStockThresholdReached`; `ProductVariantRepository` đã có binding. Order service chỉ cần gọi `StockService::decrementStock()` và rollback khi `false`.
- [ ] Listener cho `App\Events\Product\LowStockThresholdReached` (module Notification, Tầng 7).
- [ ] Frontend storefront dùng `is_filterable` làm bộ lọc tìm kiếm sản phẩm.

## Phụ thuộc

`Category` ✅ → `Brand` ✅ → `ProductAttribute` ✅ → `Product` ✅ (biến thể ✅).

