# Kế hoạch: Đóng lỗ hổng authorization trên bulk actions

## Ngữ cảnh & vấn đề

Lớp permission enforcement (Pha 1–3) đã xong, nhưng **bỏ sót đúng đường nguy hiểm nhất**:

- `app/Http/Requests/Admin/BulkActionRequest.php:11` — `authorize()` vẫn `return true`.
  Nó chỉ validate `bulk_module` + `action` có đăng ký không, **không check quyền**.
- `CategoryController::bulk` (`:81`) và `ProductController::bulk` (`:104`) còn tệ hơn:
  nhận `Request` thường, logic switch inline, **không qua FormRequest nào**.

Hệ quả cụ thể (đã verify): role `editor` có `pages.create`/`pages.update` nhưng
**không** có `pages.delete`. Hiện editor vẫn xóa hàng loạt trang được — quyền
`pages.delete` đã seed nhưng là dead code.

Tất cả permission cần map **đã có sẵn** trong `RolePermissionSeeder` (pages.delete,
posts.delete, post-categories.delete, categories.delete, products.delete + các
`.update` tương ứng). **Không cần seed thêm permission mới.**

## Mức độ ưu tiên

Cao — đây là phần dở của đúng công việc authorization ta vừa làm, phạm vi nhỏ,
và biến các quyền `.delete` đang dead code thành enforcement thật. L6 (ẩn module
shell ở sidebar) là cosmetic, để sau.

## Thiết kế

### Quyết định cốt lõi: single source of truth ở `BulkActionRegistry`

Thêm tham số `permission` vào `register()`:

```php
$registry->register('pages', 'delete', __('Xóa đã chọn'), $handler, 'pages.delete');
```

Registry trở thành nơi duy nhất gắn module+action → permission. Từ đó:
- `BulkActionRequest::authorize()` resolve permission từ registry rồi `can()`.
- `getActionOptions($module)` tự lọc theo `can()` → UI dropdown chỉ hiện action
  user được phép (giải quyết đồng thời enforcement và hiển thị).

**Phương án loại bỏ:** thêm `Gate::define` riêng hoặc map bằng switch trong
`BulkActionRequest` — trùng lặp với registry, dễ lệch khi đăng ký action mới.

### Convention map action → permission

| Action pattern | Permission |
|---|---|
| `delete` | `{module}.delete` |
| `status_*`, `activate`, `deactivate`, `publish`, `draft`, `archive` | `{module}.update` |

Lý do `status_*` → `.update` chứ không phải `.publish`: đổi trạng thái là một dạng
update dữ liệu; dùng `.update` tái sử dụng quyền đã seed, không phải thêm mới.
`posts.publish`/`products.publish` chỉ dành cho flow duyệt riêng (sau này).

### Tại sao `BulkActionRequest` vẫn `extends FormRequest`

Theo `docs/architecture/09-base-classes.md §3.3`: admin Blade form phải giữ hành
vi redirect khi validation fail, **không** được trả JSON như `BaseRequest`. Bulk
action là POST từ Blade → giữ `FormRequest`. Khi `authorize()` trả false, Laravel
ném `AuthorizationException` → render trang 403 (chấp nhận được cho admin).

## Tasks (thực thi theo thứ tự)

1. **`app/Core/BulkAction/BulkActionRegistry.php`**
   - `register(string $module, string $action, string $label, Closure $handler, ?string $permission = null)`.
   - Lưu `permission` vào `actions[$module][$action]['permission']`.
   - Thêm `getPermission(string $module, string $action): ?string`.
   - `getActionOptions(string $module)`: lọc bỏ action mà `auth()->user()?->can($permission) === false`.

2. **`app/Http/Requests/Admin/BulkActionRequest.php`**
   - `authorize()`: lấy `$module = $this->input('bulk_module')`, `$action = $this->input('action')`,
     resolve permission qua registry; nếu permission null → cho qua (backward
     compatible với action chưa gắn); nếu có → `return $this->user()?->can($permission) ?? false`.

3. **`app/Providers/BulkActionServiceProvider.php`**
   - Gắn permission cho các action đang đăng ký:
     - `post_categories`: delete → `post-categories.delete`; activate/deactivate → `post-categories.update`.
     - `posts`: delete → `posts.delete`; `status_*` → `posts.update`.
     - `pages`: delete → `pages.delete`; `status_*` → `pages.update`.

4. **Đưa Category + Product bulk vào đúng pattern**
   - `CategoryController::bulk`: đổi signature sang `bulk(BulkActionRequest $request, BulkActionRegistry $registry)`,
     gọi `$registry->dispatch('categories', ...)`. Đăng ký action `categories`
     trong `BulkActionServiceProvider` (delete → `categories.delete`, status_1/status_0
     → `categories.update`), giữ nguyên logic service hiện tại.
   - `ProductController::bulk`: tương tự, đổi sang `BulkActionRequest` + registry.
     Đăng ký module `products`: delete → `products.delete`; publish/draft/archive
     → `products.update`. Di chuyển switch logic vào handler trong ServiceProvider.
   - Cập nhật `ProductController::index()` / view: dùng `$bulkRegistry->getActionOptions('products')`
     thay vì `$this->bulkActions()` hardcoded (để UI cũng lọc theo quyền).
   - Kiểm tra `CategoryController::index()` có truyền bulkActions ra view không;
     nếu view hardcode dropdown thì chuyển sang dùng registry.

5. **Test `tests/Feature/Admin/BulkActionAuthorizationTest.php`**
   - Editor không có `pages.delete` POST bulk delete trang → expect 403.
   - Editor có `pages.update` POST bulk đổi status → expect redirect thành công.
   - Editor không có `products.delete` → 403; admin (is_admin) → pass (Gate::before).
   - Test UI: `getActionOptions('pages')` với editor không chứa key `delete`.

6. **Docs**
   - `docs/architecture/19-authorization-gap-analysis.md`: thêm mục "Pha 6 — bulk
     action authorization" (đã làm).
   - `docs/04-permissions.md`: bỏ ghi chú nợ `pages.bulk` (L5) ở phần PAGES, vì
     bulk giờ gate bằng `pages.delete`/`pages.update`.
   - `docs/architecture/09-base-classes.md §3.4`: thêm dòng note bulk action dùng
     permission của action qua registry.

## Rủi ro & kiểm tra

- **Rủi ro**: `getActionOptions` lọc theo quyền làm view render khác nhau giữa
  user → cache view không được share nội dung nhạy cảm. Kiểm tra: view không
  cache fragment bulk dropdown.
- **Rủi ro**: action đăng ký nhưng quên gắn permission → tạm thời **cho qua**
  (quyết định ở task 2). Đây là trade-off an toàn: mặc định mở cho action
  nội bộ đã đăng ký (đã qua route AdminMiddleware) còn hơn là break tất cả.
  Mitigate: test phủ tất cả module đang đăng ký.
- **Backward compat**: `register()` thêm tham số tùy chọn → code hiện tại không break.
- **Validation**: `composer test` phải pass (hiện 91 test). Chạy thêm `php -l` trên
  mọi file đã sửa. Verify bằng tinker: editor `can('pages.delete')` = false và
  response 403 khi POST.

## Phạm vi loại trừ

- Không lọc sidebar theo permission (đã ghi TODO, user xác nhận bỏ qua).
- Không ẩn module shell khỏi sidebar (L6).
- Không thêm permission `.bulk` riêng (thuyết minh ở phần Thiết kế).
