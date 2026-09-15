# 13 — Quy chuẩn giao diện (Bootstrap 5.3) & Danh mục Blade Component

> Dự án sử dụng **Bootstrap 5.3 + SCSS**. Bố cục Form chuẩn là lưới 12 cột: `col-lg-9 col-md-8` (cột chính) và `col-lg-3 col-md-4` (cột phụ).
>
> **Tuyệt đối KHÔNG dùng Tailwind CSS.**

---

## Danh mục Component chuẩn trong `resources/views/components/admin/`

| Tên Component | Cú pháp gọi | Mục đích sử dụng | Props chính |
|---|---|---|---|
| **Page Header** | `<x-admin.page-header>` | Tiêu đề đầu trang + nút hành động | `title`, `subtitle`, `breadcrumbs`, slot `actions` |
| **Card** | `<x-admin.card>` | Khung bọc khối giao diện | `title`, `class` |
| **Form Group** | `<x-admin.form-group>` | Bọc label + input + mô tả + lỗi | `label`, `name`, `description`, `required` |
| **Input** | `<x-admin.input>` | Ô nhập liệu text, email, password... | `type`, `name`, `value`, `placeholder`, `size` |
| **Textarea** | `<x-admin.textarea>` | Ô nhập văn bản nhiều dòng | `name`, `rows`, `class` |
| **Select** | `<x-admin.select>` | Dropdown chọn lựa chọn | `name`, `size`, slot `<option>` |
| **Toggle Switch** | `<x-admin.toggle>` | Công tắc bật/tắt on-off dạng switch | `name`, `checked`, `label` |
| **Button** | `<x-admin.button>` | Nút bấm hoặc liên kết dạng nút | `variant` (primary, secondary, outline...), `href`, `type`, `size` |
| **Badge** | `<x-admin.badge>` | Nhãn hiển thị trạng thái | `label`, `color` (green, amber, gray, red...) hoặc `variant` |
| **Table Container** | `<x-admin.table>` | Khung bảng dữ liệu + phân trang | `paginator`, slot `head`, slot mặc định `<tr>` |
| **Table Header** | `<x-admin.table-th>` | Thẻ `<th>` tiêu đề cột trong bảng | `align`, `padding`, `width` |
| **Table Cell Primary** | `<x-admin.table-cell-primary>` | Cột hiển thị chính (Ảnh + Tiêu đề + Actions hover) | `title`, `subtitle`, `image`, `actions` |
| **Row Actions** | `<x-admin.row-actions>` | Bộ nút Sửa / Xóa (tích hợp confirm) | `actions` (mảng cấu hình action) |
| **Filter Tabs** | `<x-admin.filter-tabs>` | Hàng tab đếm số lượng theo status | `items`, `current`, `route` |
| **Language Tabs** | `<x-admin.lang-tabs>` | Tabs chuyển đổi ngôn ngữ trong Form | `locales`, `defaultLocale`, slot mặc định |
| **SEO Meta** | `<x-admin.seo-meta>` | Cụm form SEO on-page + Schema + Preview | `model`, `locales`, `defaultLocale` |
| **Media Picker** | `<x-admin.media-picker>` | Chọn ảnh từ Media Library tập trung | `name`, `value`, `placeholder` |
| **Tree Checkbox** | `<x-admin.tree-checkbox>` | Cây chọn danh mục phân cấp | `name`, `options`, `selected`, `type` |
| **Modal** | `<x-admin.modal>` | Hộp thoại Bootstrap Modal | `id`, `title`, slot `footer` |

---

## Quy tắc sử dụng Component

1. **Tất cả input/select/textarea phải bọc trong `<x-admin.form-group>`** — để hiển thị label, mô tả, và lỗi validation nhất quán.
2. **Form dùng chung 1 file `form.blade.php` cho cả create và edit** — dùng `@if(isset($article))` để phân biệt mode.
3. **Nút lưu mặc định có 2 chế độ**: `value="save"` (về danh sách) và `value="save_and_edit"` (ở lại form).
4. **Sửa/Xóa trong bảng dùng `<x-admin.row-actions>`** hoặc cấu trúc `$actions` array (xem [10-core-mechanisms.md](10-core-mechanisms.md) mục 4.4).
5. **Không viết script SweetAlert2 thủ công** — mọi confirm xóa đã có sẵn trong `resources/js/admin/table-utils.js`.
6. **Không viết `@push('scripts')` cho logic riêng từng trang index** — nếu cần, tạo file JS riêng và require trong `vite.config.js`.