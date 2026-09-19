# Module: Brand

> 🚧 **Shell** | Profile: Full | Tầng 3 — Danh mục sản phẩm
> Route: `admin.brands.*` | Views: `resources/views/admin/brands/`
> Spec: [`07` §3.2](../07-development-process.md) (Product Categories & Brands)

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `brands` / `brand_translations` | ❌ id+timestamps — thiếu uuid/name/logo/is_active/display_order + translations |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

### 1. Migration (2 bảng)
- `brands`: id, uuid, logo (media uuid), is_active, display_order, timestamps.
- `brand_translations`: id, brand_id FK cascade, locale, unique([brand_id, locale]), name, slug, description.

### 2. Models
- `Brand`: `$fillable`, `HasUuid`, `casts`, `translations(): HasMany`.
- `BrandTranslation`: `$fillable`, `slug` index.

### 3. Repository / Service
- `BrandRepository`: thêm `getActiveBrands()` (cho select ở form Product).
- `BrandService`: `create/update` + `saveTranslations` + `generateUniqueSlug` + SEO.

### 4. Giao diện Admin
| Field | Component | name |
|---|---|---|
| Tên | `x-admin.input` | `translations[vi][name]` |
| Slug | `x-admin.input` | `translations[vi][slug]` |
| Logo | `x-admin.image-upload` | `logo` |
| Mô tả | `x-admin.textarea` | `translations[vi][description]` |
| Trạng thái | `x-admin.toggle` | `is_active` |
| Thứ tự | `x-admin.input type=number` | `display_order` |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
