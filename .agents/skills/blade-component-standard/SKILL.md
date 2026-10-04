---
name: blade-component-standard
description: |
  Chuẩn tạo và đánh giá Blade Component cho Admin Panel của dự án Licuti CMS (Bootstrap 5.3 + SCSS).
  Áp dụng khi tạo mới hoặc review bất kỳ file component nào trong
  resources/views/components/admin/*.blade.php.
---

# Tiêu chuẩn tạo Blade Component — Licuti CMS Admin (Bootstrap 5.3)

> Đọc kỹ và tuân thủ tài liệu này trước khi tạo mới hoặc chỉnh sửa
> bất kỳ Blade Component nào trong `resources/views/components/admin/`.

---

## 1. Cấu trúc bắt buộc của Component

Mỗi component phải có đủ 3 phần theo thứ tự:

```blade
{{-- 1. PROPS DECLARATION --}}
@props([
    'name' => null,
    'id'   => null,
    'size' => 'md',
])

{{-- 2. LOGIC BLOCK (@php) --}}
@php
    $inputId = $id ?? 'comp-' . ($name ?? uniqid()) . '-' . uniqid();
    $sizes = [
        'sm' => 'form-control-sm',
        'md' => '',
        'lg' => 'form-control-lg',
    ];
    $sizeClass = $sizes[$size] ?? '';
@endphp

{{-- 3. TEMPLATE (HTML) --}}
<input id="{{ $inputId }}" {{ $attributes->merge(['class' => 'form-control ' . $sizeClass]) }}>
```

---

## 2. Quy tắc khai báo `@props`

| Yêu cầu | Chi tiết |
|---|---|
| **Luôn có default value** | Mọi prop PHẢI có giá trị mặc định (`=> null`, `=> false`, `=> 'md'`) |
| **Tên prop = camelCase** | `labelPosition`, `defaultLocale`, không dùng `label_position` |
| **Kiểu dữ liệu nhất quán** | Boolean prop dùng `false`/`true`, không dùng `0`/`1` |

---

## 3. Quy tắc Block `@php` & Mapping biến thể

### 3.1 Variant Maps bằng Array (Dùng class Bootstrap 5)
Luôn định nghĩa các biến thể (size, color, variant) dưới dạng associative array ánh xạ sang class Bootstrap:

```php
// Nút bấm
$variants = [
    'primary'   => 'btn-primary',
    'secondary' => 'btn-light',
    'danger'    => 'btn-danger',
    'outline'   => 'btn-outline-secondary',
];

// Badge màu
$colors = [
    'primary'   => 'text-bg-primary',
    'secondary' => 'text-bg-secondary',
    'success'   => 'text-bg-success',
    'danger'    => 'text-bg-danger',
    'warning'   => 'text-bg-warning',
    'info'      => 'text-bg-info',
    'light'     => 'text-bg-light',
    'dark'      => 'text-bg-dark',
];
```

### 3.2 Tự sinh ID
Form element PHẢI tự sinh `id` duy nhất nếu không được truyền:
```php
$inputId = $id ?? 'ctrl-' . ($name ?? 'f') . '-' . uniqid();
```

---

## 4. Quy tắc HTML Template

### 4.1 `$attributes` Forwarding — BẮT BUỘC
Mọi component PHẢI forward `$attributes` vào element gốc để tương thích với HTML attributes (`required`, `placeholder`, `disabled`, `data-*`):
```blade
<input {{ $attributes->merge(['class' => 'form-control ' . $sizeClass]) }}>
```

### 4.2 Accessibility (a11y)
* Form input liên kết `<label for="{{ $inputId }}">` với `<input id="{{ $inputId }}">`.
* Description liên kết qua `aria-describedby="{{ $inputId }}-desc"`.
* Trạng thái disabled dùng `@disabled($isReadonly)` hoặc `disabled`.

### 4.3 Dark Mode — Tự động qua Bootstrap 5
* Hệ thống Dark Mode dựa trên `[data-bs-theme="dark"]` của Bootstrap 5.
* **TUYỆT ĐỐI KHÔNG DÙNG tiền tố `dark:` của Tailwind** (như `dark:bg-slate-900`).
* Dùng các class ngữ nghĩa của Bootstrap (`bg-body`, `text-body`, `text-body-secondary`, `bg-body-tertiary`, `border`) để tự động tương thích cả Light & Dark mode.

### 4.4 Switch / Toggle theo chuẩn Bootstrap 5
* Dùng chuẩn `.form-check .form-switch`:
```blade
<div class="form-check form-switch">
    <input type="checkbox" role="switch" class="form-check-input" id="{{ $inputId }}" name="{{ $name }}" @checked($checked)>
    @if($label)
        <label class="form-check-label small fw-medium" for="{{ $inputId }}">{{ $label }}</label>
    @endif
</div>
```

---

## 5. LỖI PHỔ BIẾN CẦN TRÁNH

| ❌ Lỗi sai | ✅ Cách viết đúng |
|---|---|
| Thêm class `dark:bg-*` hoặc `dark:text-*` | Dùng `bg-body`, `text-body`, `text-body-secondary` |
| Thêm `<div class="card-body">` bên trong `<x-admin.card>` | `<x-admin.card>` đã tự bọc `.card-body`, chỉ cần viết nội dung trực tiếp |
| Dùng `space-y-*` hoặc `space-x-*` | Dùng Flex gap: `d-flex flex-column gap-3` hoặc `gap-4` |
| Dùng `grid-cols-*` | Dùng Grid 12 cột Bootstrap: `row g-4` và `col-*` |
| Hardcode inline style màu sắc | Dùng CSS variable `--bs-*` hoặc class tiện ích Bootstrap |
| Đặt directive trong Blade comment: `{{-- ... (@php) ... --}}` | Tránh đặt `@php`, `@endphp`, `@if`... trong `{{-- --}}` vì Blade Compiler sẽ parse lỗi và nuốt khối code tiếp theo |
| Sử dụng FontAwesome `<i class="fa-*">` | Dùng icon SVG inline theo chuẩn (Heroicons style) |
