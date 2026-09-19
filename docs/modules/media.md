# Module: Media

> ✅ **Hoàn thành** | Profile: Full | Tầng 0 — Core
> Route: `admin.media.*` | Views: `resources/views/admin/media/index.blade.php`

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `media` — 14 cột (uuid, folder, mime, disk, alt...) | ✅ |
| Model | `app/Models/Media.php` | ✅ |
| Repository | `Eloquent/MediaRepository` | ✅ |
| Service | `app/Services/Admin/MediaService.php` | ✅ |
| Controller | `app/Http/Controllers/Admin/MediaController.php` | ✅ |
| Views | `admin/media/index.blade.php` | ✅ |
| Test | — | ⚠️ chưa có |

## Danh sách file & hàm

**`MediaService`**:
- `getPaginated(array $filters)` — thư viện ảnh phân trang (lọc theo folder, loại file)
- `getByUuid(string $uuid)`
- `upload(UploadedFile $file, ...)` — upload 1 file (xử lý disk, tối ưu ảnh)
- `uploadMultiple(array $files)` — upload hàng loạt
- `delete(string $uuid)` — xóa file (DB + storage)

**`MediaRepository`**: kế thừa `BaseRepository` + `getPaginatedMedia`

**`MediaController`**: `index, store, destroy, bulkDestroy`

## Giao diện Admin

- `index.blade.php` — lưới/thumbnail ảnh + chọn folder + upload (drop zone) + modal picker toàn cục `#global-media-picker` (dùng chung cho mọi `<x-admin.image-upload>` / `<x-admin.media-picker>`).
- Yêu cầu UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Việc cần làm

- [ ] Bổ sung test upload/delete.
- [ ] Kiểm tra cleanup file vật lý khi `delete()` (tránh rác trong `storage/app/public`).
