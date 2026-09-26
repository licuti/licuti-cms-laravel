# Kế hoạch sửa lỗi & nâng cấp module Product (Licuti CMS)

> Kế hoạch triển khai từ kết quả đánh giá module Product (10 khía cạnh, điểm 6.8/10).
> Mọi quyết định thiết kế đã chốt với user (xem mục "Quyết định đã chốt").
> Phạm vi: chỉ module Product + các file liên quan trực tiếp (routes, seeder verification, composer).
>
> **Lưu ý cho agent triển khai:** chạy `php artisan test` (hoặc `composer test`) sau mỗi phase.
> Không commit nếu test đỏ. Test cũ phải giữ nguyên xanh.

---

## Quyết định đã chốt

| # | Quyết định | Lý do |
|---|---|---|
| D1 | Phủ **tất cả 11 điểm**, chia 4 phase độc lập | User chọn scope đầy đủ |
| D2 | Dropdown Category: dùng `$cat->translated_name` + eager load `translations` | Đúng quy ước module Category (views dùng `translated_name`, model Category **không** có `getNameAttribute`) |
| D3 | Custom attribute `code`: **giữ unique toàn cục** + thêm rule validate | Tránh đổi semantics DB; fix bug 500 rò rỉ SQL |
| D4 | Read authorization: `->middleware('can:products.view')` trên route GET | Không tạo class mới; `Gate::before` (is_admin) vẫn hoạt động; `products.view` đã seed cho editor |
| D5 | Migration index: **dedupe trước, add unique sau** | DB thật import từ dump có thể có bản dịch trùng |
| D6 | FormRequest: tạo abstract `BaseProductRequest` chứa phần chung | Giải quyết triệt để DRY + đặt chỗ fix bug validation |
| D7 | Factory mới + test mới; **test cũ giữ nguyên** | Tránh churn vỡ test đang pass |
| D8 | Pint: fix trên file Product module + thêm `pint --test --dirty` vào `composer test` | Enforce style tăng dần, không diff toàn app |

---

## Phase 0 — Refactor FormRequest + fix 2 bug chức năng (P0)

### T1. Tạo `app/Http/Requests/Admin/Product/BaseProductRequest.php`
Abstract class extends `FormRequest`, dùng trait `AuthorizesWithPermission`:

- `protected function sharedRules(): array` — toàn bộ rules chung (price, sku, translations.*, images.*, attributes.*, variants.*) — chính nội dung `rules()` hiện tại của `StoreProductRequest`.
- `protected function sharedMessages(): array` — messages chung.
- `protected function validateVariantSkus($validator, array $ignoreSkus = []): void` — logic cũ + tham số `ignoreSkus`: bỏ qua SKU nằm trong danh sách này khi check `ProductVariant::where('sku', $sku)->exists()`.
- `protected function attributeRules(): array` — phần rules phụ thuộc ngữ cảnh create/update (xem T6).

`StoreProductRequest` / `UpdateProductRequest` extends `BaseProductRequest`, chỉ override:
- `permission()` (`products.create` / `products.update`),
- `rules()` — `sharedRules()` + vài rule ngữ cảnh (sku unique ignore product id cho update; attribute ownership cho create/update).

### T2. Fix bug #1 — update sản phẩm có biến thể giữ nguyên SKU bị reject
Trong `UpdateProductRequest` (hoặc override trong base):

```php
$product = Product::where('uuid', $this->route('uuid'))->first();
$ignoreSkus = $product?->variants()->pluck('sku')->filter()->all() ?? [];
```
truyền `$ignoreSkus` vào `validateVariantSkus()`. Kết quả: submit lại form edit với SKU biến thể không đổi → pass; đổi sang SKU của biến thể sản phẩm khác → vẫn reject đúng.

### T3. Fix bug #2 — dropdown Category rỗng + N+1 dropdown
- `app/Http/Controllers/Admin/ProductController.php`:
  - `index()`: `$this->categoryRepository->all(['*'], ['translations'])`, `$this->brandRepository->all(['*'], ['translations'])` (BaseRepository::all đã hỗ trợ relations).
  - `formViewData()`: tương tự cho categories/brands.
- Views — đổi sang accessor đúng của Category:
  - `resources/views/admin/products/index.blade.php`: `{{ $product->category?->name ?? '---' }}` → `translated_name` (line ~114); filter dropdown `$cat->name` → `$cat->translated_name` (line ~44). Brand giữ `$b->name` (Brand có `getNameAttribute`).
  - `resources/views/admin/products/form.blade.php`: `$category->name` → `$category->translated_name` (line ~283).

**Validation Phase 0:** thêm test regression (xem Phase 3 T12) rồi mới xong.

---

## Phase 1 — Bảo mật & validation hardening

### T4. XSS qua `color_code`
- `resources/views/components/admin/product-attributes.blade.php` JS `chipInner()`: escape `value.color_code` bằng `escapeHtml()` giống `value.value`.
- `app/Http/Requests/Admin/Product/StoreCustomAttributeRequest.php`: `values.*.color_code` thêm `regex:/^#[0-9a-fA-F]{6}$/` (format hiện tại `#ff0000` thỏa mãn).
- **Ghi chú:** endpoint `ProductAttributeController` (module khác) cũng nhận color_code — ngoài scope, đánh dấu trong `docs/modules/product.md` là follow-up.

### T5. Custom attribute trùng code → 500
- `StoreCustomAttributeRequest`: thêm `'code' => [..., 'unique:product_attributes,code']` + message VN.

### T6. Ownership scoping cho attributes/values
Trong `BaseProductRequest::attributeRules()` (closure rules):

- **Create:** `attributes.*.attribute_id` phải trỏ tới attribute catalog (`product_id IS NULL`).
- **Update:** `product_id IS NULL` **HOẶC** `= id của product đang sửa` (resolve từ route uuid).
- `attributes.*.value_ids.*` phải thuộc đúng attribute đó (`product_attribute_values.attribute_id = attributes.*.attribute_id`).
- Fail → 422 với message VN thân thiện.

### T7. Read authorization trên route GET
`routes/web.php` — tách 3 route GET ra khỏi `Route::resource('products', ...)`:

```php
Route::get('products', [ProductController::class, 'index'])->middleware('can:products.view')->name('products.index');
Route::get('products/create', [ProductController::class, 'create'])->middleware('can:products.view')->name('products.create');
Route::get('products/{uuid}/edit', [ProductController::class, 'edit'])->middleware('can:products.view')->name('products.edit');
Route::resource('products', ProductController::class)->only(['store', 'update', 'destroy'])->parameters(['products' => 'uuid']);
```
Giữ nguyên các route riêng: `products/bulk`, `products/{uuid}/attributes`. Quyền submit (create/update/delete) vẫn do FormRequest đảm nhiệm.

### T8. `destroy()` không rò rỉ exception message
`ProductController::destroy()`: catch → log qua `Log::error` + redirect/JSON với message chung (`__('Có lỗi xảy ra khi xóa sản phẩm.')`), không đẩy `$e->getMessage()` ra response.

---

## Phase 2 — DB index & performance

### T9. Migration `2026_09_25_000000_add_product_translation_indexes.php`
- `product_translations`: dedupe `(product_id, locale)` giữ `MIN(id)` → xóa row trùng → thêm **unique** `(product_id, locale)`; thêm index `slug`, `locale`.
- `product_attribute_translations`: dedupe `(attribute_id, locale)` → **unique**.
- `products`: index `status`, `is_featured`.
- `product_attribute_values`: index `attribute_id`.
- `down()`: drop tất cả index/unique đã thêm (đối xứng).

### T10. Perf trong controller/service
- `ProductController::getTabs()`: thay 3 COUNT query bằng 1 grouped query
  `Product::selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status')`, map label qua `ContentStatus`.
- Xóa duplication `statuses()`: dùng `App\Core\Enums\ContentStatus` (đã có đủ published/draft/archived + `label()`). `BulkActionServiceProvider::bootProductBulkActions()` cũng loop `ContentStatus::cases()` thay mảng hardcode (giống pattern Post/Page).
- `ProductRepository::getActivePaginated()`: loại bỏ eager load trùng `primaryImage.media` — **trước khi đổi**: grep toàn app xem còn ai dùng `$product->primaryImage` không; nếu chỉ `getPrimaryImageUrlAttribute()` dùng, thì đổi accessor thành `$this->images->firstWhere('is_primary', true) ?? $this->images->first()` và chỉ giữ `images.media`.
- Dọn dẹp nhỏ: bỏ `$data['uuid'] = Str::uuid()->...` trong `ProductService::create()`, `createCustomAttribute()`, `generateVariants()` (`HasUuid` boot đã tự sinh).
- JS hardcode locale 'vi' trong `saveCustomAttribute()`: lấy default locale từ data attribute (`data-default-locale` do blade set từ `$defaultLocale`) thay vì `vi`.

### T11. Config hóa hằng số
- Tạo `config/products.php`: `max_combos => 100`, `sku_prefix => 'PRD-'`.
- `ProductService` đọc `config('products.max_combos')` và `config('products.sku_prefix')`.
- Blade component `product-variants.blade.php`: `var MAX_COMBOS = {{ config('products.max_combos') }};` (blade render giá trị, JS không hardcode).

---

## Phase 3 — Factory, test, coding standards

### T12. Factory mới (`database/factories/`)
- `ProductFactory`: sku (unique seq), price, stock_quantity, status published, published_at, timestamps; optional `withTranslations()` state.
- `ProductTranslationFactory`: product_id, locale, name, slug.
- `ProductVariantFactory`: product_id, sku, price, stock_quantity, is_active.
- `ProductAttributeFactory`: code, type, product_id nullable (catalog default).
- `ProductAttributeValueFactory`: attribute_id, value, color_code, display_order.

### T13. Test mới (dùng factory, test cũ giữ nguyên)
File `tests/Feature/Admin/ProductModuleFixTest.php` (hoặc rải vào file hiện có nếu cùng chủ đề):

1. `test_update_product_with_unchanged_variant_sku_succeeds` — bug #1 (submit 2 lần, SKU không đổi, giá cập nhật).
2. `test_update_product_variant_sku_conflict_with_other_product_rejected` — đổi SKU trùng biến thể sản phẩm khác → 422.
3. `test_category_dropdown_shows_translated_name` — index filter + create form có tên category.
4. `test_product_form_shows_category_and_brand_names` — form edit.
5. `test_custom_attribute_duplicate_code_rejected` — 422 (không còn 500), message VN.
6. `test_custom_attribute_malicious_color_code_rejected` — regex reject.
7. `test_cross_product_attribute_attachment_rejected` — product B không attach custom attribute của product A.
8. `test_editor_without_products_view_forbidden_from_read_routes` — 403 index/create/edit khi chỉ có `admin.access`.
9. `test_bulk_delete_requires_products_delete` / `test_bulk_status_change_requires_products_update` — thêm vào `BulkActionAuthorizationTest.php` hoặc file mới.

### T14. Pint & composer script
- Chạy `./vendor/bin/pint` (fix mode) **chỉ** trên các file Product module đã đụng: `app/Http/Controllers/Admin/ProductController.php`, `app/Services/Admin/Product/ProductService.php`, `app/DTOs/Product/ProductDTO.php`, `app/Models/Product*.php`, `app/Http/Requests/Admin/Product/*`, `app/Repositories/.../ProductRepository.php`, `app/Providers/BulkActionServiceProvider.php`, `routes/web.php`.
- Sửa thủ công `ProductDTO::toArray()` format `{        return [` → đúng indentation.
- `composer.json` script `test`: thêm `"./vendor/bin/pint --test --dirty"` trước `@php artisan test` (chỉ check file đang đổi; các module khác sẽ dọn dần).

### T15. Cập nhật doc
- `docs/modules/product.md`: bổ sung phần "Sửa lỗi 09/2026" (3 bug + hardening), chuyển TODO `ProductRepository::searchBySku` giữ nguyên.
- `docs/modules/00-module-status.md`: cập nhật ghi chú module Product nếu cần (không đổi trạng thái).

---

## Validation plan (cuối mỗi phase)

1. `php artisan test` — toàn suite xanh (bao gồm test mới Phase 3). Kỳ vọng ≥ 37 + 9 test mới.
2. `./vendor/bin/pint --test --dirty` — clean.
3. Smoke test thủ công (sau Phase 0+1):
   - Tạo sản phẩm có thuộc tính + biến thể → save → vào edit → **save lại không đổi SKU** → thành công, giá cập nhật.
   - Dropdown Category/Brand trong index + form hiển thị tên.
   - Tạo custom attribute trùng code → thấy lỗi thân thiện (không phải 500/lỗi SQL).
   - Nhập `color_code` chứa HTML → bị reject.
4. Kiểm tra migrate lên DB thật: chạy migration T9, xác nhận không fail (dedupe log lại số row đã xóa).

## Rủi ro & xử lý

| Rủi ro | Hành động |
|---|---|
| DB thật (từ dump) thiếu permission `products.view` → `can:` middleware khóa hết admin | Trước khi deploy: `php artisan db:seed --class=RolePermissionSeeder` (seeder đã có `products.*` và cấp cho editor — đã verify line 47-48, 105) |
| Unique `(product_id, locale)` fail nếu có dữ liệu mồ côi (product đã hard-delete) | Migration dedupe chỉ xóa trùng theo (product_id, locale); row mồ côi vẫn hợp lệ unique. Nếu cần, thêm bước dọn orphan (tùy chọn, không bắt buộc) |
| `can:products.view` trên edit chặn user có `products.update` nhưng không có `products.view` | Theo seed, editor có cả hai. Nếu có role custom chỉ có `products.update`, cần cấp thêm `products.view` (đã ghi chú trong doc) |
| Bỏ eager load `primaryImage.media` phá code khác | Bước grep kiểm tra trước (T10); nếu có consumer khác → giữ nguyên eager load, chỉ ghi nhận duplication |
| Pint fix đổi format file ngoài module | Chỉ chạy pint trên danh sách file liệt kê (T14), không chạy toàn app |

## Out of scope

- Module `ProductReview` (vẫn shell), `ProductAttribute` controller endpoint validation color_code (T4 ghi chú follow-up).
- Refactor tách `ProductVariantService` (để khi tích hợp Cart/Order).
- HTML purifier cho `description` rich text (cần khi làm storefront — mục riêng).
- Áp dụng read-authorization pattern cho các module khác (Product-only trong đợt này).
- Refactor test cũ sang factory.
