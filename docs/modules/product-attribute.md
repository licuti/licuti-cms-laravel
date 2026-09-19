# Module: ProductAttribute

> 🚧 **Shell** | Profile: Full | Tầng 3 — Danh mục sản phẩm
> Route: `admin.product-attributes.*` | Views: `resources/views/admin/product-attributes/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `product_attributes` / `product_attribute_translations` | ❌ id+timestamps |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

### 1. Migration (2 bảng)
- `product_attributes`: id, uuid, code (màu-sắc/kich-co...), display_order, timestamps.
- `product_attribute_translations`: id, attribute_id FK cascade, locale, unique([attribute_id, locale]), name.

### 2. Models
- `ProductAttribute`: `HasUuid`, `translations(): HasMany`, `values(): HasMany` (→ `AttributeValue`).
- Bảng `attribute_values`: id, attribute_id FK, value, slug, display_order. *(Xem schema đầy đủ: [`03-database-details`](../03-database-details.md) §product_attributes)*

### 3. Repository / Service
- `ProductAttributeRepository`: `getActiveWithValues()`.
- `ProductAttributeService`: CRUD + translations.

### 4. Giao diện Admin
| Field | Component | name |
|---|---|---|
| Tên thuộc tính | `x-admin.input` | `translations[vi][name]` |
| Mã | `x-admin.input` | `code` |
| Giá trị | dynamic JS (thêm/xóa dòng) | `values[]` |
| Thứ tự | `x-admin.input type=number` | `display_order` |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
