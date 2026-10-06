# Plan: Sửa 12 vấn đề module Product Attributes (admin)

Phạm vi: `app/Http/Controllers/Admin/ProductAttributeValueController.php`,
`app/Services/Admin/ProductAttribute/*`, `app/Repositories/Eloquent/ProductAttribute*.php`,
`app/Repositories/Interfaces/ProductAttributeValueRepositoryInterface.php`,
`app/Http/Requests/Admin/ProductAttribute/*`, `app/Http/Requests/Admin/Product/StoreCustomAttributeRequest.php`,
`app/Observers/*`, `app/Providers/{AppServiceProvider,BulkActionServiceProvider}.php`,
`routes/web.php`, `resources/views/admin/product-attribute*/{index,form}.blade.php`,
`tests/Feature/Admin/*`.

Quy ước ghi chú: mọi route/blade/controller đều dùng UUID làm route key (`getRouteKeyName()` = `uuid`).
Controller nhận tham số theo **vị trí** (`Controller::callAction` dùng `array_values`), nên đổi tên
route parameter không phá controller — nhưng phải khớp với `$this->route('uuid')` trong FormRequest.

---

## Phase 1 — Bảo mật & đúng đắn dữ liệu (Cao)

### 1.1 IDOR: scope value theo attribute (Ý 1)
- Thêm `ProductAttributeValueRepository::findByUuidAndAttribute(string $uuid, int $attributeId): ProductAttributeValue`
  — `where('attribute_id', $attributeId)->with('translations')->where('uuid',$uuid)->firstOrFail()`.
- Thêm method vào interface `ProductAttributeValueRepositoryInterface`.
- Trong `ProductAttributeValueController` edit/update/destroy: thay
  `$this->valueRepository->findByUuidWithRelations($uuid)` bằng
  `$this->valueRepository->findByUuidAndAttribute($uuid, $attribute->id)`.
  Sai cặp attribute/value → **404**.
- Service `update()`/`delete()` vẫn query theo uuid (uuid toàn cục duy nhất) — việc kiểm tra
  ownership đã đảm bảo ở controller.

### 1.2 Chặn value trùng văn bản trong (attribute, locale) (Ý 3)
- Tạo trait `app/Core/Traits/ValidatesAttributeValueUniqueness.php`:
  ```php
  protected function valueUniquenessRules(int $attributeId, ?int $ignoreValueId = null): array
  ```
  Trả mảng rules cho từng locale có trong `translations`:
  ```php
  Rule::unique('product_attribute_value_translations', 'value')
      ->where(fn ($q) => $q->where('locale', $locale)
          ->whereIn('attribute_value_id', ProductAttributeValue::select('id')->where('attribute_id', $attributeId)))
      ->when($ignoreValueId, fn ($r) => $r->ignore($ignoreValueId, 'attribute_value_id'))
  ```
  Bỏ qua locale có value rỗng (đã có rule `nullable`).
- `StoreProductAttributeValueRequest` resolve `attributeId` từ route `attribute_uuid`
  (`ProductAttribute::where('uuid', $this->route('attribute_uuid'))->value('id')`)
  rồi merge rules từ trait.
- `UpdateProductAttributeValueRequest` tương tự, truyền thêm `$ignoreValueId`
  (lấy từ route `uuid` → `ProductAttributeValue::where('uuid',...)->value('id')`).
- Message: `'translations.*.value.unique' => __('Giá trị ":value" đã tồn tại trong thuộc tính này.')`.

### 1.3 Unique code theo scope (Ý 5) — validation app-level
- `StoreProductAttributeRequest`: thay `'unique:product_attributes,code'` bằng
  `Rule::unique('product_attributes','code')->where(fn ($q) => $q->whereNull('product_id'))`
  (admin form chỉ tạo global).
- `UpdateProductAttributeRequest`: thêm cùng `->where(...whereNull('product_id'))` + `->ignore($attributeId)`.
- `StoreCustomAttributeRequest`: thay bằng
  `Rule::unique('product_attributes','code')->where(fn ($q) => $q->where('product_id',$productId)->orWhereNull('product_id'))`
  với `$productId` từ route (`products/{uuid}`). Tránh trùng trong cùng product + không đụng global.
- Giữ nguyên DB (`unique(code)` đơn lẻ) — không migration, an toàn cho data sẵn có.

### 1.4 per_page không giới hạn (Ý 2)
- `ProductAttributeValueRepository::getActivePaginatedByAttribute`:
  ```php
  $perPage = (int) ($filters['per_page'] ?? 15);
  $perPage = max(5, min(100, $perPage));
  ```
  (theo sẵn pattern `TagRepository::getFiltered`).

---

## Phase 2 — UI/UX (Trung bình)

### 2.1 Empty-state colspan sai (Ý 4)
- `resources/views/admin/product-attribute-values/index.blade.php:91`:
  `colspan="{{ $attribute->type === COLOR ? 3 : 2 }}"`.

### 2.2 Color picker mặc định `#000000` (Ý 12a)
- `product-attribute-values/form.blade.php`: input text `color_code` đổi fallback
  `$valueModel?->color_code ?? '#000000'` → `?? ''` (input `type="color"` giữ `#000000` mặc định
  của trình duyệt; hai input vẫn sync qua `onchange`/`oninput`).
- Khi `attribute->type === color`: thêm rule `color_code` `required` (conditional trong
  `Store`/`UpdateProductAttributeValueRequest` — resolve type từ route).
  → User bắt buộc xác nhận màu; không còn lưu đen im lặng.
  Message: `'color_code.required' => __('Vui lòng chọn mã màu cho giá trị này.')`.

### 2.3 Bulk delete cho values (Ý 12b)
- `BulkActionServiceProvider::bootProductAttributeBulkActions`: đăng ký module
  `product_attribute_values` action `delete`, permission `product-attributes.delete`,
  handler loop `ProductAttributeValueService::delete($uuid)` trong `DB::transaction`.
- `ProductAttributeValueController::bulk(BulkActionRequest $request, string $attributeUuid)`:
  `$registry->dispatch('product_attribute_values', ...)` → redirect về values.index.
- Route (trong group `product-attributes/{attribute_uuid}`):
  `Route::post('values/bulk', [ProductAttributeValueController::class, 'bulk'])->name('values.bulk');`
  (đặt **trước** `Route::resource('values', ...)` để không bị `values/{uuid}` bắt nhầm).
- View `product-attribute-values/index.blade.php`: thêm cột checkbox (`#check-all` + `.row-checkbox`),
  thanh bulk action (`#bulk-action-select` + `#btn-apply-bulk`) và hidden form
  `#form-bulk-action` post tới `values.bulk` với `bulk_module=product_attribute_values`.
  JS `table-utils.js` đã tự khởi tạo → không cần code JS mới.
  Empty-state colspan tính thêm cột checkbox (3/4 → bây giờ 4/3 với màu, 3/2 không màu —
  đếm lại theo số `<th>` thực tế khi code).

### 2.4 Reorder value (up/down) (Ý 12c)
- `ProductAttributeValueService::moveValue(string $attributeUuid, string $uuid, string $direction): void`
  — trong `DB::transaction`: tìm value (qua `findByUuidAndAttribute`), tìm sibling kề cùng
  `attribute_id` theo `display_order asc, id asc` (`direction === 'up'`: bản có display_order
  nhỏ hơn gần nhất; `'down'`: lớn hơn gần nhất); nếu có → swap `display_order`
  (dùng raw `update` cả 2 row hoặc `DB::table` để tránh model event lặp); `clearCache()`.
  Không có sibling → redirect bình thường (không lỗi).
- Routes:
  ```php
  Route::post('values/{uuid}/move-up', [ProductAttributeValueController::class,'moveUp'])->name('values.move_up');
  Route::post('values/{uuid}/move-down', [ProductAttributeValueController::class,'moveDown'])->name('values.move_down');
  ```
- Controller: inject `BulkActionRegistry`? không cần — 2 method nhỏ `moveUp`/`moveDown`
  gọi `$this->service->moveValue($attributeUuid, $uuid, 'up'|'down')` rồi
  `redirect()->back()->with('success', ...)`.
- Authorization: tạo `MoveProductAttributeValueRequest` (extends FormRequest,
  `permission(): 'product-attributes.update'`) dùng cho cả 2 route — theo convention
  "write route do FormRequest đảm nhiệm". Routes dùng request này làm type-hint param thứ 3.
- View: thêm cột "Thứ tự" chứa 2 icon button `bi-arrow-up`/`bi-arrow-down` (form POST nhỏ hoặc
  `<x-admin.button href=...>` — cần method POST nên dùng form inline có `@csrf`, class
  `btn btn-link p-0`). Ẩn mũi tên lên ở row đầu, mũi tên xuống ở row cuối
  (dùng loop index của `$values`).

---

## Phase 3 — Dọn dẹp code & cache (Thấp)

### 3.1 Redundant uuid (Ý 8)
- `ProductAttributeValueService::create`: xóa `$data['uuid'] = Str::uuid()->toString();`
  (`HasUuid::bootHasUuid` tự sinh khi tạo). Xóa import `Str` nếu không còn dùng.
- `ProductAttributeService`: xóa import `Illuminate\Support\Facades\DB` và `Str` (không dùng trực tiếp).

### 3.2 Service update trả model stale (Ý 6)
- `ProductAttributeService::update`: cuối transaction `return $model->fresh(['translations','values.translations']);`
- `ProductAttributeValueService::update`: `return $model->fresh(['translations']);`
  (hiện tại controller chỉ dùng `submit_action`, nhưng đảm bảo caller sau này nhận data mới).

### 3.3 Song song 2 cơ chế invalidate cache (Ý 9)
- Tạo `app/Observers/ProductAttributeValueObserver.php`:
  ```php
  public function __construct(private readonly ProductAttributeRepositoryInterface $repository) {}
  public function saved(ProductAttributeValue $value): void { $this->repository->clearCache(); }
  public function deleted(ProductAttributeValue $value): void { $this->repository->clearCache(); }
  ```
- Đăng ký trong `AppServiceProvider::boot()`: `ProductAttributeValue::observe(...)`.
- Xóa các lời gọi `clearCache()` thủ công:
  `ProductAttributeValueService::create/update/delete` (3 chỗ) và
  `ProductAttributeRepository::createValue()` (1 chỗ).
- Cập nhật comment trong `ProductAttributeObserver` cho đúng phạm vi cover mới.
- Lưu ý path không qua observer: `deleteCascade()` dùng `$model->values()->delete()` (mass delete,
  không fire event) — nhưng attribute sau đó cũng bị delete → `ProductAttributeObserver::deleted`
  vẫn clear. An toàn.

### 3.4 Cache per-product phình (Ý 10)
- `ProductAttributeRepository::getAvailableForProduct`: đổi
  `Cache::rememberForever($key, ...)` → `Cache::remember($key, now()->addDays(30), ...)`.
  Giữ version stamp; key cũ vẫn bị vô hiệu hóa ngay bởi version increment, TTL chỉ để dọn dọt.
  `getActiveWithValues` (catalog, 1 key duy nhất) giữ `rememberForever`.

### 3.5 Query polish (Ý 11)
- `ProductAttributeValueRepository::getActivePaginatedByAttribute`:
  - trim search: `$search = trim($filters['search']);` trước khi build `like`.
  - thêm secondary sort: `->orderBy('display_order')->orderBy('id', 'desc')`.
  (Việc lọc search theo locale cụ thể để ngoài phạm vi — giữ search đa locale.)

### 3.6 Chuẩn hóa tên route parameter (Ý 7)
- `routes/web.php`:
  - `product-attributes/{product_attribute}/edit` → `product-attributes/{uuid}/edit`
  - trong group values: `values/{value}/edit` → `values/{uuid}/edit`
  → `$this->route('uuid')` trong `UpdateProductAttributeRequest` ổn định; `UpdateProductAttributeValueRequest`
  cũng dùng `$this->route('uuid')` nhất quán.

---

## Phase 4 — Test

Thêm vào `tests/Feature/Admin/ProductAttributeCrudTest.php`:
- `test_admin_cannot_edit_value_of_another_attribute` → GET edit cặp sai trả 404.
- `test_admin_cannot_update_value_of_another_attribute` → PUT cặp sai trả 404.
- `test_admin_cannot_delete_value_of_another_attribute` → DELETE cặp sai trả 404 + DB không mất value.
- `test_duplicate_value_text_is_rejected` → POST store 2 value cùng text `Đỏ` → validation error.
- `test_value_can_be_updated_to_same_text` → PUT cùng text hiện tại không bị chặn (ignore rule).
- `test_custom_attribute_code_does_not_block_global` → custom code 'color' trên product A
  không chặn tạo global 'color' (chỉnh `StoreCustomAttributeRequest` path qua
  `products.attributes.store`).
- `test_global_code_unique_only_within_global_scope` → tạo 2 global cùng code vẫn lỗi.
- `test_color_code_required_when_attribute_is_color`.
- `test_value_per_page_is_clamped` → `?per_page=9999` → response không vượt 100 item.

Tạo `tests/Feature/Admin/ProductAttributeValueTest.php`:
- `test_admin_can_bulk_delete_values`
- `test_admin_cannot_bulk_delete_in_use_values` (value đang gắn product → báo lỗi / giữ lại)
- `test_move_up_swaps_display_order`, `test_move_down_swaps_display_order`
- `test_move_up_on_first_value_is_noop`
- `test_move_value_scoped_to_attribute` (move value của attribute B qua URL attribute A → 404)

Lưu ý test setup: `RefreshDatabase` + tạo `Language` vi default (theo sẵn `ProductAttributeCrudTest`).
Bulk delete test cần product gắn value qua pivot `product_attribute_value`.

---

## Thứ tự thực thi & verification

1. Phase 1 (1.1 → 1.4) → chạy test CRUD sẵn + test mới.
2. Phase 2 (2.1 → 2.4) → duyệt thủ công 2 URL:
   `/admin/product-attributes` và `/admin/product-attributes/{uuid}/values`.
3. Phase 3 (3.1 → 3.6) → chạy lại toàn test.
4. Phase 4 → `composer test` (chạy `pint --test --dirty` + `php artisan test`).
   Nếu pint báo style fail → chạy `./vendor/bin/pint` sửa rồi rerun.

**Không cần** `npm run build` (không thêm JS; `table-utils.js` đã global).

## Rủi ro / rollback
- 1.3 đổi ngữ nghĩa unique code: cho phép nhiều custom attribute cùng code trên các product khác nhau.
  Đây là hành vi mong muốn fix bug; data cũ vẫn thỏa mãn rule mới → không cần backfill.
- 3.3 thêm observer: nếu có code path mass-update value không qua model (hiện không có),
  cache sẽ không invalidate — đã rà `deleteCascade` và bulk handler mới (loop qua service → qua model).
- 2.3/2.4 thêm route mới: đặt `values/bulk` và `values/{uuid}/move-*` khai báo rõ ràng để
  tránh xung đột với resource REST.
