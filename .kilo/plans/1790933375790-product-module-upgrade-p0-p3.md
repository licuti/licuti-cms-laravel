# Plan: Nâng cấp module Product (P0 → P3)

> Nguồn: audit rà soát `docs/` + code Product module (phiên trước).
> Mục tiêu: đưa module Product về 100% chuẩn kiến trúc docs + đặt groundwork
> scalability cho Tầng 5–6 (Cart/Order/Inventory).
>
> **Quyết định đã chốt với user:**
> - Phạm vi: **P0 → P3 đầy đủ**.
> - Cột legacy `products.dimensions`: **drop luôn** (đã có backfill
>   `length`/`width`/`height`).
>
> **Lưu ý quan trọng (chống sai):**
> - `CACHE_STORE` mặc định = `database` → **không hỗ trợ cache tags**.
>   Dùng key-based cache + `Cache::forget()` như pattern
>   `LanguageRepository` (`Cache::forever` + `clearCache`). KHÔNG dùng
>   `Cache::tags(...)`.
> - `BaseRepository` **không có** `deleteByIds()` / `countByStatus()` (docs
>   `09` ghi sai — chúng nằm ở từng module con, VD `PostRepository:54,59`).
>   Phải tự thêm vào `ProductRepository` + `ProductRepositoryInterface`.
> - `ProductVariant::getNameAttribute()` lấy tên độc lập theo
>   `display_order` của attribute → sort combo key **không** phá tên biến thể.
> - `TagRepositoryInterface`/`TagRepository` **đã tồn tại** (chỉ có
>   `getFiltered`) → cần thêm method cho select tag.

---

## P0 — Sửa bug dữ liệu (làm đầu tiên, độc lập, có regression test)

### P0.1 — Canonicalize variant combo key (sort cả 3 phía)

**Vấn đề:** 3 cách sinh combo key không thống nhất:
- PHP `generateVariants()` sinh combo theo thứ tự attribute × value,
  `$comboKeys = implode('-', $combo)` — **KHÔNG sort** (`ProductService:357`).
- `existingByKey` dùng `$variant->attributeValues->pluck('id')->sort()->implode('-')`
  — **CÓ sort** (`ProductService:365`).
- JS form submit key `variants[{key}][...]` sau khi `combo.sort()` — **CÓ sort**
  (`product-variants.blade.php:272`).

→ Khi id value của 2 attribute đan xen (VD attribute A có id 50-51 đặt trước
attribute B có id 10-11): combo PHP = `"50-10"`, form/DB = `"10-50"` →
`$variantData[$key]` trượt (mất sku/price/stock người dùng nhập) +
`existingByKey[$key]` trượt (variant cũ bị xóa rồi tạo lại → mất id + dữ liệu).

**Fix (sửa 1 điểm duy nhất, mọi phía đã sort nên chỉ cần sort combo PHP):**

`app/Services/Admin/Product/ProductService.php` — trong `generateVariants()`,
ngay sau khi build `$combos`:

```php
$combos = array_map(
    fn ($combo) => [...$combo, /* sort numeric */],
    $combos
);
// cụ thể: sort từng combo tăng dần numeric trước khi implode
$comboKeys = array_map(function ($combo) {
    $ids = array_map('intval', $combo);
    sort($ids, SORT_NUMERIC);
    return implode('-', $ids);
}, $combos);
```

Lưu ý `$combos[$order]` vẫn dùng để `attributeValues()->sync($combos[$order])`
(thứ tự sync không quan trọng với pivot không có display_order) nên sort key
không ảnh hưởng sync.

**Test (`tests/Feature/Admin/ProductAttributeVariantTest.php`, thêm 2 case):**
1. `test_variant_data_preserved_when_attribute_value_ids_interleave` — tạo
   product có 2 attribute variation, value id của attribute thứ tự đầu LỚN HƠN
   attribute thứ 2 (dùng factory, sắp thứ tự tạo record). Submit update với
   payload `variants[key-sorted]` chứa sku/price/stock riêng → assert variant
   giữ nguyên id + dữ liệu, KHÔNG bị tạo lại (kiểm tra count variant không đổi).
2. `test_variant_key_collision_does_not_duplicate` — cùng combo submit 2 lần
   → số variant không nhân đôi.

### P0.2 — Eager load `primaryImageMedia` (fix N+1 index)

**Vấn đề:** `ProductRepository::getActivePaginated()` eager load `images.media`
nhưng index gọi `$product->primary_image_url` → accessor `Media::where('uuid',...)`
chạy 1 query/row (`Product.php:168`). Model đã có relation `primaryImageMedia()`
nhưng không dùng.

**Fix:**
- `app/Repositories/Eloquent/ProductRepository.php::getActivePaginated()`:
  thêm `'primaryImageMedia'` vào mảng `with([...])`.
- `app/Models/Product.php::getPrimaryImageUrlAttribute()`: khi
  `preg_match('/^[0-9a-f-]{36}$/i', $image)` → ưu tiên
  `$this->primaryImageMedia?->getUrl()` (relation đã eager load), fallback
  `Media::where(...)` cho trường hợp relation chưa load (backward-compat với
  code khác gọi accessor ngoài listing).

**Test:** `tests/Feature/Admin/ProductCrudTest.php` thêm
`test_products_index_does_not_n_plus_one_media` — tạo 5 product có
`primary_image` = media uuid; `DB::enableQueryLog()`, GET index; assert số
query chứa `select * from media` ≤ 1 (hoặc dùng
`assertQueryCount`/`DB::getQueryLog()` count).

---

## P1 — Chuẩn hóa lớp theo docs (mỗi task độc lập, không phá API)

### P1.1 — Đẩy `getTabs()` + tag query về Repository/Service

Controller đang chứa DB query (`ProductController:169-172` `Product::selectRaw`,
`:203` `Tag::orderBy`). Chuẩn (`11` Bước 5, `PostService:88`): `getTabs()`
nằm trong Service, đếm qua `Repository::countByStatus()`.

**Thay đổi:**
1. `ProductRepositoryInterface` + `ProductRepository`: thêm
   ```php
   public function countByStatus(): array;   // copy PostRepository:59
   public function deleteByIds(array $ids): int;  // copy PostRepository:54
   ```
2. `ProductService`: thêm `getTabs(): array` (copy y nguyên logic từ controller,
   nhưng gọi `$this->repository->countByStatus()`).
3. `ProductController`: xóa private `getTabs()`, `index()` gọi
   `$this->service->getTabs()`.
4. `TagRepositoryInterface` + `TagRepository`: thêm
   `public function getActiveOrdered(): Collection` —
   `->orderBy('name')->get(['id','name','slug'])`.
5. `ProductController::formViewData()`: inject `TagRepositoryInterface`, thay
   `Tag::orderBy('name')->get(...)` bằng `$this->tagRepository->getActiveOrdered()`.
   (Controller vẫn inject được theo chuẩn — `ProductController` constructor
   đã có pattern inject nhiều repository.)

**Test:** `ProductModuleFixTest` — thêm assert `getTabs()` đếm đúng (3
published + 2 draft → tab counts). Có thể test qua `index()` response thấy
count trong filter-tabs, hoặc Unit test `ProductService::getTabs()`.

### P1.2 — Service không query model trực tiếp

`ProductService` đang dùng `Tag::where/create` (`:168,176`),
`ProductAttributeValue::where/create` (`:207,217,284`), `Language::where`
(`:264`). Chuyển về repository:

1. Inject `TagRepositoryInterface` vào `ProductService` (constructor).
2. `syncTags()`: `Tag::where('slug',$slug)->first()` →
   `$this->tagRepository->findWhere(['slug' => $slug])->first()`;
   `Tag::create([...])` → `$this->tagRepository->create([...])`.
3. `ProductAttributeValue` queries → thêm method vào
   `ProductAttributeRepository`:
   ```php
   public function findValueByText(int $attributeId, string $value): ?ProductAttributeValue
   public function createValue(int $attributeId, string $value, ?string $colorCode = null): ProductAttributeValue
   ```
   (Đặt ở đây vì value là aggregate con của attribute — tránh tạo thêm
   repository mới chỉ dùng 2 chỗ.)
4. `Language::where('is_default',...)` → inject
   `LanguageRepositoryInterface::getActiveLanguages()` (đã cache) và lấy
   `firstWhere('is_default', true)?->code`. Cùng lúc thay
   `BaseProductRequest::defaultLocale()` và
   `StoreCustomAttributeRequest::rules()` dùng `LanguageResolver` (đã có
   `getDefaultLanguage()`, cached) — **cần inject qua constructor hoặc
   `app(LanguageResolver::class)`** vì FormRequest không constructor-inject
   được; ưu tiên `app()` trong method để giữ đối tượng request gọn.

**Test:** các test hiện có (tags sync, custom attribute) vẫn pass = đủ. Thêm
1 unit test `ProductServiceTest` (mới, `tests/Unit/Services/`):
`test_sync_tags_reuses_existing_tag_by_slug` — tạo tag trước, sync tag text
trùng slug → assert không tạo tag mới (count tag không đổi).

### P1.3 — `handleTransaction()` thay `DB::transaction()`

`ProductService` dùng `DB::transaction()` (`:33,92,414,426`) thay vì
`$this->handleTransaction()` của `BaseService` (docs `09` §3.1, `14` §1).
Đổi 4 chỗ — hành vi giống hệt (`BaseService::handleTransaction` chính là
`DB::transaction`), chỉ là tuân thủ chuẩn.

### P1.4 — Bulk delete dùng `deleteByIds()`

`BulkActionServiceProvider:176-181` đang loop `$service->delete($product->uuid)`
(N lần `findByUuidWithRelations` + delete).

**Fix:** đổi callback dùng
`app(ProductRepositoryInterface::class)->deleteByIds($ids)` (method thêm ở
P1.1), vẫn bọc `DB::transaction()`. Bulk status (`:191-193`) đổi từ
`->get()->each(fn($p) => $p->update(...))` sang 1 query:
`Product::whereIn('id', $ids)->update(['status' => $status->value])` —
**nhưng** query này là DB query trong provider; chuẩn hơn chuyển thành
`ProductService::updateStatusByIds(array $ids, string $status): int` và provider
gọi service (Service dùng repository). Thêm `updateStatusByIds` vào
`ProductRepositoryInterface` + `ProductRepository`.

**Test:** `BulkActionAuthorizationTest` đã có 2 case bulk product — chạy lại
pass. Thêm assert count query giảm (tùy chọn).

---

## P2 — Enum hóa & dọn dẹp

### P2.1 — Tạo `ProductType` enum + dùng Enum trong validate

Tạo `app/Core/Enums/ProductType.php`:
```php
enum ProductType: string
{
    case PHYSICAL = 'physical';
    case VIRTUAL  = 'virtual';
    case DIGITAL  = 'digital';

    public function label(): string { /* Vật lý / Ảo / Số */ }
    public function needsShipping(): bool { return $this === self::PHYSICAL; }
}
```
Áp dụng:
- `BaseProductRequest::sharedRules()`: `'product_type' => ['nullable', new Enum(ProductType::class)]`,
  `'status' => ['required', new Enum(ContentStatus::class)]` (thay `Rule::in([...])`).
- `ProductDTO`: thay `string $productType = 'physical'` →
  `public readonly ProductType $productType = ProductType::PHYSICAL`;
  `fromRequest()` dùng `ProductType::tryFrom((string) $request->input('product_type')) ?? ProductType::PHYSICAL`;
  `toArray()` trả `$this->productType->value`. Tương tự `$status` →
  `ContentStatus`, `toArray()` trả `->value`.
- `ProductController::productTypes()`: dùng
  `collect(ProductType::cases())->mapWithKeys(fn($t) => [$t->value => $t->label()])`.
- `Product::$casts`: thêm `'product_type' => ProductType::class`? **KHÔNG** —
  cast enum + fillable + toArray() đã lo; nhưng nếu model cast sang enum thì
  `$product->product_type` trả enum, form `old()` so sánh chuỗi sẽ cần
  `->value`. **Quyết định: KHÔNG cast enum ở model** (giữ string trong DB,
  enum chỉ ở DTO/validate/label) để tránh phá blade form so `=== 'physical'`.
  Ghi rõ lý do vào comment.

**Test:** `ProductCrudTest::test_invalid_product_type_is_rejected` đã có → chạy
lại. Thêm `test_product_type_enum_rejects_unknown_value` submit
`product_type='service'` → 422 (session error).

### P2.2 — Sửa `published_at` semantic

`ProductDTO::toArray()` luôn `'published_at' => $this->publishedAt ?? now()`
→ draft cũng nhận `published_at = now()`.

**Fix:** chỉ set khi status published HOẶC user nhập:
```php
'published_at' => $this->publishedAt
    ?? ($this->status === ContentStatus::PUBLISHED ? now() : null),
```
(với P2.1 `$this->status` là `ContentStatus`.)

**Test:** `test_draft_product_has_null_published_at` — create status=draft,
assert DB `published_at IS NULL`; update sang published → tự set now().

### P2.3 — Drop cột `dimensions` legacy

Đã quyết định drop. Cột đã được backfill sang `length/width/height` (migration
`2026_09_28_010000`), form không còn field nào submit.

**Thay đổi:**
1. Migration mới `2026_10_03_000000_drop_dimensions_from_products.php`:
   ```php
   if (Schema::hasTable('products') && Schema::hasColumn('products','dimensions')) {
       Schema::table('products', fn ($t) => $t->dropColumn('dimensions'));
   }
   ```
   `down()`: thêm lại `$table->string('dimensions',100)->nullable()` (không
   thể khôi phục dữ liệu — ghi comment).
2. Xóa `'dimensions'` khỏi `Product::$fillable`.
3. Xóa `dimensions` khỏi `ProductDTO` (property + `fromRequest` + `toArray`).
4. Xóa rule `'dimensions' => [...]` khỏi `BaseProductRequest::sharedRules()`.
5. `form.blade.php:400-404`: xóa block `@if(!empty($product?->dimensions))`
   hiển thị thông báo "Kích thước cũ".

**Test:** `php artisan migrate` + `migrate:rollback` không lỗi; chạy lại toàn
suite. `ProductCrudTest` có test update product → assert không lỗi.

### P2.4 — Dọn file stale

- Xóa `resources/views/admin/products/module-analysis.md` (sai chỗ — file
  analysis nằm trong `resources/views/`, nội dung stale 09/2026, ghi sai
  "No N+1" — chính là bug P0.2). Nếu muốn giữ lịch sử, **không** move vào
  `docs/` (nội dung sai lạc) → xóa hẳn, audit mới đã nằm trong plan này +
  `docs/modules/product.md`.
- Cập nhật `docs/modules/product.md`: thêm section "Đợt nâng cấp 10/2026"
  liệt kê P0–P3 đã làm (ghi vào sau khi implement xong).
- Cập nhật `docs/modules/00-module-status.md` dòng 57 nếu cần (module vẫn ✅).

---

## P3 — Groundwork scalability (cho Cart/Order/Inventory)

### P3.1 — `ProductObserver` + cache invalidation groundwork

Tạo `app/Observers/ProductObserver.php`:
```php
class ProductObserver
{
    public function __construct(private readonly ProductRepositoryInterface $repository) {}

    public function saved(Product $product): void
    {
        // Invalidate các cache key liên quan (xem P3.3)
        Cache::forget('product:'.$product->uuid);
        Cache::forget('products:featured');
        // Hook cho ActivityLog (Tầng 7) sau này
    }

    public function deleted(Product $product): void { /* same forget */ }
}
```
Đăng ký trong `AppServiceProvider::boot()`:
```php
Product::observe(ProductObserver::class);
```
**Lưu ý:** observer `saved` trigger khi tạo/sửa qua Eloquent. ProductService
chạy trong transaction — observer vẫn fire đúng (sau commit do Eloquent event).
KHÔNG fire khi `deleteByIds()` (bulk query) → P3.2 giải quyết bằng cách service
gọi `forget` tay khi bulk.

**Test:** Unit test `tests/Unit/Observers/ProductObserverTest.php` —
`test_saved_product_forgets_cache` — cache put key, save product, assert
`Cache::missing`.

### P3.2 — Atomic stock operations (`StockService`)

Tạo `app/Services/Shared/Product/StockService.php` (đặt `Shared/` vì sẽ dùng
bởi cả Admin + storefront + Cart/Order sau này — precedent `Services/Shared/`):
```php
class StockService extends BaseService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductVariantRepositoryInterface $variantRepository // nếu có
    ) {}

    /**
     * Trừ tồn kho atomic, chống oversell.
     * Trả false nếu không đủ hàng (KHÔNG throw để caller quyết định).
     */
    public function decrementStock(int $productId, ?int $variantId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $variantId, $quantity) {
            $model = $variantId
                ? $this->variantRepository->lockForUpdateFind($variantId)   // SELECT ... FOR UPDATE
                : $this->productRepository->lockForUpdateFind($productId);

            if (! $model || ! $model->track_inventory) return true;  // không theo dõi → cho qua
            if ($model->stock_quantity < $quantity && ! $model->allow_backorder) return false;

            $model->decrement('stock_quantity', $quantity);
            // fire event LowStockThresholdReached nếu <= low_stock_threshold
            return true;
        });
    }

    public function incrementStock(int $productId, ?int $variantId, int $quantity): bool;
    public function isLowStock(/* ... */): bool;
}
```
**Quyết định thiết kế:**
- Dùng `->lockForUpdate()` (row lock) + `decrement()` — atomic, chống race
  2 đơn hàng cùng trừ. **KHÔNG** dùng read-then-write qua accessor.
- Trả `bool` không throw — caller (Order service) tự quyết định rollback.
- `ProductVariant` chưa có repository riêng — tạo `ProductVariantRepository`
  + interface + binding (`RepositoryServiceProvider`) với method
  `lockForUpdateFind(int $id): ?ProductVariant` (và sau này thêm
  `findByUuid`, ...). Đây cũng là bước đầu cho module variant riêng (docs
  `product-variant.md`).
- Thêm `lockForUpdateFind(int $id): ?Product` vào `ProductRepository` (dùng
  `$this->model->lockForUpdate()->find($id)`).
- Event `app/Events/Product/LowStockThresholdReached.php` (construct với
  product/variant + current stock) — dispatch từ StockService, không cần
  listener ngay ( groundwork; Notification module Tầng 7 sẽ listen sau).

**Test:** `tests/Unit/Services/StockServiceTest.php`:
- `test_decrement_stock_atomic_rejects_insufficient_stock` — set stock 5,
  decrement 10 → false, stock vẫn 5.
- `test_decrement_stock_uses_lock_for_update` — khó test race thật; test
  hành vi: decrement 3 thành công, stock = 2.
- `test_track_inventory_disabled_skips_decrement` — `track_inventory=false`,
  decrement bất kỳ → true, stock không đổi.
- `test_allow_backorder_allows_negative` — stock 0, `allow_backorder=true`,
  decrement 1 → true, stock = -1.
- `test_low_stock_event_dispatched` — threshold 5, stock 5 → decrement 1 →
  `Event::assertDispatched(LowStockThresholdReached::class)`.
- Thêm method `decrementStock` integration test qua service (KHÔNG qua HTTP —
  chưa có route).

### P3.3 — Cache read-heavy catalog lookups

Cache các query đọc nặng, thay đổi ít, dùng pattern
`LanguageRepository` (key + `Cache::forget`, **không tags** — cache driver
`database`):

1. **`ProductRepository::getActiveWithValues()`** (dùng ở form create) —
   cache key `'product_attributes:with_values'`. Invalidate khi
   `ProductAttribute` save (thêm `ProductAttributeObserver` hoặc trong
   `ProductAttributeService`).
   → **CẨN THẬN:** method này nằm ở `ProductAttributeRepository`, không phải
   `ProductRepository`. Cache ở `ProductAttributeRepository::getActiveWithValues()`
   + `getAvailableForProduct(int $productId)` (cache key per-product:
   `'product_attributes:for_product:'.$productId`).
2. **Category/Brand select ở `formViewData`** — cache key
   `'categories:select'`, `'brands:select'`. Invalidate qua `CategoryObserver`
   / `BrandObserver` (mới) hoặc trong service save.
3. Thêm `clearCache()` method mỗi repository + gọi từ service khi có thay đổi
   (create/update/delete) — observer không đủ vì nhiều service ghi qua
   repository, **ưu tiên observer** cho uniformity.

**Phạm vi giới hạn (tránh over-engineer):** CHỈ cache 3 lookup trên. KHÔNG
cache `getActivePaginated` (filter/search thay đổi liên tục, cache phức tạp,
invalidation khó) và KHÔNG cache `findByUuidWithRelations`.

**Test:** Unit test `ProductAttributeRepositoryTest`:
- `test_get_active_with_values_is_cached` — call 2 lần, assert query log chỉ
  có 1 query (dùng `DB::getQueryLog()`).
- `test_cache_invalidated_on_attribute_save` — ProductAttributeObserver fire
  → `Cache::missing`.

---

## Thứ tự thực hiện & dependency

```
P0.1 (combo key) ──┐
P0.2 (N+1 media) ──┴──> test suite xanh
                              │
        P1.1 (getTabs/repo) ──┤
        P1.2 (service query) ─┤──> P1.3 (handleTransaction) ──> P1.4 (bulk)
                              │
        P2.1 (enum) ──────────┤──> P2.2 (published_at, cần P2.1)
        P2.3 (drop dims) ─────┤
        P2.4 (dọn file) ──────┘
                              │
        P3.1 (observer) ──────┤──> P3.3 (cache, cần observer để invalidate)
        P3.2 (StockService) ──┘
```

Mỗi phase kết thúc: `composer test` (pint + artisan test) phải xanh. Commit
theo scope (conventional commit kiểu repo hiện tại: `fix(product):`, `refactor(product):`, `feat(product):`).

---

## Validation tổng thể

1. `composer test` — phải pass toàn bộ (hiện 143+ test, sau plan sẽ thêm ~12
   test mới). `pint --test --dirty` phải clean.
2. `php artisan migrate` + `php artisan migrate:rollback` — cho migration
   drop `dimensions`.
3. Manual smoke test (optional, có DB): tạo product 2 attribute có value id
   đan xen + submit form → variant không bị tạo lại; load `/admin/products` →
   query count media = 1.
4. Cập nhật `docs/modules/product.md` + `00-module-status.md` sau khi xong.

## Risk & rollback

| Risk | Giảm thiểu |
|---|---|
| P0.1 sort combo key thay đổi key format | Cả 3 phía đã sort, chỉ PHP chưa → sync lên. Test interleave là regression chính. |
| P2.1 enum phá blade form so string | QUYẾT ĐỊNH: không cast enum ở model, `toArray()` trả string. Blade không đổi. |
| P2.3 drop cột mất dữ liệu | Đã backfill từ P2 (09/2026); `down()` thêm lại cột rỗng + comment. Chạy `SELECT count(*) FROM products WHERE dimensions IS NOT NULL AND (length IS NULL OR width IS NULL OR height IS NULL)` trước khi migrate — nếu >0 phải backfill tay trước. |
| P3.2 StockService chưa ai gọi | Đây là groundwork — không có route/UI. Test unit là verification chính. Đánh dấu rõ trong docs là "sẵn sàng cho Cart/Order". |
| P3.3 cache staleness | Chỉ cache 3 lookup ít đổi + observer invalidate. Có `clearCache()` tay. |

## Out of scope (ghi rõ để không làm)

- Storefront frontend (chưa có module front).
- Tích hợp thực tế Cart/Order/Inventory (module shell) — chỉ làm
  StockService + event hook.
- Cache decorator `Repositories/Cache/` generic (docs `08` đề cập) — chỉ cache
  targeted 3 lookup.
- Repository method `searchBySku` / `getOutOfStock` (docs product.md ghi "nếu
  cần") — chưa có consumer.
- Refactor `ProductService` 462 dòng thành `VariantGenerator` /
  `AttributeSyncer` collaborator (P4 tiềm năng, để sau khi P0-P3 ổn định).
