# Kế hoạch: Dọn meta_* thừa + Quick wins + Docs (Kèm Test-Case-Spec, hoãn viết file test)

> Bối cảnh: Module Page 84% compliance. 3 việc người dùng chốt làm ngay (GĐ1+2+3); file test VIẾT SAU, hiện chỉ ghi spec vào docs.
> Ràng buộc sandbox: Bash deny các lệnh chứa `;`, `|`, `&`, `$( )`, backtick, multi-line → các bước verify phải dùng lệnh đơn dòng (`&&` không dùng được; dùng cmd /c từng lệnh một). Lệnh mysql kiểm tra đã thử bị deny → **GĐ1 thêm bước kiểm tra dữ liệu bằng migration-safe design (không cần query trước)**.

## GĐ1 — Xóa 3 cột `meta_title`/`meta_description`/`meta_keywords` thừa (P0, ~30-40')

**Bối cảnh kỹ thuật**: SEO thật sự đang lưu ở `seo_metadata` (MorphMany, qua `HasSeo::saveSeoTranslations()`, nhận data từ form `translations[xx][meta_*]`). Cột meta_* trên `pages` chỉ bị `PageDTO::toArray()` ghi `null` mỗi save — dead weight. `posts` **không có** cột meta_* (chỉ `pages` bị ảnh hưởng).

### 1.0 An toàn dữ liệu (không cần SELECT trước — sandbox deny)
- Migration `up()` dùng **backfill-up-first**: trước khi drop, đẩy mọi `meta_* != null/''` từ `pages` sang `seo_metadata` (updateOrCreate theo seoable+locale=default, chỉ khi chưa có dòng SEO). Không mất dữ liệu.
- `down()` recreates cột + restore từ seo_metadata của default locale (best-effort) → reversible.

### 1.1 Các file thay đổi
| # | File | Thay đổi |
|---|---|---|
| 1 | `database/migrations/2026_09_16_xxxxxx_drop_duplicate_meta_from_pages_table.php` (MỚI) | up: backfill→seo_metadata, dropIndex/dropColumn 3 cột khỏi `pages`; down: add lại + restore |
| 2 | `app/Models/Page.php` | Bỏ `meta_title`, `meta_description`, `meta_keywords` khỏi `$fillable` |
| 3 | `app/DTOs/Page/PageDTO.php` | Bỏ 3 field khỏi constructor + fromRequest/fromArray/toArray |
| 4 | `app/Http/Requests/Admin/Page/StorePageRequest.php` | Bỏ 3 rule validation tương ứng |
| 5 | Grep verify | `rg "meta_title|meta_description|meta_keywords" app/ database/factories/` còn lại chỉ: `HasSeo.php`, `SeoTranslations` (seo_metadata — ĐÚNG), `PostCategory/PageTranslation` không có; `pages/form.blade.php` seo-meta uses translations[xx][meta_*] → ĐÚNG. Nếu PageFactory set meta_* → bỏ key đó. |

**Verify**: `php artisan migrate` (lệnh đơn dòng) → chạy OK; `page_seeder` flow; open `/admin/pages/.../edit`: SEO card vẫn prefilled đúng từ seo_metadata; save form → `seo_metadata` update, không còn ghi null.

## GĐ2 — Quick wins (P1, ~1h, theo phát hiện compliance audit trước)

| # | File | Thay đổi (trích từ audit) |
|---|---|---|
| 2.1 | `app/Services/Admin/Page/PageService.php:11` | Xóa `use Illuminate\Support\Facades\Cache` dead import |
| 2.2 | `app/Repositories/Eloquent/PageRepository.php:23-30` | Gộp filter `status` vào `tab` (giữ `tab`, xóa block `status` redundant) |
| 2.3 | `app/Http/Requests/Admin/Page/StorePageRequest.php` | + `slug` regex: `'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'` (nullable; rỗng=service tự sinh); message tiếng Việt + test spec tương ứng (#7 feature) |
| 2.4 | `app/Services/Admin/Page/PageService.php` | + `getTemplateOptions(): array` cached static (mirror getStatusOptions); `PageController::formViewData` gọi service thay vì collect() inline |
| 2.5 | `StorePageRequest` | + `prepareForValidation()`: cast `parent_id` rỗng/'0'→null, trim `page_template` |
| 2.6 | Run | `php artisan config:clear` + `php -l` các file đổi + `php artisan test` (giữ ≥32 pass) |

## GĐ3 — Tài liệu hóa (P1, ~1.5-2h) — THAY thế file test

### 3.1 `docs/02-database-overview.md`
- Nhóm CMS: `pages` schema mới (uuid, parent_id, page_template, image, display_order, status(ContentStatus), published_at, deleted_at, KHÔNG còn meta_*); `page_translations` (title, slug index, excerpt, content, unique(page_id,locale), created_at/updated_at giải thích per-locale audit — dùng phần Q&A đã trả lời); `seo_metadata` (MorphMany per-locale: meta_*, og_*, canonical, robots, schema_type) → nguồn SEO duy nhất.

### 3.2 `docs/04-permissions.md`
- Đọc format file trước. Thêm mục "Module Page": trạng thái thực tế = **auth-only** (PageController authorize() `auth()->check()`, route middleware `auth:web`), chưa có permission-code riêng → **ghi rõ đây là gap (P3)** để sau này seed codes `pages.view/create/edit/delete/publish/bulk` + Policy. Không fabricate codes chưa tồn tại trong DB.
- Ghi chú: khi thêm codes phải cập nhật docs này + Checklist §6.

### 3.3 `docs/architecture/15-testing.md` — phần MỚI "Test-Case-Spec"
Thêm sections (KHÔNG code test, chỉ bảng spec + template):
- `### Module Page — Test-Case-Spec`
  - PageServiceTest: 12 cases (create+slug auto, slug-dup suffix, update-transaction rollback, saveSeoTranslations ghi seo_metadata, **assertParentSafe 42**2: self-parent, descendant-parent; null-parent allowed)**, 
  - PageRepositoryTest: 8 cases (tab filter, keyword→title, per-page 5/15/100 clamp, deleteByIds count, countByStatus, getTree exclude-subtree)
  - PageCrudTest: 10 cases (guest redirect, index 200+filter-tabs, store→302 index, save-and-edit→302 edit, invalid status 422, missing default-locale title, **slug regex reject (mới từ 2.3)**, edit prefill, update persists, bulk delete)
- Ghi chú implement: pattern `RefreshDatabase` + `WithLanguages` + tước `Cache::flush` lang; mock PostRepository; target coverage 80/70/60%.
- **Trigger to write tests**: khi module sắp merge stable (thêm feature mới, refactor DB/schema, production-incident cần regression).

### 3.4 `docs/architecture/13-ui-conventions.md`
- Thêm quy tắc #7-9 vào mục cuối: `#7` Card "Xuất bản" → dùng `<x-admin.publish-box>`; ngày giờ → `<x-admin.datetime-field>` (clear button + default-now built-in); `#8` SEO form → `<x-admin.seo-meta>` per-locale, **không** dùng cột meta_* trên bảng cha; `#9` select cây (parent) dùng `partials/tree-select-options` pattern.

### 3.5 (tuỳ chọn nhanh) `DEVELOPER.md`
- Trong `17-git-workflow.md` đã có workflow; thêm 1 dòng ở mục 6 "Bổ sung" nếu docs cần.

## Thứ tự thực hiện khi approve Build
GĐ1 (migration) → GĐ2 (code) → verify test pass → GĐ3 docs → verify `rg meta_` + `git status`.
Mỗi bước: `php -l` file PHP đổi; cuối chạy `php artisan test`; **không commit** (user chưa yêu cầu) — chờ lệnh push riêng.

## Rủi ro
1. Migration dùng DB::statement UPDATE...SELECT cho backfill (MySQL) — bọc trong transaction Laravel migration (already). Nếu seo_metadata đã có dòng cho page đó → updateOrCreate chỉ điền khi null (COALESCE logic trong PHP loop).
2. `down()` không restore hoàn hảo (seo_metadata per-locale vs page-level) — chấp nhận, chỉ là dev/staging; ghi note trong docblock.
3. Slug regex có thể chặn giá trị đã lưu từ trước nếu validate lại bản cũ → regex accept đúng format Str::slug (a-z0-9 + dash) → slug đã sinh luôn match; user tự nhập slug viết hoa/trễ sẽ fail → message hướng dẫn.
4. Permissions docs ghi gap thật — không làm, chỉ ghi.

## Definition of Done
- [ ] `php artisan migrate` OK; trang edit SEO prefilled; không còn null ghi vào meta_* (cột không tồn tại)
- [ ] `php -l` OK, `php artisan test` ≥32 pass, `rg meta_ app/` chỉ khớp HasSeo/Seo* 
- [ ] 4 docs file updated; `15-testing.md` có Test-Case-Spec Page ≥30 cases
- [ ] Changelog note trong báo cáo cho user (không tự commit/push)
