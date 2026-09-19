# Module: Product

> 🚧 **Shell** | Profile: Full | Tầng 4 — Sản phẩm
> Route: `admin.products.*` | Views: `resources/views/admin/products/`
> Spec: [`07` §3.2](../07-development-process.md) (MODULE: Products) — **module phức tặc nhất, build cuối nhóm Catalog**

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `products` / `product_translations` | ❌ id+timestamps — thiếu uuid/category_id/brand_id/sku/price/compare_price/is_active/is_featured + translations |
| Migration `product_variants`, `product_variant_attributes` | ❌ rỗng / **không tồn tại** |
| Migration `product_images` | ⚠️ kiểm tra |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm (theo [`07` §3.2](../07-development-process.md))

### 1. Migration
- `products`: id, uuid, category_id FK, brand_id FK, sku, price, compare_price, weight, dimensions, is_active, is_featured, timestamps.
- `product_translations`: id, product_id FK cascade, locale, unique([product_id, locale]), name, slug, short_description, description, meta_*.
- `product_images`: product_id, media_uuid, display_order.
- `product_variants`: id, product_id FK, sku, price, stock_quantity, is_active.
- `product_variant_attributes`: variant_id, attribute_id, value.

### 2. Models
- `Product`: `HasUuid`, `HasSeo`, relations (`category`, `brand`, `translations`, `images`, `variants`), `scopeActive`.
- `ProductTranslation`, `ProductVariant`, `ProductImage`.

### 3. Repository / Service
- `ProductRepository`: `getPaginated`, `findByUuid`, `getWithVariants`, `searchBySku`, `getOutOfStock`.
- `ProductService`: `create/update` (transaction + translations + images + SEO), `generateVariants(Product, array $matrix)`, `updateVariantStock`.

### 4. Giao diện Admin
Field theo spec [`07` §3.2](../07-development-process.md): tên/slug/mô tả ngắn & chi tiết (TinyMCE), danh mục, thương hiệu, SKU, giá bán, giá gốc, tồn kho, thư viện ảnh (`x-admin.image-upload multiple`), trọng lượng/kích thước, nổi bật, trạng thái, **biến thể động (dynamic JS)**, SEO (`x-admin.seo-meta`).
UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).

## Phụ thuộc
`Category` ✅ → `Brand` 🚧 → `ProductAttribute` 🚧 → `Product`. **Phải hoàn thành Brand và ProductAttribute trước.**
