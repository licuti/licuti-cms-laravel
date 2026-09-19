# Module: Setting

> ✅ **Hoàn thành** | Profile: Full | Tầng 1 — Nền tảng
> Route: `admin.settings.{edit,update}` | Views: `resources/views/admin/settings/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `settings` — 10 cột (key, group, value, type, đa ngôn ngữ) | ✅ |
| Model | `app/Models/Setting.php` | ✅ |
| FormRequest | `app/Http/Requests/Admin/Setting/UpdateSettingRequest.php` | ✅ |
| Repository | `Eloquent/SettingRepository` | ✅ |
| Service | `app/Services/Admin/Setting/SettingService.php` | ✅ |
| Controller | `app/Http/Controllers/Admin/SettingController.php` | ✅ |
| Views | `admin/settings/` (tab theo group) | ✅ |
| Test | — | ⚠️ chưa có |

## Danh sách file & hàm

**`SettingService`**:
- `getGroup(string $group)` — lấy toàn bộ setting của 1 group (render form theo type)
- `saveGroup(string $group, array $data)` — lưu nguyên group (transaction)
- `getAllCached()` — đọc toàn bộ setting từ cache (tránh query lặp)
- `get(string $key)` — đọc 1 giá trị (dùng frontend/service khác)

**`SettingRepository`**: kế thừa `BaseRepository` + `getByGroup`, `getByKey`, `updateOrCreateByKey`

**`SettingController`**: `edit, update`

## Giao diện Admin

- Form theo group (general, payment, email...), mỗi setting render theo `type`: text/textarea/toggle/color/number.
- Yêu cầu UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Việc cần làm

- [ ] Bổ sung test save group.
- [ ] Kiểm tra cache invalid khi `saveGroup` (đã có `getAllCached`).
