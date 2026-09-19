# Module: PostCategory

> ✅ **Hoàn thành** | Profile: Full | Tầng 2 — CMS
> Route: `admin.post-categories.*` | Views: `resources/views/admin/post-categories/`
> Spec: [`07-development-process` §3.1](../07-development-process.md) (MODULE: Post Categories)

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `post_categories` + `post_category_translations` | ✅ |
| Model | `app/Models/PostCategory.php` (+ Translation) | ✅ |
| FormRequest | `app/Http/Requests/Admin/PostCategory/{Store,Update}PostCategoryRequest.php` | ✅ |
| DTO | `app/DTOs/PostCategory/PostCategoryDTO.php` | ✅ |
| Repository | `Eloquent/PostCategoryRepository` | ✅ |
| Service | `app/Services/Admin/PostCategory/PostCategoryService.php` | ✅ |
| Controller | `app/Http/Controllers/Admin/PostCategoryController.php` | ✅ |
| Views | `admin/post-categories/{index,form}.blade.php` | ✅ |
| Test | — | ⚠️ chưa có |

## Danh sách file & hàm

**`PostCategoryService`**:
- `getList(array $filters)` — cây/phân trang + filter
- `getByUuid(string $uuid)`
- `getAllActive()` — cho `<select>` ở form Post
- `create(PostCategoryDTO $dto)` / `update(string $uuid, PostCategoryDTO $dto)` — lưu translation + `generateUniqueSlug` + SEO
- `delete(string $uuid)` — kiểm tra category con/bài viết liên quan

**`PostCategoryRepository`**: kế thừa `BaseRepository` + `getActivePaginated`, `getAllForList`, `getAllActive`, `getTree`

**`PostCategoryController`**: `index, create, store, edit, update, destroy, bulk`

## Giao diện Admin

Field theo spec [`07` §3.1](../07-development-process.md): tên/slug/mô tả đa ngôn ngữ (`translations[locale][...]`), ảnh đại diện (`x-admin.image-upload`), danh mục cha (`x-admin.select`), thứ tự, trạng thái (`x-admin.toggle`).
UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Việc cần làm

- [ ] Bổ sung `PostCategoryServiceTest` + `PostCrudTest` tương đương.
