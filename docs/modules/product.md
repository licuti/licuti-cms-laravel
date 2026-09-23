# Module: Product

> ✅ **Hoàn thành (CRUD + Biến thể)** | Profile: Full | Tầng 4 — Sản phẩm
> Route: `admin.products.*` | Views: `resources/views/admin/products/`
> Spec: [`07` §3.2](../07-development-process.md) (MODULE: Products) — **module phức tạp nhất, build cuối nhóm Catalog**

## Hiện trạng (audit 09/2026, cập nhật sau đợt thuộc tính & biến thể)

| Hạng mục | Trạng thái |
|---|---|
| Migration `products` / `product_translations` | ✅ Đầy đủ (uuid, category_id, brand_id, sku, barcode, price, compare_price, cost_price, stock_quantity, track_inventory, weight, dimensions, is_featured, is_active, status, published_at, soft-delete) |
| Migration `product_variants`, pivot `product_attribute` / `product_attribute_value` / `product_variant_attribute_values` | ✅ Đầy đủ + logic service |
| Migration `product_images` | ✅ |
| Models (`Product`, `ProductTranslation`, `ProductImage`, `ProductVariant`, `ProductReview`, `ProductAttribute*`) | ✅ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ |
| Test | ✅ `ProductCrudTest` 14 case + `ProductAttributeVariantTest` 10 case (CRUD, tab filter, slug unique, SKU unique, authorization, image gallery, sinh/dỡ biến thể, custom attribute, guard nổ tổ hợp) |

## Đã làm trong đợt thuộc tính & biến thể (Pha 7)

- **Thuộc tính trong form Product**: component `x-admin.product-attributes` — thêm thuộc tính từ catalog toàn cục (dropdown), tạo thuộc tính tùy chỉnh per-product qua AJAX (`POST admin/products/{uuid}/attributes`), chọn giá trị dạng chips (color swatch khi `type=color`), toggle "Dùng cho biến thể" (`is_variation`).
- **Biến thể tự sinh**: component `x-admin.product-variants` — cartesian product các attribute `is_variation`, render bảng `sku/price/compare_price/stock_quantity/is_active`; preserve input đã nhập theo combo key khi thêm/bỏ giá trị; server render sẵn variant hiện có khi edit.
- **Service**: `ProductService::syncAttributes()` (sync pivot `product_attribute` + `product_attribute_value`), `ProductService::generateVariants()` (preserve-by-combo, guard > 100 combos → `ValidationException`), `ProductService::createCustomAttribute()` (thuộc tính custom `product_id`-scoped).
- **Custom attribute scoping**: `product_attributes.product_id` nullable — `NULL` = catalog toàn cục, `= X` = custom của product X. `ProductAttributeRepository::getActivePaginated` + `getActiveWithValues` lọc `whereNull('product_id')`; `getAvailableForProduct(id)` lấy catalog + custom của product.
- **Xóa shell `admin.product-variants.*`** (route/controller/service/DTO/repo/views/binding) — hòa biến thể vào form Product như quyết định thiết kế.

## Đã làm trong đợt stabilize (Pha 6+)

- **Tab filter sửa**: `ProductRepository::getActivePaginated` lọc theo `tab` (trước đó lọc `status` — không khớp `x-admin.filter-tabs`, click tab không có tác dụng).
- **Slug uniqueness**: `ProductService` tạo/sửa dùng `generateUniqueSlug('product_translations', ...)` như Post/Page/Category (trước đó dùng `Str::slug()` thô, trùng tên → trùng slug).
- **Authorization**: `StoreProductRequest` → `products.create`, `UpdateProductRequest` → `products.update` (qua `AuthorizesWithPermission`); bulk action gate qua `BulkActionRegistry` (`delete` → `products.delete`, đổi trạng thái → `products.update`); custom attribute endpoint → `products.update`.

## Việc cần làm

- [x] **Thư viện ảnh nhiều ảnh**: component `x-admin.image-gallery` (render item có sẵn + clone qua `<template>` khi click "Thêm ảnh"); chọn ảnh đại diện bằng radio `primary_index`; DTO đọc theo convention `images[i][image]_uuid` / `_remove` (sửa luôn bug submit ảnh đơn không được lưu); service đảm bảo đúng 1 ảnh đại diện.
- [x] **Biến thể sản phẩm**: `ProductService::generateVariants` + `syncAttributes` + UI dynamic JS trong form (xem component `x-admin.product-attributes` / `x-admin.product-variants`).
- [ ] Bổ sung `ProductRepository::searchBySku`, `getOutOfStock` (theo spec `07` §3.2) nếu cần cho phần báo cáo / import.
- [ ] Frontend storefront hiển thị/chọn biến thể (chưa có module front).
- [ ] Tích hợp Cart/Order/Inventory với `product_variants` (đang là shell, FK variant chưa dùng).
- [ ] Ảnh riêng cho variant (hiện dùng gallery của product).

## Phụ thuộc

`Category` ✅ → `Brand` ✅ → `ProductAttribute` ✅ → `Product` ✅ (biến thể ✅).

