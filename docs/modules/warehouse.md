# Module: Warehouse

> 🚧 **Shell** | Profile: **Simple** | Tầng 7 — Tồn kho
> Route: `admin.warehouses.*` | Views: `resources/views/admin/warehouses/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `warehouses` | ❌ id+timestamps — thiếu uuid/code/name/address/is_active |
| Migration `inventories`, `inventory_histories` | ❌ rỗng / **không tồn tại** |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

Ứng viên **Simple Profile** ([`07` §1.5](../07-development-process.md)).

### 1. Migration
- `warehouses`: id, uuid, code unique, name, address, is_active, timestamps.
- `inventories`: id, warehouse_id FK, product_variant_id FK, quantity, reorder_point, unique([warehouse_id, product_variant_id]).
- `inventory_histories`: id, inventory_id FK cascade, change (±), type (import/export/adjust), note, created_by, timestamps.

### 2. Models
- `Warehouse`: `$fillable`, `HasUuid`, `inventories(): HasMany`.

### 3. Repository / Service
- Repository: `BaseRepository` + `getActiveWithStock()`.
- Service: CRUD đơn giản (bỏ DTO, theo Simple Profile).

### 4. Giao diện Admin
| Field | Component | name |
|---|---|---|
| Mã kho | `x-admin.input` | `code` |
| Tên | `x-admin.input` | `name` |
| Địa chỉ | `x-admin.textarea` | `address` |
| Trạng thái | `x-admin.toggle` | `is_active` |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
