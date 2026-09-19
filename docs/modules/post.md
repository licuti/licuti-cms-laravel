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

## Việc cần làm

- [ ] Kiểm tra `syncTags` — do module `Tag` đang broken (DB thiếu cột), việc gán tag khi lưu post có thể lỗi.
