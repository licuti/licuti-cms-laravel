# Module: User

> ✅ **Hoàn thành** | Profile: Full | Tầng 0 — Core
> Route: `admin.users.*` | Views: `resources/views/admin/users/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `users` — 22 cột (có `is_admin`, soft-delete, `last_login_at`) | ✅ |
| Model | `app/Models/User.php` | ✅ |
| FormRequest | `app/Http/Requests/Admin/User/{Store,Update}UserRequest.php` | ✅ |
| Repository | `Interfaces/UserRepositoryInterface` → `Eloquent/UserRepository` | ✅ |
| Service | `app/Services/Admin/User/UserService.php` | ✅ |
| Controller | `app/Http/Controllers/Admin/UserController.php` | ✅ |
| Views | `admin/users/{index,form}.blade.php` | ✅ |
| Test | — | ⚠️ chưa có |

## Danh sách file & hàm

**`UserService`** — logic nghiệp vụ:
- `getList(array $filters)` — danh sách phân trang + filter (keyword, status, role, is_admin, thùng rác)
- `getByUuid(string $uuid)` — lấy user theo uuid (cho trang sửa)
- `create(array $data)` — tạo user (mã hóa mật khẩu, sinh uuid qua model)
- `update(string $uuid, array $data)` — cập nhật (xử lý mật khẩu trống = giữ cũ)
- `delete(string $uuid)` — xóa mềm
- `syncRoles(string $uuid, array $roles)` — gán vai trò (RBAC)
- `restore(string $uuid)` / `forceDelete(string $uuid)` — khôi phục / xóa vĩnh viễn
- `bulkDelete` / `bulkUpdateStatus` / `bulkRestore` / `bulkForceDelete` / `resolveActions` — thao tác hàng loạt qua `BulkActionRegistry`

**`UserRepository`** — query:
- Kế thừa `BaseRepository` + `findByEmail`, `findByEmailOrFail`, `findByPhone`, `existsByEmail`, `updateLastLogin`, `getPaginatedUsers`

**`UserController`** — `index, create, store, edit, update, destroy, restore, forceDelete, assignRoles, bulkAction`

## Giao diện Admin

- `index.blade.php` — bảng users + filter + bulk actions + cột actions (sửa, xóa, khôi phục).
- `form.blade.php` — tạo/sửa user + chọn vai trò.
- Yêu cầu UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md) (dùng `x-admin.table`, `x-admin.filter-tabs`, `x-admin.form-group`).

## Việc cần làm

- [ ] Bổ sung `tests/Feature/Admin/UserCrudTest.php` + `tests/Unit/Services/UserServiceTest.php`.
- [ ] Áp dụng permission `users.*` qua middleware/policy (đang chỉ check `auth`).
- [ ] Kiểm tra `resolveActions` có khớp với `BulkActionServiceProvider` không.
