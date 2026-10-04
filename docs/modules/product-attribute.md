# Module: ProductAttribute

> ✅ **Hoàn thành** | Profile: Full | Tầng 3 — Danh mục sản phẩm
> Route: `admin.product-attributes.*` (Bảo vệ bởi `can:product-attributes.*`) | Views: `resources/views/admin/product-attributes/`
> Catalog toàn cục (`product_id IS NULL`) + thuộc tính tùy chỉnh per-product (`product_id = X`).

## Hiện trạng (audit 10/2026, cập nhật kiến trúc UI và tách module Giá trị)

| Hạng mục | Trạng thái |
|---|---|
| Migration `product_attributes` / `product_attribute_translations` / `product_attribute_values` | ✅ Đầy đủ (uuid, code, type, is_filterable, display_order, **product_id nullable**) |
| Models (`ProductAttribute`, `ProductAttributeTranslation`, `ProductAttributeValue`) | ✅ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ Tách riêng ProductAttributeValue thành sub-module RESTful. Tuân thủ Base Class, Enum `AttributeType`, xử lý lỗi và N+1. |
| Test | ✅ `ProductAttributeCrudTest` 5 case + `ProductAttributeVariantTest` (scoping catalog) |

## Thiết kế

### Phạm vi thuộc tính (`product_id` nullable)

| `product_id` | Ý nghĩa | Nơi hiển thị |
|---|---|---|
| `NULL` | Catalog toàn cục (dùng cho mọi product) | Index `admin/product-attributes` |
| `= X` | Thuộc tính tùy chỉnh của product X | Không hiện ở catalog; chỉ hiện trong form product X |

- `ProductAttribute::scopeGlobal()` + `isGlobal()`.
- `ProductAttributeRepository::getActivePaginated()` / `getActiveWithValues()` lọc `whereNull('product_id')` và tối ưu truy vấn nạp kèm `values.translations`.
- `ProductAttributeRepository::getAvailableForProduct($productId)` = catalog toàn cục **HOẶC** custom của product đó.

### Loại hiển thị (`type`)

Được quản lý thông qua Enum `App\Enums\AttributeType`:
- `select` (mặc định)
- `color` (color swatch, có `color_code`)
- `button`
- `radio`

### Module Giá trị Thuộc tính (Sub-module RESTful)
- Tách `ProductAttributeValue` khỏi quá trình lưu/sửa của `ProductAttribute`.
- Module con có DTO, Service, Repository, FormRequest và Route độc lập: `admin/product-attributes/{attribute_uuid}/values`.
- Kế thừa chuẩn dự án: Các FormRequest Admin đều kế thừa `FormRequest` và dùng trait `AuthorizesWithPermission` để redirect lỗi chính xác thay vì ném ra JSON. Permission sử dụng chuẩn kebab-case `product-attributes.*`. Controller kế thừa `BaseController`.
- **Cache Invalidation**: Quá trình Thêm/Sửa/Xóa giá trị (`ProductAttributeValueService`) luôn thực hiện gọi ngược về `$this->attributeRepository->clearCache()` để đảm bảo Catalog Cache được cập nhật kịp thời.
- Hỗ trợ đa ngôn ngữ trực tiếp cho giá trị (thay vì lồng phức tạp trong form của thuộc tính).
- Trang Edit của thuộc tính hiển thị bảng Tóm tắt (Preview) các giá trị và nút hành động nhanh.

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

## Giao diện Admin (Cập nhật 10/2026)

| Field | Component | name | Vị trí |
|---|---|---|---|
| Tên thuộc tính | `x-admin.input` | `translations[vi][name]` | Cột chính |
| Mã | `x-admin.input` | `code` | Cột chính |
| Loại hiển thị | `select` | `type` | Cột chính |
| Giá trị | preview table | N/A | Cột chính (Khi Edit) |
| Lọc tìm kiếm | `form-switch` | `is_filterable` | Sidebar |
| Thứ tự | `x-admin.input type=number` | `display_order` | Sidebar |

- **Bulk Actions**: Đã được thiết lập trong `BulkActionServiceProvider` hỗ trợ `Xóa đã chọn`, `Bật bộ lọc tìm kiếm`, `Tắt bộ lọc tìm kiếm`. Các thao tác này đều kích hoạt `clearCache()` để đồng bộ Cache Invalidation thay vì bypass Observer. Trang danh sách đã loại bỏ các thẻ card bọc ngoài dư thừa, dùng thẻ `table-cell-primary` đồng nhất theo UI Guidelines.
- UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
