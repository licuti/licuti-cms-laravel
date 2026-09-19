# Module: Banner

> 🚧 **Shell** | Profile: Full | Tầng 2 — CMS
> Route: `admin.banners.*` | Views: `resources/views/admin/banners/`
> Spec: [`07` §3.1](../07-development-process.md) (MODULE: Banners)

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| Migration `banners` | dump SQL | ❌ id+timestamps — thiếu uuid/position/link/display_order/start_date/end_date/is_active |
| Migration `banner_translations` | dump SQL | ❌ id+timestamps — thiếu banner_id/locale/title/image |
| Model | `app/Models/Banner.php` | ❌ kiểm tra `$fillable` |
| FormRequest | `.../Admin/Banner/{Store,Update}BannerRequest.php` | ✅ |
| DTO | `app/DTOs/Banner/BannerDTO.php` | ✅ |
| Repository | `Eloquent/BannerRepository` | ⚠️ `getActivePaginated` query cột không tồn tại |
| Service | `BannerService` (51 LOC) | ⚠️ shell CRUD |
| Controller | `BannerController` | ✅ |
| Views | `admin/banners/{index,form}.blade.php` | ✅ |

## Việc cần làm

### 1. Migration (2 bảng)
- `banners`: id, uuid, position (enum: home_slider/sidebar/header/footer), link, display_order, start_date, end_date, is_active, timestamps.
- `banner_translations`: id, banner_id FK cascade, locale, unique([banner_id, locale]), title, image.

### 2. Models
- `Banner`: `$fillable`, `HasUuid`, `casts` (is_active boolean, start/end date), `translations(): HasMany`.
- `BannerTranslation`: `$fillable`, belongsTo.

### 3. Repository / Service
- `BannerRepository`: thêm `getActiveByPosition(string $position)` — dùng cho frontend.
- `BannerService`: `create/update` bọc `handleTransaction` + `saveTranslations` + `generateUniqueSlug` nếu cần slug; xóa kiểm tra đang dùng.

### 4. Giao diện Admin
Field theo spec [`07` §3.1](../07-development-process.md):
| Field | Component | name |
|---|---|---|
| Tiêu đề (mỗi locale) | `x-admin.input` | `translations[vi][title]` |
| Hình ảnh | `x-admin.image-upload` | `translations[vi][image]` |
| Link đích | `x-admin.input` | `link` |
| Vị trí | `x-admin.select` | `position` |
| Ngày bắt đầu/kết thúc | `x-admin.datetime-field` | `start_date`, `end_date` |
| Thứ tự | `x-admin.input type=number` | `display_order` |
| Trạng thái | `x-admin.toggle` | `is_active` |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
