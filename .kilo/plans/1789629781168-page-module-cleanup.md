# Kế hoạch: Dọn dẹp module Page (code chết + fix tree indent + docs)

> Phạm vi: làm gọn 3 việc trong module Page. **KHÔNG** động vào logic nghiệp vụ, **không** thêm migration, **không** thêm pagination.

## Bối cảnh & quyết định đã chốt

- Page module đang chạy đúng, không có lỗi correctness hay N+1 (đã kiểm chứng: `pages/index.blade.php` không gọi `image_url`; form edit đã eager-load `imageMedia` qua `PageService::findByUuid`).
- Chuẩn dự án: **entity nội dung** (`posts`, `pages`) dùng cột `status` (enum `ContentStatus`) + `publish-box`; **master data** (`categories`, `brands`, `banners`...) dùng `is_active`. Page dùng `status` là đúng chuẩn, **không cần thêm `is_active`**.
- `author_id` **chỉ có ở `posts`** (migration `2026_08_28_175815`). `pages` không có và spec `docs/03-database-details.md:563-576` không yêu cầu → **không thêm**.

---

## Việc 1 — Xóa code chết `getList` / `getFiltered`

Nguyên nhân: `PageController::index()` gọi `service->getTreeList()`. Chuỗi `PageService::getList()` → `PageRepository::getFiltered()` không có controller nào gọi (đã grep toàn bộ `app/`, xác nhận 100%).

**Sửa:**

1. `app/Services/Admin/Page/PageService.php`
   - Xóa hàm `getList(array $filters): LengthAwarePaginator` (dòng 49–52).
   - Xóa import `Illuminate\Pagination\LengthAwarePaginator` (dòng 12) nếu không còn dùng.
2. `app/Repositories/Eloquent/PageRepository.php`
   - Xóa hàm `getFiltered(array $filters): LengthAwarePaginator` (dòng 19–46).
   - Xóa import `Illuminate\Pagination\LengthAwarePaginator` (dòng 10) nếu không còn dùng.
3. `app/Repositories/Interfaces/PageRepositoryInterface.php`
   - Xóa khai báo `getFiltered(array $filters): LengthAwarePaginator` (dòng 11–14) + docblock.
   - Có thể xóa luôn import `LengthAwarePaginator` (dòng 7).

**Bổ sung hygiene (đề nghị làm cùng):** thêm `getTreeList(array $filters): Collection` vào `PageRepositoryInterface` — đây mới là phương thức thực sự được dùng nhưng đang không nằm trong contract (gọi qua interface mà không khai báo). Khai báo giúp IDE/PHPStan nhận diện đúng.

---

## Việc 2 — Fix tree indent ở `pages/index.blade.php`

**Bug:** `PageRepository::getTreeList()` đã gán `$node->depth` và `$node->has_children` (dòng 77–78) nhưng view bỏ qua hoàn toàn. `index.blade.php:69–72` chỉ check `$hasParent` rồi in cố định `"==="` → trang cấp 1 và cấp 3 nhìn không khác nhau.

**Sửa trong `resources/views/admin/pages/index.blade.php`** (khối `@php` dòng 65–85):

Thay logic `$hasChildrenIcon` bằng:

```php
$depth     = (int) ($page->depth ?? 0);
$hasKids   = (bool) ($page->has_children ?? false);
$indent    = $depth > 0 ? str_repeat('&nbsp;&nbsp;&nbsp;', $depth) : '';
$treeIcon  = $hasKids
    ? '<span class="text-body-secondary me-1">▾</span>'
    : '<span class="text-body-tertiary me-1">•</span>';
```

Render cell (dòng 91–96) thành dạng:

```blade
<td class="py-3 px-3">
    <div class="d-flex align-items-center">
        <span class="me-2 text-body-tertiary" style="white-space: pre;">{!! $indent !!}</span>
        {!! $treeIcon !!}
        <x-admin.table-cell-primary :title="$title" :actions="$actions" />
    </div>
</td>
```

Pattern thụt lề `str_repeat('&nbsp;', $depth)` lấy từ partial chuẩn `components/admin/partials/tree-select-options.blade.php:8` để đồng bộ visual.

**Edge case đã biết (không fix, thuộc phạm vi khác):** khi filter theo status/keyword bỏ cha nhưng giữ con, node con vẫn hiện với `depth` gốc → có khoảng thụt lề "mồ côi". Đây là hành động có chủ đích (giữ ngữ cảnh cây), chỉ cần agent triển khai biết để không báo nhầm là bug.

---

## Việc 3 — Cập nhật `docs/modules/page.md` cho khớp thực tế

| Vị trí | Sai | Đúng |
|---|---|---|
| Dòng 16 | `PageService.php (126 LOC)` | Cập nhật số LOC thực tế sau khi xóa `getList` (đếm lại file, ≈178) |
| Dòng 25 | `- getList(array $filters) — filter tab/category/keyword` | `- getTreeList(array $filters) — cây phân cấp + filter tab/status/keyword` |
| Dòng 32 | `...kế thừa BaseRepository + getFiltered, updateStatusByIds, deleteByIds, countByStatus, getTree` | `...kế thừa BaseRepository + getTreeList, updateStatusByIds, deleteByIds, countByStatus, getTree` |
| Dòng 39 | `...x-admin.seo-meta, x-admin.language-switcher-widget.` | `...x-admin.seo-meta, x-admin.lang-tabs, x-admin.publish-box, x-admin.image-upload.` |

---

## Rủi ro & cách kiểm tra

**Rủi ro:**
- Xóa `getFiltered` trên interface có thể vỡ binding nếu có implementation khác hoặc API controller nào đó implement interface này. → Đã grep, chỉ có 1 implementation (`PageRepository`). An toàn.
- Blade compile lỗi do cú pháp mới.

**Kiểm tra (agent triển khai chạy):**
1. `php -l` trên 4 file PHP đã sửa.
2. `php artisan view:cache` rồi `php artisan view:clear` — xác nhận blade compile.
3. Vào trình duyệt mở `/admin/pages` (cả tree 2–3 cấp + tab status + keyword filter) — kiểm tra thụt lề đúng theo cấp.
4. Mở `/admin/pages/{uuid}/edit` — kiểm tra form vẫn load bình thường (không bị ảnh hưởng).
5. Grep lại `->getFiltered(` và `service->getList(` trong `app/` — chắc chắn không còn tham chiếu Page.

## Phạm vi loại trừ (OUT OF SCOPE)

- Không thêm pagination cho index (đang render tree toàn bộ — cố ý).
- Không viết tests, không thêm permissions `pages.*` (đã liệt kê trong docs, làm sau).
- Không thêm `author_id` / `is_active` cho pages.
- Không sửa `docs/architecture/10-core-mechanisms.md:11` (tuyến bố `author_id` cho pages/categories) — đây là mâu thuẫn docs khác, cần quyết định riêng về thiết kế, không thuộc đợt này.
