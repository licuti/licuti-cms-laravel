# Plan: Chuẩn hóa HTML/bố cục trang product-attribute-values

Phạm vi: `resources/views/admin/product-attribute-values/index.blade.php` (+ nhỏ ở
`form.blade.php`). Không động đến logic/controller/route — chỉ view.

## Đánh giá hiện trạng (so với `docs/architecture/13-ui-conventions.md`,
skill `blade-component-standard`, và module tham chiếu `products/index`,
`product-attributes/index`)

### Đã đúng tiêu chuẩn
- Bulk action bar: `#bulk-action-select` + `#btn-apply-bulk` (variant primary) +
  hidden form `#form-bulk-action` có `bulk_module` — giống y `products/index`.
- Bảng dùng `<x-admin.table>` + `<x-admin.table-th>` + `<x-admin.table-cell-primary>`
  nhận `$actions` array → thỏa rule #4 (Sửa/Xóa qua row-actions).
- Cột checkbox `#check-all` / `.row-checkbox` giống `products/index`.
- Empty state icon `bi-inbox fs-2 d-block mb-2 text-muted` — pattern dùng ở
  `products`, `product-attributes`, `banners`, `menus` → nhất quán (không phải lỗi).
- Form grid `col-md-8 col-lg-9` / `col-md-4 col-lg-3` = đúng chuẩn §13.
- Input màu nằm trong `<x-admin.form-group>` (rule #1).
- Colspan empty-state đã đúng (4 khi color / 3 khi không).

### Lệch chuẩn cần sửa
1. **Nút reorder viết tay, không qua `<x-admin.button>`** (index:125,133).
   `<button class="btn btn-link p-0 text-body-secondary">` — component catalog
   quy định nút bấm luôn dùng `<x-admin.button variant="link">`.
2. **Nhãn tooltip "Lên lên"/"Xuống xuống"** lệch với aria-label "Di chuyển lên/
   xuống", và khẩu ngữ không phù hợp admin.
3. **`@php` khối nằm giữa template** (index:61-65) — nên hoist lên đầu file.
4. **Empty state không phân biệt "không có DL" vs "lọc không trúng"** —
   `categories/index` dùng câu "Không tìm thấy ... phù hợp".
5. **Layout jitter**: row đầu/cuột ẩn nút → số thứ tự bị nhảy vị trí.

### Ngoài phạm vi (pre-existing, để nguyên)
- Inline `onchange`/`oninput` ở color picker (`form.blade.php`) — có từ trước,
  hoạt động ổn, không nên đụng trong đợt này.
- `text-muted` (BS 5.3 deprecated) — dùng toàn repo, sửa là việc dọn dẹp riêng.

## Thay đổi

### 1. index.blade.php — hoist `@php` lên đầu file
Di chuyển khối tính `$isColor`/`$emptyColspan` lên ngay sau `@section('content')`
để logic gom một chỗ, template thuần render.

### 2. Nút reorder dùng `<x-admin.button>`
```blade
<form action="{{ route('admin.product-attributes.values.move_up', [$attribute->uuid, $valueModel->uuid]) }}"
      method="POST" class="d-inline">
    @csrf
    <x-admin.button type="submit" variant="link" size="sm"
                    class="p-0 lh-1 text-body-secondary align-baseline"
                    title="{{ __('Di chuyển lên') }}" aria-label="{{ __('Di chuyển lên') }}">
        <i class="bi bi-arrow-up"></i>
    </x-admin.button>
</form>
```
- `x-admin.button` tự ra `btn btn-link btn-sm d-inline-flex ...` → chỉ cần thêm
  `p-0 lh-1` cho icon-only.
- Tooltip + aria-label统一 là "Di chuyển lên"/"Di chuyển xuống".

### 3. Chống jitter: giữ chỗ cố định cho 2 nút
Bọc khối nút trong `<span class="d-inline-flex" style="min-width: 3rem;">` —
khi ẩn 1 nút thì số thứ tự không dịch chuyển. Dùng inline style vì đây là giá
trị cụ thể của cell này (tương tự `style="width:40px;"` đã dùng ở cột checkbox).

### 4. Empty-state tách 2 trường hợp
```blade
@empty
    <tr>
        <td colspan="{{ $emptyColspan }}" class="text-center py-5 text-body-secondary">
            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
            {{ request()->filled('search')
                ? __('Không tìm thấy giá trị nào phù hợp với từ khóa.')
                : __('Chưa có giá trị nào.') }}
        </td>
    </tr>
@endforelse
```

## Validation
- `php artisan test --filter="ProductAttributeValueTest|ProductAttributeCrudTest"`
  (test `per_page` GET index → assert view render OK).
- `./vendor/bin/pint --test --dirty`.
- Kiểm tra thủ công: `/admin/product-attributes/{uuid}/values` — hover row đầu
  (chỉ có mũi tên xuống), row cuối (chỉ mũi tên lên), row giữa (cả hai); bấm
  reorder xong vẫn thấy nút đúng vị trí không nhảy.
- Không cần `npm run build` — chỉ thay đổi markup/Blade, không thêm JS.
