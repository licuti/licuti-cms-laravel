# Kế hoạch: Thuộc tính & Biến thể sản phẩm (kiểu WP/WooCommerce)

## Ngữ cảnh & kết quả đánh giá

Yêu cầu WP-like: (1) product chọn nhiều thuộc tính, (2) chọn giá trị thuộc tính cho
từng thuộc tính, (3) biến thể render ra từ lựa chọn.

**Thực trạng (đã verify):**
- ✅ Catalog thuộc tính toàn cục xong: CRUD `admin.product-attributes.*`, model
  `ProductAttribute` (code, type select/color/button/radio, is_filterable,
  translations, `values()` repeater có color_code), test pass.
- ❌ Không có pivot product↔attribute, không có pivot product↔attribute_value.
- ❌ `product_variants` là shell rỗng (chỉ id+timestamps), không có product_id/sku/price.
- ❌ Không có `product_variant_attributes` (pivot variant↔giá trị), không có
  `generateVariants`, không có UI động.
- Shell `admin.product-variants.*` (controller/service/DTO/repo/views) tồn tại nhưng rỗng.

=> Thuộc tính đạt ~40% (chỉ catalog chung), biến thể ~0%.

## Quyết định thiết kế (đã chốt với user)

1. **Biến thể quản lý trong form Product** (đúng WP): khối "Thuộc tính & Biến thể"
   ngay trong form; chọn thuộc tính → tick giá trị → bảng biến thể tự render
   (sku/price/stock). **Xóa shell `admin.product-variants.*`** (route/controller/
   service/DTO/repo/views/RepositoryServiceProvider binding).
2. **Thuộc tính: catalog toàn cục + custom per-product.**
3. **Custom attribute lưu bằng cách mở rộng `product_attributes`**: thêm cột
   `product_id` nullable (NULL = catalog toàn cục, = X = custom của product X);
   values vẫn dùng `product_attribute_values`. Tái dụng 100% CRUD hiện có.
4. **Khi đổi lựa chọn và lưu: preserve-by-combo** — tổ hợp còn hợp lệ giữ nguyên
   sku/price/stock; tổ hợp mới tạo variant mới (giá/tồn mặc định theo product);
   tổ hợp bị bỏ xóa.

## Schema (migration mới)

**M1 — `product_attributes` thêm cột:**
- `product_id` nullable FK→products, `cascadeOnDelete`, index.
- (Catalog index lọc `whereNull('product_id')`.)

**M2 — pivot `product_attribute`:**
- `product_id` FK cascade, `attribute_id` FK cascade,
- `is_variation` boolean default false (đánh dấu "dùng cho biến thể"),
- `display_order` int default 0,
- unique `[product_id, attribute_id]`.

**M3 — pivot `product_attribute_value`:**
- `product_id` FK cascade, `attribute_value_id` FK cascade,
- unique `[product_id, attribute_value_id]`.

**M4 — pivot `product_variant_attribute_values`:**
- `variant_id` FK→product_variants cascade, `attribute_value_id` FK cascade,
- unique `[variant_id, attribute_value_id]`.

**M5 — `product_variants` nâng cấp (alter table):**
- `uuid` unique, `product_id` FK cascade, `sku` nullable unique, `price` nullable
  decimal, `compare_price` nullable decimal, `stock_quantity` int default 0,
- `is_active` boolean default true, `display_order` int default 0.
- (Variant price nullable → frontend fallback theo product price; phạm vi admin
  chỉ lưu.)

## Models

- `ProductAttribute`: thêm `product()` (BelongsTo, nullable) + scope
  `global()`/`isGlobal()`; giữ nguyên phần còn lại.
- `Product`: `attributes(): BelongsToMany(ProductAttribute)` qua `product_attribute`
  (withPivot is_variation, display_order); `attributeValues(): BelongsToMany(
  ProductAttributeValue)`; `variants(): HasMany(ProductVariant)`.
- `ProductAttributeValue`: thêm `products(): BelongsToMany`.
- `ProductVariant`: bỏ `$guarded=[]`, thêm `$fillable` (uuid, product_id, sku,
  price, compare_price, stock_quantity, is_active, display_order), casts, relations
  `product()`, `attributeValues(): BelongsToMany` + accessor `getNameAttribute()`
  (join tên giá trị theo thứ tự, vd "Đỏ - S").

## Service / DTO / FormRequest

- `ProductService`:
  - `syncAttributes(Product|int $product, array $attributeMatrix): void` —
    sync pivot `product_attribute` + `product_attribute_value` trong transaction.
    `$attributeMatrix` = `[['attribute_id' => ..., 'is_variation' => bool,
    'value_ids' => [...]]]`; tự xóa value_ids không còn; cascade khiến variant
    không hợp lệ bị xóa qua FK pivot.
  - `generateVariants(Product $product, array $matrix): void` — cartesian product
    các attribute có `is_variation=true` (chỉ dùng value_ids đã chọn); key = sort
    value_ids; match variant cũ theo key (giữ dữ liệu), tạo mới (mặc định
    price/stock theo product), xóa variant có key không còn. **Guard nổ tổ hợp**:
    throw validation error nếu > 100 combos.
  - `update()` gọi `syncAttributes` + `generateVariants` từ `$dto`.
  - `create()` tương tự (sau khi model có id).
- `ProductDTO`: thêm `attributes` (matrix trên) + `variants` (mảng
  `[['key' => 'sorted-value-ids', 'sku' => ..., 'price' => ..., ...]]`).
- `StoreProductRequest`/`UpdateProductRequest`: rules cho `attributes.*.attribute_id`
  (exists, distinct), `attributes.*.value_ids.*` (exists:product_attribute_values),
  `attributes.*.is_variation` boolean; `variants.*.sku` (nullable, unique:
  product_variants, sku, ignore current), `variants.*.price` numeric min 0...

## UI (form Product, `resources/views/admin/products/form.blade.php`)

Thêm 2 card full-width dưới khối hiện tại:

**Card "Thuộc tính sản phẩm"** (component mới `x-admin.product-attributes`):
- Nút "Thêm thuộc tính" → dropdown từ catalog (`whereNull('product_id')`, load
  JSON `id, name, type, values`); nút "Thuộc tính tùy chỉnh" → form inline
  (tên + giá trị cách nhau dấu phẩy) → POST `admin.products.attributes.store`
  (route mới, tạo `product_attributes` có product_id) → attribute xuất hiện trong
  danh sách.
- Mỗi attribute đã thêm: tên, chips/checkbox chọn value (render theo type: color
  swatch nếu type=color), checkbox "Dùng cho biến thể" (is_variation), nút xóa.
- Hidden inputs: `attributes[i][attribute_id]`, `attributes[i][is_variation]`,
  `attributes[i][value_ids][]`.

**Card "Biến thể"** (component mới `x-admin.product-variants`):
- JS client-side: nghe change trên khối thuộc tính → tính cartesian của các
  attribute `is_variation` → render bảng (`variants[i][key]`, `variants[i][sku]`,
  `variants[i][price]`, `variants[i][compare_price]`, `variants[i][stock_quantity]`,
  `variants[i][is_active]`); khi rerender giữ giá trị đã nhập theo key (map tạm).
- Server render sẵn các variant hiện có khi edit (để người sửa không mất gì nếu
  JS chưa chạy); `key` = sorted value ids.

Xóa: `resources/views/admin/product-variants/`, `ProductVariantController`,
`ProductVariantService`, `ProductVariantDTO`, 2 FormRequest variant,
`ProductVariantRepository` + interface + binding trong `RepositoryServiceProvider`,
route `product-variants`.

## Xóa code chết kèm

- Đã verify: `ProductVariantService::getList()` gọi
  `$this->repository->getActivePaginated()` nhưng `BaseRepository` KHÔNG có method
  này (chỉ `ProductAttributeRepository` tự định nghĩa riêng) → route
  `admin.product-variants.index` đang **fatal nếu truy cập**. Xóa toàn bộ shell
  giải quyết luôn lỗi tiềm ẩn này.

## Tasks (thứ tự thực thi)

1. M1–M5 migrations + `php artisan migrate`; verify schema.
2. Models: relations, scopes, `$fillable` variant, accessor tên variant.
3. `ProductAttributeRepository`: scope global cho catalog index (whereNull
   product_id) + `getAvailableForProduct()` (catalog + custom của product).
4. Route mới `POST admin/products/{uuid}/attributes` (tạo custom attribute) +
   controller method; permission `products.update`.
5. `ProductService::syncAttributes` + `generateVariants` (transaction + guard).
6. `ProductDTO` + 2 FormRequest: fields mới + validation.
7. Component `x-admin.product-attributes` + JS picker/chips.
8. Component `x-admin.product-variants` + JS cartesian render (preserve input).
9. Gắn 2 component vào form Product; truyền catalog JSON.
10. Xóa shell product-variants (route/controller/service/dto/repo/views/binding).
11. Tests (`tests/Feature/Admin/ProductAttributeVariantTest.php`):
    - product chọn 2 attribute (color red/blue, size S/M) is_variation → 4 variants;
    - thêm value "green" → 6 variants, 4 cũ giữ nguyên sku/price;
    - bỏ "dùng cho biến thể" của size → variants về 2 (theo color);
    - custom attribute tạo từ form product (product_id set, không hiện ở catalog);
    - sku trùng variant bị reject; permission: editor thiếu products.update → 403.
12. Docs: `docs/modules/product.md` (đánh dấu xong variants, cập nhật tasks),
    `docs/modules/product-variant.md` (ghi quyết định hòa vào Product + xóa shell),
    `docs/modules/product-attribute.md` (bổ sung product_id scoping + custom flow),
    `docs/03-database-details.md` (schema pivot mới).

## Rủi ro & kiểm tra

- **Nổ tổ hợp**: guard 100 combos ở cả JS (warning) lẫn service (ValidationException).
- **Race FK cascade**: xóa value khỏi selection → FK cascade xóa
  product_attribute_value + product_variant_attribute_values; variant mồ côi
  (không còn value nào) sẽ bị `generateVariants` dọn (key rỗng).
- **SKU unique**: variant sku nullable unique — MySQL unique cho phép nhiều NULL,
  ok; khi sync variant mới chưa có sku để NULL.
- **JS cartesian phức tạp**: test thủ công qua trình duyệt (tạo/edit product),
  verify preserve input khi thêm/bỏ value.
- **Validation**: `php -l` mọi file sửa + `composer test` full suite phải pass
  (hiện 105 test) + `php artisan view:cache` compile check.

## Phạm vi loại trừ

- Frontend storefront hiển thị/chọn biến thể (chưa có module front).
- Tích hợp Cart/Order/Inventory (đang là shell, FK variant chưa dùng).
- Ảnh riêng cho variant (dùng gallery của product).
- Bản dịch cho custom attribute name ngoài default locale (accessor `translate()`
  đã fallback).
