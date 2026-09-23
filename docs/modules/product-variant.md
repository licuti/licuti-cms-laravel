# Module: ProductVariant

> ✅ **Hoàn thành (hòa vào module Product)** | Profile: Full | Tầng 4 — Sản phẩm
> Không còn route/view riêng — biến thể quản lý trong form Product.

## Quyết định thiết kế (đã chốt)

Theo spec `07` §3.2, biến thể được quản lý **bên trong form Product** (kiểu WP/WooCommerce):
chọn thuộc tính → tick giá trị → đánh dấu "Dùng cho biến thể" → bảng biến thể tự sinh.

=> **Đã xóa shell `admin.product-variants.*`** (route/controller/service/DTO/repo/views/RepositoryServiceProvider binding)
và chuyển logic sang `ProductService` + 2 component trong form Product.

> Lợi ích phụ: xóa shell giải quyết luôn bug tiềm ẩn — `ProductVariantService::getList()`
> gọi `$this->repository->getActivePaginated()` nhưng `BaseRepository` không có method này
> → route `admin.product-variants.index` fatal nếu truy cập.

## Hiện trạng (audit 09/2026, cập nhật sau đợt thuộc tính & biến thể)

| Hạng mục | Trạng thái |
|---|---|
| Migration `product_variants` (uuid, product_id, sku, price, compare_price, stock_quantity, is_active, display_order) | ✅ |
| Pivot `product_attribute` (is_variation, display_order, unique [product_id, attribute_id]) | ✅ |
| Pivot `product_attribute_value` (unique [product_id, attribute_value_id]) | ✅ |
| Pivot `product_variant_attribute_values` (unique [variant_id, attribute_value_id]) | ✅ |
| Model `ProductVariant` ($fillable, casts, relations, accessor `name` = "Đỏ - S") | ✅ |
| Service `ProductService::syncAttributes` / `generateVariants` / `createCustomAttribute` | ✅ |
| UI `x-admin.product-attributes` + `x-admin.product-variants` | ✅ |
| Test `ProductAttributeVariantTest` 10 case | ✅ |

## Logic chính

### `syncAttributes(Product $product, array $attributeMatrix)`

`$attributeMatrix` = `[['attribute_id', 'is_variation', 'value_ids']]`.
Sync pivot `product_attribute` (withPivot is_variation/display_order) + `product_attribute_value`.
`cascadeOnDelete` ở pivot `product_variant_attribute_values` tự xóa link variant↔value không còn hợp lệ.

### `generateVariants(Product $product, array $matrix, array $variantData)`

- Cartesian product các attribute `is_variation` (chỉ value_ids đã chọn).
- **Combo key** = sorted `attribute_value_id` join bằng `-` (dùng làm identity cả ở JS lẫn service).
- **Preserve-by-combo**: variant cũ khớp key giữ nguyên sku/price/stock; tổ hợp mới tạo variant mới (mặc định theo product); tổ hợp bị bỏ xóa; variant mồ côi (key rỗng do cascade) bị dọn.
- **Guard nổ tổ hợp**: > 100 combos → `ValidationException` (cả JS lẫn service đều guard).

### Endpoint custom attribute

```
POST admin/products/{uuid}/attributes → admin.products.attributes.store
```

## Việc cần làm

- [ ] Frontend storefront hiển thị/chọn biến thể (chưa có module front).
- [ ] Tích hợp Cart/Order/Inventory với `product_variants` (đang là shell, FK variant chưa dùng).
- [ ] Ảnh riêng cho variant (hiện dùng gallery của product).
- [ ] Variant price fallback: price `NULL` → frontend lấy theo product price (chỉ admin lưu, phạm vi chưa cần).

Tham khảo: [`07` §3.2](../07-development-process.md), [`03-database-details`](../03-database-details.md), [product.md](product.md).
