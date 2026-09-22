# Module: Page

> ✅ **Hoàn thành** | Profile: Full | Tầng 2 — CMS
> Route: `admin.pages.*` | Views: `resources/views/admin/pages/`
> Là module chuẩn mực nhất — tham khảo khi build module mới.

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `pages` + `page_translations` (refactor 09/2026: thêm `status`, `page_template`, `parent_id`, `published_at`; SEO nằm hết ở `seo_metadata`) | ✅ |
| Model | `app/Models/Page.php` (+ Translation, `HasUuid`, `HasSeo`) | ✅ |
| FormRequest | `app/Http/Requests/Admin/Page/{Store,Update}PageRequest.php` | ✅ |
| DTO | `app/DTOs/Page/PageDTO.php` | ✅ |
| Repository | `Eloquent/PageRepository` | ✅ |
| Service | `app/Services/Admin/Page/PageService.php` (178 LOC) | ✅ |
| Controller | `app/Http/Controllers/Admin/PageController.php` | ✅ |
| Views | `admin/pages/{index,form}.blade.php` | ✅ |
| Test | ⏳ **hoãn** — spec đầy đủ trong [`15-testing` §6](../architecture/15-testing.md) |

## Danh sách file & hàm

**`PageService`**:
- `getStatusOptions()` / `getTemplateOptions()` — enum `ContentStatus` + `PageTemplate`
- `getTreeList(array $filters)` — cây phân cấp (depth + has_children) + filter tab/status/keyword
- `findByUuid(string $uuid)`
- `create(PageDTO $dto)` / `update(string $uuid, PageDTO $dto)` — transaction + translations + `generateUniqueSlug` + SEO + bảo vệ cây phân cấp (chặn parent là chính nó / con cháu)
- `delete(string $uuid)`
- `getTabs()` — đếm theo status
- `getParentOptions()` — `<select>` danh mục cha (loại subtree của chính node khi edit)

**`PageRepository`**: kế thừa `BaseRepository` + `getTreeList`, `updateStatusByIds`, `deleteByIds`, `countByStatus`, `getTree`

**`PageController`**: `index, create, store, edit, update, destroy, bulk`

## Giao diện Admin

- `index.blade.php` — filter-tabs + bảng (thụt lề theo `depth`, icon ▾/• cho node có con/lá) + bulk actions.
- `form.blade.php` — lang-tabs đa ngôn ngữ, tiêu đề/slug/content (TinyMCE), template, parent, status, publish box, `x-admin.seo-meta`, `x-admin.image-upload`.
- Yêu cầu UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Việc cần làm

- [ ] Viết test theo spec 12 case `PageServiceTest` + 8 case `PageRepositoryTest` + 10 case `PageCrudTest` (đã có sẵn spec trong [`15-testing` §6](../architecture/15-testing.md)).
- [x] Áp permission `pages.*` (Pha 3) + gate bulk action qua `BulkActionRegistry` (Pha 6 — `delete` → `pages.delete`, đổi trạng thái → `pages.update`).
