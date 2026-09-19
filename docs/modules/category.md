# Module: Category (Danh mục sản phẩm)

> 🟡 **Hoàn thành - Cần kiểm tra và điều chỉnh** | Profile: Full | Tầng 3 — Danh mục sản phẩm
> Route: `admin.categories.*` | Views: `resources/views/admin/categories/`
> Spec: [`07-development-process` §3.2](../07-development-process.md) (Product Categories & Brands)

## Hiện trạng (audit & refactor 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `categories` + `category_translations` (9/11 cột thật) | ✅ |
| Model | `app/Models/Category.php` (+ Translation) | ✅ |
| FormRequest | `app/Http/Requests/Admin/Category/{Store,Update}CategoryRequest.php` | ✅ |
| DTO | `app/DTOs/Category/CategoryDTO.php` | ✅ |
| Repository | `Eloquent/CategoryRepository` | ✅ |
| Service | `app/Services/Admin/Category/CategoryService.php` | ✅ |
| Controller | `app/Http/Controllers/Admin/CategoryController.php` | ✅ |
| Views | `admin/categories/{index,form,_quick_form}.blade.php` | ✅ (chuẩn Bootstrap 5.3 theo mẫu Post) |
| Test | — | ⚠️ chưa có |
| **Lỗi L1** | `resources/views/admin/categorys/` | ✅ Đã xóa sạch thư mục orphan |

## Danh sách file & hàm

**`CategoryService`**:
- `getList(array $filters)` — cây + phân trang
- `getByUuid(string $uuid)`
- `getAllActive()` — cho `<select>` ở form Product
- `create(CategoryDTO $dto)` / `update(string $uuid, CategoryDTO $dto)` — translation + slug + SEO
- `delete(string $uuid)` — bảo vệ xóa khi có sản phẩm con
- `bulkAction(...)` — qua registry

**`CategoryRepository`**: kế thừa `BaseRepository` + `getActivePaginated`, `getAllForList`, `getAllActive`

**`CategoryController`**: `index, create, store, edit, update, destroy, bulk`

## Giao diện Admin

Field theo spec [`07` §3.2](../07-development-process.md): tên/slug/mô tả đa ngôn ngữ, ảnh, danh mục cha, thứ tự, trạng thái.
UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Việc cần làm

- [x] **L1**: xóa thư mục `resources/views/admin/categorys/`.
- [x] Chuẩn hóa giao diện `index.blade.php`, `form.blade.php`, `_quick_form.blade.php` sang Bootstrap 5.3 theo mẫu `Post`.
- [ ] Bổ sung test Unit & Feature.
