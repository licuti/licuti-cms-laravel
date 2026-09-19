# Module: FlashSale

> 🚧 **Shell** | Profile: Full | Tầng 5 — Giao dịch
> Route: `admin.flash-sales.*` | Views: `resources/views/admin/flash-sales/`
> Spec: [`07` §3.3`](../07-development-process.md) (MODULE: Coupons & Flash Sales)

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `flash_sales` | ❌ id+timestamps |
| Migration **`flash_sale_products`** | ❌ **KHÔNG TỒN TẠI** |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

### 1. Migration
- `flash_sales`: id, uuid, name, start_date, end_date, is_active, timestamps.
- `flash_sale_products`: id, flash_sale_id FK cascade, product_variant_id FK, flash_price, quantity_limit, timestamps.

### 2. Models
- `FlashSale`: `HasUuid`, `casts` (dates), `products(): HasMany`.
- `FlashSaleProduct`: `$fillable`, `variant(): BelongsTo`.

### 3. Service (theo spec `07` §3.3`)
- `getActiveSales(): Collection` — đang diễn ra
- `getProductsOnSale(): Collection`
- `getCurrentPriceForVariant(int $variantId): ?float` — override giá nếu đang flash sale (dùng ở frontend + cart)

### 4. Giao diện Admin
| Field | Component | name |
|---|---|---|
| Tên chương trình | `x-admin.input` | `name` |
| Thời gian | `x-admin.datetime-field` × 2 | `start_date`, `end_date` |
| Danh sách SP + giá sale | dynamic JS table | `products[]` (variant_id, flash_price, quantity_limit) |
| Trạng thái | `x-admin.toggle` | `is_active` |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
