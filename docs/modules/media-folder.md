# Module: MediaFolder

> ✅ **Hoàn thành** | Profile: Simple | Tầng 0 — Core
> Route: `admin.media-folders.*` (chỉ store/destroy) | Không có view riêng (nhúng trong Media)

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `media_folders` — 8 cột (uuid, name, parent_id) | ✅ |
| Model | `app/Models/MediaFolder.php` | ✅ |
| Repository | `Eloquent/MediaFolderRepository` | ✅ |
| Service | `app/Services/Admin/MediaFolderService.php` | ✅ |
| Controller | `app/Http/Controllers/Admin/MediaFolderController.php` | ✅ |
| Test | — | ⚠️ chưa có |

## Danh sách file & hàm

**`MediaFolderService`**:
- `create(array $data)` — tạo thư mục con
- `delete(string $uuid)` — xóa thư mục (kiểm tra ảnh con trước khi xóa)

**`MediaFolderRepository`**: kế thừa `BaseRepository` + `getSubFolders`, `getBreadcrumbs` (điều hướng folder cha)

**`MediaFolderController`**: `store, destroy`

## Giao diện Admin

Không có view riêng — thao tác nằm trong `admin/media/index.blade.php` (tạo/xóa folder qua AJAX).

## Việc cần làm

- [ ] Bổ sung test create/delete.
- [ ] Kiểm tra rule xóa folder có ảnh: hiện tại báo lỗi hay cascade?
