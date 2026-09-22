# Module: Product

> ✅ **Hoàn thành (CRUD)** | Profile: Full | Tầng 4 — Sản phẩm
> Route: `admin.products.*` | Views: `resources/views/admin/products/`
> Spec: [`07` §3.2](../07-development-process.md) (MODULE: Products) — **module phức tạp nhất, build cuối nhóm Catalog**

## Hiện trạng (audit 09/2026, cập nhật sau đợt stabilize)

| Hạng mục | Trạng thái |
|---|---|
| Migration `products` / `product_translations` | ✅ Đầy đủ (uuid, category_id, brand_id, sku, barcode, price, compare_price, cost_price, stock_quantity, track_inventory, weight, dimensions, is_featured, is_active, status, published_at, soft-delete) |
| Migration `product_variants`, `product_attribute_values` | ✅ Schema; ❌ chưa có logic service/UI |
| Migration `product_images` | ✅ |
| Models (`Product`, `ProductTranslation`, `ProductImage`, `ProductVariant`, `ProductReview`, `ProductAttribute*`) | ✅ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ |
| Test | ✅ `ProductCrudTest` 10 case (CRUD, tab filter, slug unique, SKU unique, authorization) |

## Đã làm trong đợt stabilize (Pha 6+)

- **Tab filter sửa**: `ProductRepository::getActivePaginated` lọc theo `tab` (trước đó lọc `status` — không khớp `x-admin.filter-tabs`, click tab không có tác dụng).
- **Slug uniqueness**: `ProductService` tạo/sửa dùng `generateUniqueSlug('product_translations', ...)` như Post/Page/Category (trước đó dùng `Str::slug()` thô, trùng tên → trùng slug).
- **Authorization**: `StoreProductRequest` → `products.create`, `UpdateProductRequest` → `products.update` (qua `AuthorizesWithPermission`); bulk action gate qua `BulkActionRegistry` (`delete` → `products.delete`, đổi trạng thái → `products.update`).

## Việc cần làm

- [ ] **Biến thể sản phẩm** (phần lớn còn thiếu): `ProductService::generateVariants(Product, array $matrix)`, `updateVariantStock`, UI dynamic JS trong form, bind bảng `product_variants` + `product_attribute_values`.
- [ ] **Thư viện ảnh nhiều ảnh**: form hiện chỉ upload 1 ảnh chính (`images[0]`); relation `images()` + DTO đã sẵn sàng cho gallery nhiều ảnh — thiếu UI `x-admin.image-upload multiple`.
- [ ] Bổ sung `ProductRepository::searchBySku`, `getOutOfStock` (theo spec `07` §3.2) nếu cần cho phần báo cáo / import.

## Phụ thuộc

`Category` ✅ → `Brand` ✅ → `ProductAttribute` ✅ → `Product` ✅ (biến thể đang dở).
