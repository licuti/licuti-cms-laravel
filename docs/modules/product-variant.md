# Module: ProductVariant

> 🚧 **Shell** | Profile: Full | Tầng 4 — Sản phẩm
> Route: `admin.product-variants.*` | Views: `resources/views/admin/product-variants/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `product_variants` / `product_variant_attributes` | ❌ rỗng / **không tồn tại** |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

> ⚠️ **Đánh giá lại Profile:** theo spec `07`, biến thể được quản lý **bên trong form Product** (dynamic JS, `variants[0][sku]...`), không hẳn là module CRUD riêng. Quyết định:
> - **Giữ module riêng** → cần migration + model riêng, index/form riêng.
> - **Hòa vào Product** → xóa Controller/Service/View shell, chuyển logic sang `ProductService::generateVariants` + form Product.

### Nếu giữ module riêng
- `product_variants`: id, product_id FK cascade, sku unique, price, stock_quantity, is_active.
- `product_variant_attributes`: variant_id, attribute_id, value.
- `ProductVariant`: `$fillable`, `product(): BelongsTo`, `attributeValues(): HasMany`.
- `ProductVariantService`: CRUD + kiểm tra sku trùng.
- UI: bảng biến thể thuộc 1 sản phẩm + form sku/price/stock.

Tham khảo: [`07` §3.2](../07-development-process.md), [`03-database-details`](../03-database-details.md) §product_variants.
