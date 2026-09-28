# Module: ProductAttribute

> ✅ **Hoàn thành** | Profile: Full | Tầng 3 — Danh mục sản phẩm
> Route: `admin.product-attributes.*` | Views: `resources/views/admin/product-attributes/`
> Catalog toàn cục (`product_id IS NULL`) + thuộc tính tùy chỉnh per-product (`product_id = X`).

## Hiện trạng (audit 09/2026, cập nhật sau đợt thuộc tính & biến thể)

| Hạng mục | Trạng thái |
|---|---|
| Migration `product_attributes` / `product_attribute_translations` / `product_attribute_values` | ✅ Đầy đủ (uuid, code, type, is_filterable, display_order, **product_id nullable**) |
| Models (`ProductAttribute`, `ProductAttributeTranslation`, `ProductAttributeValue`) | ✅ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ |
| Test | ✅ `ProductAttributeCrudTest` 5 case + `ProductAttributeVariantTest` (scoping catalog) |

## Thiết kế

### Phạm vi thuộc tính (`product_id` nullable)

| `product_id` | Ý nghĩa | Nơi hiển thị |
|---|---|---|
| `NULL` | Catalog toàn cục (dùng cho mọi product) | Index `admin/product-attributes` |
| `= X` | Thuộc tính tùy chỉnh của product X | Không hiện ở catalog; chỉ hiện trong form product X |

- `ProductAttribute::scopeGlobal()` + `isGlobal()`.
- `ProductAttributeRepository::getActivePaginated()` / `getActiveWithValues()` lọc `whereNull('product_id')`.
- `ProductAttributeRepository::getAvailableForProduct($productId)` = catalog toàn cục **HOẶC** custom của product đó.

### Loại hiển thị (`type`)

`select` (mặc định) · `color` (color swatch, có `color_code`) · `button` · `radio`.

### Tạo custom attribute từ form Product

```
POST admin/products/{uuid}/attributes  →  ProductController@storeAttribute
    permission: products.update (AuthorizesWithPermission)
    → ProductService::createCustomAttribute()
```

Trả JSON `{ success, attribute: {id, uuid, name, type, values[]} }` để JS thêm ngay vào danh sách.

### Chọn giá trị & tạo mới tại form Product (Product Form Revamp P1.1 + P1.2)

- **Chọn giá trị**: `<select multiple class="attribute-values-select">` khởi tạo bằng **TomSelect** (import qua Vite, bundle +~40KB gzip) — plugins `remove_button` + `clear_button`, `create: true` / `createOnBlur: true`, search mặc định, `hideSelected`. Options nạp từ `data-catalog` (JSON trong wrapper).
- **Tạo giá trị mới tại chỗ**: tom-select `create` sinh value text mới → native select submit raw text → `ProductDTO::parseAttributes()` phân loại: `^\d+$` = id số vào `value_ids`, còn lại vào `new_values`. `ProductService::syncAttributes()` tạo `ProductAttributeValue` (check trùng `attribute_id` + `value`).
- **Tạo thuộc tính mới tại trang create**: inline form "Thuộc tính tùy chỉnh" chuyển từ AJAX-only sang **submit cùng form chính** — payload `new_attributes[key][name|type|is_variation|values[]]`. `ProductService::createNewAttributes()` tạo attribute scoped `product_id` (chạy sau khi model save để có id), tạo values, sync pivot, trả về matrix có id thật để `generateVariants()` sinh tổ hợp ngay trong cùng transaction.
- Label "Dùng cho biến thể" đổi thành **"Dùng làm trục biến thể (sinh tổ hợp)"**.
- Validation ownership (`BaseProductRequest::validateAttributeOwnership()`) chỉ check id số > 0, bỏ qua text mới.

## Việc cần làm

- [ ] Frontend storefront dùng `is_filterable` làm bộ lọc tìm kiếm sản phẩm.
- [ ] Bản dịch cho custom attribute name ngoài default locale (accessor `translate()` đã fallback).

## Giao diện Admin

| Field | Component | name |
|---|---|---|
| Tên thuộc tính | `x-admin.input` | `translations[vi][name]` |
| Mã | `x-admin.input` | `code` |
| Loại hiển thị | `select` | `type` |
| Lọc tìm kiếm | `form-switch` | `is_filterable` |
| Giá trị | dynamic JS (thêm/xóa dòng) | `values[]` |
| Mã màu (khi type=color) | `input type=color` + text | `values[i][color_code]` |
| Thứ tự | `x-admin.input type=number` | `display_order` |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
