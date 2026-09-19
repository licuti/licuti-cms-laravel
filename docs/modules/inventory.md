# Module: Inventory

> 🚧 **Shell** | Profile: Full | Tầng 7 — Tồn kho
> Route: `admin.inventories.*` | Views: `resources/views/admin/inventories/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `inventories` | ❌ id+timestamps |
| Migration **`inventory_histories`** | ❌ **KHÔNG TỒN TẠI** |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

### 1. Migration
- `inventories`: id, uuid, warehouse_id FK, product_variant_id FK, quantity, reorder_point, unique([warehouse_id, product_variant_id]).
- `inventory_histories`: id, inventory_id FK cascade, change int, type (import/export/adjust), note, created_by FK, timestamps.

### 2. Models
- `Inventory`: `HasUuid`, `casts` (quantity integer), `warehouse()`, `variant()`, `histories()`.
- `InventoryHistory`: `$fillable`, `casts`.

### 3. Repository / Service
- `InventoryRepository`: `getLowStock()`, `getByVariant(int $variantId)`.
- `InventoryService`: `import(int $inventoryId, int $qty, string $note)`, `export(...)`, `adjust(...)` — mỗi thao tác ghi history trong transaction.

### 4. Giao diện Admin
- `index.blade.php` — bảng tồn kho: variant, kho, số lượng, mức cảnh báo (badge đỏ khi < reorder_point), actions import/export.
- Form nhập/xuất: số lượng + ghi chú (modal `x-admin.modal`).
- UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Phụ thuộc
`Warehouse` 🚧 → `Inventory` → `ProductVariant` 🚧.
