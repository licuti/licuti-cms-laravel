# Module: Language

> ✅ **Hoàn thành** | Profile: Full | Tầng 1 — Nền tảng
> Route: `admin.languages.*` | Views: `resources/views/admin/languages/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| DB | `languages` — 10 cột (code, name, native_name, is_default, is_active, flag) | ✅ |
| Model | `app/Models/Language.php` | ✅ |
| FormRequest | `app/Http/Requests/Admin/Language/{Store,Update}LanguageRequest.php` | ✅ |
| Repository | `Eloquent/LanguageRepository` | ✅ |
| Service | `app/Services/Admin/Language/LanguageService.php` + `LanguageResolver` | ✅ |
| Controller | `app/Http/Controllers/Admin/LanguageController.php` | ✅ |
| Views | `admin/languages/{index,form}.blade.php` | ✅ |
| Test | — | ⚠️ chưa có |

## Danh sách file & hàm

**`LanguageService`** — business rules đặc thù:
- `createLanguage(LanguageDTO $dto)` — tạo; nếu `is_default` thì bỏ default của các ngôn ngữ khác + clear cache
- `updateLanguage(Language $language, LanguageDTO $dto)` — chặn tắt/không-set-default trực tiếp ngôn ngữ mặc định (ném `LanguageException`)
- `deleteLanguage(Language $language)` — chặn xóa ngôn ngữ mặc định

**`LanguageResolver`** — helper toàn cục:
- `getActiveLanguages()`, `getDefaultLanguage()`, `getCurrentLanguage()` — dùng cho mọi module đa ngôn ngữ

**`LanguageRepository`**: kế thừa `BaseRepository` + `getActiveLanguages`, `clearCache`, `getPaginatedLanguages`

**`LanguageController`**: `index, create, store, edit, update, destroy`

## Giao diện Admin

- `index.blade.php` — danh sách ngôn ngữ + badge default.
- `form.blade.php` — code, name, native_name, flag (upload), is_default, is_active.

## Việc cần làm

- [ ] **L4**: `LanguageService` hiện không kế thừa `BaseService` và dùng raw `DB::beginTransaction()` thay vì `$this->handleTransaction()` — chuyển lại cho đồng nhất.
- [ ] Bổ sung test (đặc biệt rule không xóa/hủy default).
