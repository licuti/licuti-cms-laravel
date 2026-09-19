# Module: Menu

> 🚧 **Shell** | Profile: Full | Tầng 2 — CMS
> Route: `admin.menus.*` | Views: `resources/views/admin/menus/`
> Spec: [`07` §3.1](../07-development-process.md) (MODULE: Menus) — xây SAU khi Pages & PostCategories xong (đã xong)

## Hiện trạng (audit 09/2026)

| Hạng mục | Đường dẫn | Trạng thái |
|---|---|---|
| Migration `menus` | dump SQL | ❌ id+timestamps — thiếu uuid/name/location |
| Migration `menu_items` | dump SQL | ❌ id+timestamps — thiếu menu_id/parent_id/title/url/target/icon/display_order |
| Model | `app/Models/Menu.php`, `MenuItem.php` | ❌ |
| FormRequest | `.../Admin/Menu/{Store,Update}MenuRequest.php` | ✅ |
| DTO | `app/DTOs/Menu/MenuDTO.php` | ✅ |
| Repository | `Eloquent/MenuRepository` | ⚠️ |
| Service | `MenuService` (42 LOC) | ⚠️ shell — chưa có `updateItems` (lưu cây drag & drop) |
| Controller | `MenuController` | ✅ — thiếu `saveItems()` |
| Views | `admin/menus/{index,form}.blade.php` | ✅ — thiếu UI drag-drop (Nestable.js) |

## Việc cần làm

### 1. Migration (2 bảng)
- `menus`: id, uuid, name, location (header/footer/sidebar), timestamps.
- `menu_items`: id, menu_id FK cascade, parent_id nullable, title, url, target (_self/_blank), icon, display_order.

### 2. Models
- `Menu`: `HasUuid`, `items(): HasMany`, `translations` nếu cần tên menu đa ngữ.
- `MenuItem`: `$fillable`, `parent(): BelongsTo`, `children(): HasMany` (đệ quy cho cây).

### 3. Repository / Service / Controller
- `MenuRepository`: thêm `getWithItems(string $uuid)`, `getByLocation(string $location)` (frontend).
- `MenuService`: `updateItems(string $menuUuid, array $treeData)` — lưu toàn bộ cây (đệ quy parent_id + display_order) trong transaction.
- `MenuController`: thêm `saveItems(Request $request, string $uuid)` — POST nhận JSON cây items.

### 4. Giao diện Admin
- `index.blade.php` — danh sách menu + location badge.
- `form.blade.php` (đặc biệt: chỉ edit, không create riêng): cây items kéo thả (Nestable.js), submit JSON `items_json`; form thêm item: title, type (custom/page/category — chọn từ select có link nội dung), target.
- UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
