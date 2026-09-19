# Module: Role & Permission

> ✅ **Hoàn thành** | Profile: Full | Tầng 0 — Core
> Route: `admin.roles.*` | Views: `resources/views/admin/roles/` (chỉ index — tạo/sửa qua modal)

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `roles`, `permissions` + pivot spatie (`role_has_permissions`, `model_has_roles`...) | ✅ |
| Model | `app/Models/Role.php`, `app/Models/Permission.php` | ✅ |
| FormRequest | `app/Http/Requests/Admin/Role/{Store,Update}RoleRequest.php` | ✅ |
| Repository | `Eloquent/RoleRepository` | ✅ |
| Service | `app/Services/Admin/Role/RoleService.php` | ✅ |
| Controller | `app/Http/Controllers/Admin/RoleController.php` | ✅ |
| Views | `admin/roles/index.blade.php` (dùng modal thay form riêng) | ✅ |
| Test | — | ⚠️ chưa có |

## Danh sách file & hàm

**`RoleService`**:
- `getAllWithCount()` — danh sách role kèm số user đang dùng
- `getByIdOrName($idOrName)` — lấy role theo id hoặc tên
- `create(array $data)` / `update($id, array $data)` / `delete($id)` — CRUD role
- `getAllPermissions()` — danh sách permission để gán

**`RoleRepository`**: kế thừa `BaseRepository` + `findByName`, `findByNameOrFail`, `getRolesWithCount`

**`RoleController`**: `index, store, update, destroy, editPermissions, updatePermissions`

## Giao diện Admin

- `index.blade.php` — bảng roles + nút "Thêm vai trò" mở modal (`x-admin.modal`), cột actions gán quyền.
- Yêu cầu UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Việc cần làm

- [ ] Bổ sung test CRUD role.
- [ ] Seed permission `pages.bulk` + đưa phân quyền về middleware `can:roles.*` / `RolePolicy` (nợ P3, xem [`04-permissions`](../04-permissions.md) note).
