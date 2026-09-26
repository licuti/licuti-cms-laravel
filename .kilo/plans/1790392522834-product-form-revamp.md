# Kế hoạch sửa lỗi & nâng cấp form Sản phẩm (admin/products/create & edit)

## Mục tiêu
Sửa các bug UI/UX hiện tại trên trang thêm/sửa sản phẩm và nâng cấp trải nghiệm
quản lý thuộc tính + biến thể theo chuẩn các CMS thương mại lớn (Shopify,
WooCommerce, Magento, Medusa), đồng thời bổ sung các trường sản phẩm còn thiếu.

## Bối cảnh hiện tại (đã xác minh qua code)

- Layout hiện tại: cột trái `col-md-9` chứa 5 card xếp dọc (thông tin, SEO,
  giá/kho, thuộc tính, biến thể), cột phải `col-md-3` chứa gallery ảnh, publish
  box, danh mục, thương hiệu, vận chuyển.
- Bug dropdown "Thêm thuộc tính" bị sidebar đè: `product-attributes.blade.php:64`
  dùng `dropdown-menu-end` khiến menu bạch sang trái; sidebar cố định
  `z-index: 1040` (app.scss) > z-index mặc định dropdown của Bootstrap (1000).
- Ảnh đại diện đang chọn qua radio trên từng item của `image-gallery`
  (`image-gallery.blade.php:78-90`), không có trường riêng.
- Trang create KHÔNG thể tạo thuộc tính tùy chỉnh: nút chỉ render khi
  `$storeRoute` non-null (`product-attributes.blade.php:67`), mà form truyền
  `null` khi tạo mới (`form.blade.php:234`).
- Giá trị thuộc tính đang render toàn bộ thành chip phẳng
  (`product-attributes.blade.php:110`), không search, không phân trang, không
  thêm mới tại chỗ.
- Bảng `product_variants` trong DB **đã có** các cột `barcode`, `cost_price`,
  `image` nhưng model `$fillable` (`ProductVariant.php:17-26`), request rules
  (`BaseProductRequest.php:204-209`), DTO (`ProductDTO.php:136-155`), service
  (`ProductService.php:245-265`) và UI (`product-variants.blade.php:42-76`)
  đều chưa sử dụng.
- Bảng `products` chỉ có `weight` (decimal) và `dimensions` (string
  `"20x15x10"`), không có length/width/height riêng, không có tax/shipping
  config, không có inventory policy, không có tags.

## Quyết định thiết kế (đã xác nhận với user)

1. **Layout**: chuyển sang **tabs ngang theo section** (Bootstrap `nav-tabs`)
   thay vì 5 card xếp dọc.
2. **Chọn giá trị thuộc tính**: dùng thư viện **tom-select** (thêm dependency
   qua npm, import qua Vite) — multi-select có search + tạo giá trị mới khi gõ.
3. **Ảnh đại diện**: thêm cột riêng `products.primary_image` + dùng component
   `x-admin.image-upload` (single), tách biệt khỏi album ảnh (gallery).
4. **Phạm vi Phase 2**: tách dimensions L×R×C, vận chuyển & thuế, chính sách
   tồn kho, tags sản phẩm, loại sản phẩm (product type).

---

## Phase 0 — Sửa bug (không động DB)

### 0.1. Fix dropdown "Thêm thuộc tính" bị sidebar đè

File: `resources/views/components/admin/product-attributes.blade.php:64`

- Bỏ `dropdown-menu-end` (menu sẽ căn trái, bạch sang phải — xa sidebar).
- Thêm `style="z-index: 1060"` cho `.dropdown-menu` (hoặc đặt CSS variable
  `--bs-dropdown-zindex` trong scope của wrapper) để đảm bảo menu luôn trên
  sidebar (`z-index: 1040`).
- Giữ nguyên ô tìm kiếm + danh sách có scroll (`max-height: 320px`).

### 0.2. Tách "Ảnh đại diện" thành trường single upload

Files: migration mới, `Product.php`, `ProductService.php`, `ProductDTO.php`,
`form.blade.php`, `image-gallery.blade.php`.

- **Migration** `add_primary_image_to_products_table`: thêm
  `products.primary_image` (nullable string, lưu media uuid/URL — cùng pattern
  với `brands.logo`). Backfill từ `product_images` hiện có: lấy row
  `is_primary = true` đầu tiên (hoặc `display_order` đầu tiên nếu không có).
- **`Product` model**: thêm `primary_image` vào `$fillable`; thêm accessor
  `getPrimaryImageUrlAttribute()` ưu tiên `primary_image` (giải uuid qua Media
  giống `ProductImage::getUrlAttribute()`), fallback về images đầu tiên để giữ
  backward-compat cho `index.blade.php:84` đang gọi `primary_image_url`.
- **`ProductDTO::parseImages()`**: bỏ logic `primary_index`/`is_primary`; gallery
  giờ chỉ còn là album.
- **`ProductService::create()/update()`**: lưu `primary_image` từ
  `$request->input('primary_image_uuid')` (pattern giống brand logo); phần
  images chỉ còn `image` + `display_order`.
- **`form.blade.php`**: thay card "Hình ảnh sản phẩm" bằng 2 trường:
  - "Ảnh đại diện": `<x-admin.image-upload name="primary_image" .../>` (cột phải).
  - "Album ảnh": `<x-admin.image-gallery name="images" .../>` (cột phải hoặc
    tab "Hình ảnh"), bỏ radio "Ảnh đại diện".
- **`image-gallery.blade.php`**: thêm prop `showPrimary` (default `true`) để
    ẩn radio khi dùng cho album sản phẩm; giữ nguyên behavior cho các module khác.

**Breaking change**: `product_images.is_primary` không còn được ghi bởi product
form. Giữ cột (không drop) để tránh phá vỡ dữ liệu cũ; `ProductCrudTest` các
dòng assert `is_primary` (dòng 101, 127, 151, 179) cần cập nhật.

---

## Phase 1 — UX thuộc tính & biến thể

### 1.1. Chọn giá trị thuộc tính: chip phẳng → tom-select multi-select

Files: `package.json`, `resources/js/app.js`, `resources/css/app.scss`,
`product-attributes.blade.php`, `BaseProductRequest.php`, `ProductDTO.php`,
`ProductService.php`.

- `npm install tom-select`; import `TomSelect` + CSS
  (`tom-select/dist/css/tom-select.bootstrap5.css`) qua `resources/js/app.js`
  và `resources/css/app.scss`.
- Trong `product-attributes.blade.php`: thay block `.value-chips` bằng
  `<select multiple name="attributes[{id}][value_ids][]" class="attribute-values-select">`
  khởi tạo bằng TomSelect với:
  - `plugins: ['remove_button', 'clear_button']`
  - `create: true` (cho gõ giá trị mới), `createOnBlur: true`
  - `maxItems: null`, `hideSelected: true`, search mặc định.
  - Options nạp từ `data-catalog` (JSON đã có sẵn trong wrapper).
- Giữ nút "Thêm thuộc tính" (dropdown catalog có search) và "Thuộc tính tùy chỉnh".
- Giải thích rõ "Dùng cho biến thể": đổi label thành
  "Dùng làm trục biến thể (sinh tổ hợp)" + tooltip/description ngắn, tách trực
  quan 2 nhóm "Thuộc tính mô tả" / "Thuộc tính phân loại" bằng border/màu badge.

### 1.2. Cho phép tạo thuộc tính & giá trị mới tại trang create

Files: `product-attributes.blade.php` (JS), `BaseProductRequest.php`,
`ProductDTO.php`, `ProductService.php`.

- Khi tom-select `create: true` sinh ra value mới (không có id), submit kèm
  flag: hidden input `attributes[{attrId}][new_values][]` (text) song song với
  `value_ids[]` (id có sẵn). JS tách 2 loại trước khi submit (hoặc gửi tất cả
  qua một field `values[]` dạng `{id}` / `new::text` và server tự phân loại).
- Với thuộc tính mới hoàn toàn (custom attribute): giữ inline form
  "Thuộc tính tùy chỉnh", nhưng chuyển từ AJAX-only sang **submit cùng form
  chính**: thêm input `new_attributes[]` chứa `{name, type, values[]}`; server
  tạo attribute (product-scoped khi biết product id, hoặc tạm thời global nếu
  create — xem xét dưới) trong cùng transaction với product.
  - **Quyết định**: khi tạo product mới, attribute mới phải chờ product được
    save xong mới scope được `product_id`. Payload `new_attributes[]` được
    service xử lý SAU khi `$model` được create (đã có sẵn trong
    `ProductService::create()` sau dòng 37), gán `product_id = $model->id`.
- **Request rules mới** (`BaseProductRequest.php`):
  - `attributes.*.new_values` => `nullable|array`
  - `attributes.*.new_values.*` => `string|max:255`
  - `new_attributes` => `nullable|array`
  - `new_attributes.*.name` => `required|string|max:255`
  - `new_attributes.*.type` => `nullable|in:select,color,button,radio`
  - `new_attributes.*.values` => `required|array|min:1`
  - `new_attributes.*.values.*` => `string|max:255`
- **`ProductService::syncAttributes()`**: mở rộng để (a) tạo custom attributes
  từ `new_attributes[]` rồi đưa id vào matrix, (b) tạo value mới từ
  `new_values[]` thuộc attribute tương ứng (tránh trùng lặp — check
  `value` + `attribute_id` tồn tại thì bỏ qua). Phải chạy trong transaction
  (đã có).
- **Validation ownership** (`validateAttributeOwnership()`): cập nhật để cho
  phép new_values/new_attributes đi qua, vẫn giữ ràng buộc: attribute cố định
  phải là global hoặc thuộc product hiện tại.

### 1.3. Nâng cấp bảng "Biến thể sản phẩm"

Files: `product-variants.blade.php`, `ProductVariant.php`,
`BaseProductRequest.php`, `ProductDTO.php`, `ProductService.php`.

- **Mở khóa các cột DB đã có** (`barcode`, `cost_price`, `image`):
  - `ProductVariant::$fillable`: thêm 3 cột.
  - `BaseProductRequest::sharedRules()`: thêm rules
    `variants.*.barcode` (nullable|string|max:100),
    `variants.*.cost_price` (nullable|numeric|min:0),
    `variants.*.image_uuid` (nullable|string|max:255).
  - `ProductDTO::parseVariants()`: thêm 3 trường.
  - `ProductService::generateVariants()`: thêm 3 trường vào cả nhánh update
    (dòng 245-252) và nhánh create (dòng 258-265).
- **UI bảng**: bảng chính giữ các trường thường sửa: Ảnh thumb (nhỏ, click mở
  media picker), Tên biến thể, SKU, Giá, Tồn kho, Hoạt động. Các trường phụ
  (barcode, giá vốn, giá so sánh) đưa vào **modal/drawer "Chi tiết"** mỗi dòng
  — dùng `x-admin.modal` (đã có sẵn, `modal.blade.php`), dataBinding qua JS
  (đọc/ghi input ẩn theo `data-key`).
- **Chống tràn bảng khi 100 tổ hợp**: bọc bảng trong container
  `max-height: 480px; overflow-y: auto` + sticky thead; thêm thanh công cụ:
  "Sinh SKU tự động" (prefix + số thứ tự), "Áp dụng giá cho tất cả",
  "Áp dụng tồn kho cho tất cả" — giảm nhập tay hàng loạt.
- **Ảnh biến thể**: dùng `x-admin.image-upload` kích thước nhỏ (thêm prop
  `size="sm"` hoặc wrap bằng CSS) trong modal chi tiết; submit dưới tên
  `variants[{key}][image_uuid]`.

---

## Phase 2 — Mở rộng schema sản phẩm

### 2.1. Migration `enhance_products_for_shipping_taxonomy`

Thêm vào `products`:

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `product_type` | string, default `physical` | physical / virtual / digital |
| `length` | decimal(8,2) nullable | cm |
| `width` | decimal(8,2) nullable | cm |
| `height` | decimal(8,2) nullable | cm |
| `is_free_shipping` | boolean default false | |
| `shipping_fee` | decimal(15,2) nullable | phí cố định ghi đè |
| `tax_rate` | decimal(5,2) nullable | % VAT, vd 10.00 |
| `is_tax_inclusive` | boolean default true | giá đã bao gồm VAT |
| `allow_backorder` | boolean default false | cho đặt khi hết hàng |
| `low_stock_threshold` | integer unsigned nullable | cảnh báo tồn thấp |
| `min_order_quantity` | integer unsigned nullable | |
| `max_order_quantity` | integer unsigned nullable | |
| `sold_individually` | boolean default false | giới hạn 1/sp đơn |

- **Backfill**: tách `dimensions` (`"20x15x10"`) ra `length/width/height` bằng
  regex trong migration; giữ cột `dimensions` (không drop) để an toàn.
- Bảng mới `product_tag` (product_id, tag_id, timestamps) + unique composite;
  thêm relationship `tags()` BelongsToMany trên `Product` và `products()` trên
  `Tag` (model `Tag` hiện chỉ có `posts()`).

### 2.2. UI Phase 2 theo tabs

Tái cấu trúc `form.blade.php` sang tabs ngang:

- **Tab "Thông tin chung"**: lang-tabs + tên/slug/mô tả ngắn/ch tiết + SEO (như
  hiện tại, gộp SEO chung cho product vì SEO meta đã per-locale).
- **Tab "Giá & Tồn kho"**: giá chính/giá so sánh/giá vốn, SKU, barcode, tồn kho,
  track_inventory + nhóm chính sách tồn kho (allow_backorder,
  low_stock_threshold, min/max order, sold_individually).
- **Tab "Vận chuyển"**: product_type (select), weight, length/width/height,
  is_free_shipping, shipping_fee, tax_rate, is_tax_inclusive. Ẩn/hiện nhóm
  vận chuyển theo product_type bằng JS (virtual/digital → ẩn weight/dims).
- **Tab "Thuộc tính"**: component `product-attributes` (đã nâng cấp P1).
- **Tab "Biến thể"**: component `product-variants` (đã nâng cấp P1).
- **Sidebar cột phải**: Ảnh đại diện (P0), Album ảnh, Xuất bản, Danh mục,
  Thương hiệu, **Tags** (tom-select multiple, create mới được).

### 2.3. Cập nhật backend Phase 2

- `Product::$fillable` + casts: thêm toàn bộ cột mới.
- `ProductDTO`: thêm property + `fromRequest()` + `toArray()`.
- `BaseProductRequest::sharedRules()`: thêm rules tương ứng (numeric/min/bool
  + `product_type` in:physical,virtual,digital).
- `ProductService`: không thay đổi logic variant, chỉ truyền thêm fields qua
  `toArray()` (đã tự động qua `$fillable`).

---

## Cấu trúc file bị thay đổi

**View/components**
- `resources/views/admin/products/form.blade.php` — tái cấu trúc tabs + P2 fields.
- `resources/views/components/admin/product-attributes.blade.php` — fix dropdown,
  tom-select, tạo mới thuộc tính/giá trị.
- `resources/views/components/admin/product-variants.blade.php` — bảng + modal
  chi tiết + toolbar hàng loạt.
- `resources/views/components/admin/image-gallery.blade.php` — prop `showPrimary`.
- (có thể) `resources/views/components/admin/attribute-values-select.blade.php`
  — component mới đóng gói tom-select nếu tái dùng được ở nơi khác.

**Backend**
- `app/Models/Product.php`, `app/Models/ProductVariant.php`,
  `app/Models/ProductImage.php` (không đổi), `app/Models/Tag.php`.
- `app/DTOs/Product/ProductDTO.php`.
- `app/Http/Requests/Admin/Product/BaseProductRequest.php`.
- `app/Services/Admin/Product/ProductService.php`.
- `app/Http/Controllers/Admin/ProductController.php` (truyền tags + config).

**Asset**
- `package.json` (+tom-select), `resources/js/app.js`, `resources/css/app.scss`.
- `database/migrations/` — 2 migration (P0 primary_image, P2 enhance).

**Test**
- `tests/Feature/Admin/ProductCrudTest.php` — cập nhật assert `is_primary`.
- Thêm test: tạo thuộc tính mới tại create, tạo giá trị mới, variant barcode/
  cost/image persist, P2 fields persist.

---

## Validation & kiểm thử

- Chạy `php artisan test --filter=Product` sau mỗi phase; đảm bảo
  `ProductAttributeVariantTest` và `ProductCrudTest` pass.
- Kiểm thủ công 2 luồng: (a) tạo product mới từ đầu + tạo thuộc tính tùy chỉnh
  + gõ giá trị mới + bật biến thể → save; (b) sửa product cũ có dữ liệu
  legacy (`dimensions` string, `is_primary` trên images) → migrate + verify
  không mất ảnh đại diện.
- Test dropdown "Thêm thuộc tính" không còn bị sidebar đè (resize màn hình).
- Test tom-select với attribute có 100+ giá trị (seed dữ liệu).
- `npm run build` để verify Vite build tom-select không lỗi.

---

## Risks & rollback

- **Breaking `is_primary`**: giữ cột + backfill, accessor fallback; nếu storefront
  nào đó đọc `is_primary` trực tiếp cần kiểm tra (chỉ tìm thấy usage trong admin
  index qua `primary_image_url` accessor).
- **Thêm dependency tom-select**: tăng bundle ~40KB gzip; nếu muốn rollback, thay
  bằng custom dropdown (nhóm đã cân nhắc). Toàn bộ JS tom-select cô đọng trong
  1-2 file, dễ rip ra.
- **Tạo attribute global vs product-scoped**: khi tạo product mới, attribute mới
  phải tạo sau khi model save → nếu validation fail giữa chừng, attribute rác có
  thể bị thiếu (đã bọc trong `DB::transaction` nên rollback an toàn).
- **Migration backfill `dimensions`**: regex có thể miss định dạng lạ → log các
  row không match được, không fail migration.
- Mỗi phase deploy độc lập; Phase 0 và 1 không phụ thuộc DB mới, có thể rollback
  bằng `git revert` + giữ nguyên schema.

---

## Thứ tự thực hiện khuyến nghị

1. **P0.1** fix dropdown (1 file, nhỏ nhất, demonstrable ngay).
2. **P0.2** tách ảnh đại diện (migration + DTO + service + view).
3. **P1.1** tom-select chọn giá trị (chưa đụng tạo mới).
4. **P1.2** tạo mới thuộc tính/giá trị tại create.
5. **P1.3** nâng cấp bảng biến thể + mở khóa cột DB.
6. **P2.1+P2.2+P2.3** schema + tabs + fields.
7. Cập nhật test + kiểm thử toàn bộ.

---

## TIẾN ĐỘ (cập nhật lần cuối: 2026-09-26)

### Đã hoàn thành (commit sẵn sàng)

- **P0.1** — Fix dropdown "Thêm thuộc tính" bị sidebar đè:
  `product-attributes.blade.php` bỏ `dropdown-menu-end`, thêm
  `--bs-dropdown-zindex: 1060`. Lưu ý: file này sau đó bị ghi đè toàn bộ
  ở P1.1/P1.2 (fix vẫn còn trong bản mới).
- **P0.2** — Tách ảnh đại diện thành `products.primary_image`:
  - Migration `2026_09_26_060000_add_primary_image_to_products_table.php`
    (thêm cột + backfill từ `product_images.is_primary`/`display_order`).
  - `Product::$fillable` thêm `primary_image`; accessor
    `getPrimaryImageUrlAttribute()` ưu tiên `primary_image`, fallback
    `is_primary` legacy; thêm relation `primaryImageMedia()`.
  - `ProductDTO`: thêm `primaryImage`, `parseImages()` bỏ is_primary,
    `toArray()` thêm `primary_image`.
  - `ProductService::create()/update()`: lưu `primary_image` qua
    `$dto->toArray()`, images chỉ còn `image` + `display_order`.
  - `image-gallery.blade.php`: thêm prop `showPrimary` (default true).
  - `form.blade.php`: thêm `x-admin.image-upload name="primary_image"` +
    album `:show-primary="false"`.
  - `BaseProductRequest`: `primary_index` → `primary_image_uuid`.
  - `ProductCrudTest`: 4 test cũ rewrite + thêm test legacy fallback.
  - **Đã verify**: `php artisan test --filter=ProductCrudTest` → 15 pass.

### Đang làm dở — CẦN TIẾP TỤC

- **P1.1** (tom-select) — **ĐÃ CÀI NHƯNG CHƯA HOÀN THIỆN, đang giữa refactor**:
  - `npm install tom-select` xong (v2.6.2, vào `package.json`).
  - `resources/js/app.js` import + `window.TomSelect`.
  - `resources/css/app.scss` import `tom-select.bootstrap5.css`.
  - `product-attributes.blade.php` đã rewrite toàn bộ: chips →
    `<select multiple class="attribute-values-select">`, JS init TomSelect
    (plugins remove_button/clear_button, `create: true`).
  - **VẤN ĐỀ CHƯA GIẢI QUYẾT** (lý do dừng): logic phân biệt "value có id" vs
    "value mới tạo" đang dùng `data.created` — **SAI**, tom-select 2.6 không set
    flag đó. Kết quả đọc source tom-select:
    - `create: true` (default filter) → option mới có
      `{value: <text>, text: <text>}` (valueField === labelField === value).
    - `userOptions[key]` (truthy) đánh dấu option do user tạo; native select
      submit value = `<text>` (không phải id số).
    - `updateOriginalInput()` (tom-select.ts:2241) tự syncretize `<option>`
      selected cho select tag → **submit native select là đủ**, không cần
    JS tách 2 field.
  - **Cần sửa**: hàm `getMatrix()` trong `product-attributes.blade.php` —
    bỏ nhánh `data.created`, nhận value raw; server tự phân loại id số vs
    `new::<text>`. Hoặc đơn giản hơn: server parse `value_ids[]` cả id lẫn
    text (đang có rule `exists` cản trở → cần nới rule hoặc dùng field
    `values[]` dạng `{id}` / `new::text` như plan 1.2 đã dự phòng).
- **P1.2** (tạo thuộc tính/giá trị tại create) — UI inline form đã viết
  (payload `new_attributes[]`), **NHƯNG** backend chưa đụng:
  - `BaseProductRequest::sharedRules()` chưa có rules `new_attributes.*`,
    `attributes.*.new_values`.
  - `ProductService::syncAttributes()` chưa xử lý `new_attributes` +
    `new_values` (chưa tạo attribute/value trong transaction).
  - `validateAttributeOwnership()` chưa cho phép new_values/new_attributes.
  - `ProductController::formViewData()` chưa truyền tags.
- **P1.3, P2.1, P2.2, P2.3** — chưa bắt đầu.

### Lỗi kỹ thuật đang mở (chỉ xuất hiện khi chạy UI thật)

- tom-select chưa init được đúng vì `getMatrix()` dùng API không tồn tại
  (`select.tomselect.options[value].created`). Cần sửa trước khi build.

### Kiểm thử trạng thái hiện tại

- `php artisan test --filter=ProductCrudTest` → PASS (15 test, P0.2 ok).
- **Chưa chạy** `php artisan test --filter=Product` toàn bộ sau khi P1.1
  modify file (nên chạy lại trước khi commit).
- **Chưa chạy** `npm run build` — tom-select import có thể lỗi build
  (chưa verify Vite).
- `php artisan migrate` chưa chạy trên DB dev (migration P0.2 còn treo).

