# Module: Tag

> 🚧 **Shell — BROKEN** | Profile: **Simple** | Tầng 2 — CMS
> Route: `admin.tags.*` | Views: `resources/views/admin/tags/`
> Spec: [`07` §3.1](../07-development-process.md) (xem Tags trong module Posts)

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| Migration | `..._create_tags_table.php` | ❌ **chỉ id+timestamps** — thiếu `uuid`, `name`, `slug` |
| DB thực tế | dump SQL `tags` | ❌ id+timestamps |
| Pivot | `..._create_post_tag_table.php` | ❌ **chỉ id+timestamps** — thiếu `post_id`, `tag_id` |
| Model | `app/Models/Tag.php` | ❌ **rỗng** — không `$fillable`, không `HasUuid`, không relations |
| FormRequest | `app/Http/Requests/Admin/Tag/{Store,Update}TagRequest.php` | ✅ (tồn tại) |
| DTO | `app/DTOs/Tag/TagDTO.php` | ✅ |
| Repository | `Eloquent/TagRepository` | ⚠️ `getActivePaginated` query cột không tồn tại |
| Service | `app/Services/Admin/Tag/TagService.php` | 🔴 **ghi uuid/name/slug vào cột không tồn tại → SQL error khi tạo** |
| Controller | `app/Http/Controllers/Admin/TagController.php` | ✅ |
| Views | `admin/tags/{index,form}.blade.php` | ✅ |

**Kết luận:** tạo/sửa tag qua admin hiện **lỗi runtime**. Module là ứng viên đầu tiên cho Simple Profile (xem [`07` §1.5](../07-development-process.md)).

## Việc cần làm (theo thứ tự)

### 1. Migration — sửa schema
```php
// tags: id, uuid unique, name, slug (index), timestamps
// post_tag: id, post_id FK cascade, tag_id FK cascade, unique([post_id, tag_id])
```

### 2. Model `Tag`
- `$fillable = ['uuid', 'name', 'slug']`
- `use HasUuid;`
- `posts(): BelongsToMany` (pivot `post_tag`)

### 3. Repository (`TagRepository`)
- Dùng `BaseRepository`; chỉ giữ `searchByName(string $q)` nếu cần gợi ý tag khi nhập bài viết.

### 4. Service — chuyển sang Simple Profile
- **Bỏ DTO** (truyền `$request->validated()`).
- **Bỏ `DB::transaction()` bao quanh 1 lệnh ghi đơn** — không cần.
- Nếu không còn logic gì: **bỏ luôn Service**, Controller inject `TagRepositoryInterface` + bọc ghi trong transaction (theo ngoại lệ Simple Profile).

### 5. Giao diện Admin
- `index.blade.php` — bảng tên + slug + nút xóa (confirm qua `x-admin.row-actions`).
- `form.blade.php` — 2 field: `name` (`x-admin.input`), `slug` (auto-sinh từ name, để trống Service tự sinh).
- Yêu cầu UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

### 6. Kiểm tra ngược
- `PostService::syncTags()` phụ thuộc bảng `post_tag` — sau khi sửa, test lại flow tạo post + tag.
