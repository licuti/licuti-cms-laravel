# Module: Post

> ✅ **Hoàn thành** | Profile: Full | Tầng 2 — CMS
> Route: `admin.posts.*` | Views: `resources/views/admin/posts/`
> Spec: [`07-development-process` §3.1](../07-development-process.md) (MODULE: Posts)

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `posts` + `post_translations` + `post_tag` | ✅ |
| Model | `app/Models/Post.php`, `Tag.php` | ✅ |
| FormRequest | `app/Http/Requests/Admin/Post/{Store,Update}PostRequest.php` | ✅ |
| DTO | `app/DTOs/Post/PostDTO.php` | ✅ |
| Repository | `Eloquent/PostRepository` | ✅ |
| Service | `app/Services/Admin/Post/PostService.php` | ✅ |
| Controller | `app/Http/Controllers/Admin/PostController.php` | ✅ |
| Views | `admin/posts/{index,form}.blade.php` | ✅ |
| Test | `PostServiceTest` + `PostCrudTest` | ✅ |

## Danh sách file & hàm

**`PostService`**:
- `getStatusOptions()` / `getStatusMap()` — enum `ContentStatus` → label/badge màu
- `getList(array $filters)` — filter theo tab (status), category, keyword
- `create(PostDTO $dto)` / `update(string $uuid, PostDTO $dto)` — transaction, lưu translation + slug + SEO + sync tags
- `delete(string $uuid)` — soft delete
- `getTabs()` — đếm theo status cho `<x-admin.filter-tabs>`

**`PostRepository`**: kế thừa `BaseRepository` + `getFiltered`, `updateStatusByIds`, `deleteByIds`, `countByStatus`

**`PostController`**: `index, create, store, edit, update, destroy, bulk`

## Giao diện Admin

Field theo spec [`07` §3.1](../07-development-process.md): tiêu đề/slug/excerpt/content đa ngôn ngữ, ảnh đại diện, danh mục, tags, nổi bật, status, ngày xuất bản, SEO (`x-admin.seo-meta`).
Content dùng TinyMCE: `class="tinymce-editor"` + `<x-admin.scripts.tinymce />`.
UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

### Đa ngôn ngữ — một ngôn ngữ mỗi lần (2026-11-11)

- Form chỉ render **một** `.lang-panel` theo `$currentLocale`; không còn tabs render mọi ngôn ngữ (tránh N TinyMCE + N bộ SEO).
- Sidebar dùng `<x-admin.language-switcher-widget>`: hiện ngôn ngữ đang sửa + danh sách bản dịch khác kèm nút sửa/thêm (`?lang=`). Nút chỉ xuất hiện ở chế độ sửa; khi tạo mới chỉ có gợi ý lưu trước (tránh tạo bản ghi mới thay vì bản dịch).
- Form có `data-unsaved-guard` + `<x-admin.scripts.unsaved-guard />`: cảnh báo `beforeunload` và confirm khi bấm link chuyển ngôn ngữ nếu có thay đổi chưa lưu.
- `PostController::formViewData` chọn `currentLocale` từ `?lang=` (chỉ ngôn ngữ active) và truyền `languages`; tạo mới luôn dùng ngôn ngữ mặc định.
- `UpdatePostRequest` bắt buộc tiêu đề theo **ngôn ngữ đang sửa**; `StorePostRequest` vẫn bắt buộc tiêu đề ngôn ngữ mặc định.
- `PostService::saveTranslations` sẵn có `updateOrCreate(['locale' => ...])` nên lưu một locale **không xóa** bản dịch khác (đã có test).
- Test: `PostCrudTest` (15 test) phủ lưu theo locale, giữ bản dịch khác, validation theo locale, view một panel, create không có link bản dịch, redirect giữ `?lang=`.

## Việc cần làm

- [ ] Kiểm tra `syncTags` — do module `Tag` đang broken (DB thiếu cột), việc gán tag khi lưu post có thể lỗi.
