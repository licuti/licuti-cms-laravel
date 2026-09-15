# 🎨 LICUTI CMS — BỘ THIẾT KẾ HỆ THỐNG UI/UX (BOOTSTRAP 5.3 + SCSS)

> **Tài liệu chuẩn UI cho AI Agent:** Mọi file Blade, CSS/SCSS và JS đều PHẢI tuân thủ các quy tắc dưới đây để đảm bảo tính đồng nhất trên toàn bộ Admin Panel.

---

## 1. TỔNG QUAN STACK & QUY CHUẨN KỸ THUẬT

| Tiêu chí | Chuẩn áp dụng |
|---|---|
| **Stack giao diện** | **Laravel Blade + Bootstrap 5.3 + SCSS + jQuery + SweetAlert2** |
| **Font chữ** | `Outfit` (Google Fonts: 300, 400, 500, 600, 700) |
| **Icon** | Heroicons (Outline style, stroke-width="2", kích thước `1rem` / `1.25rem` hoặc class `.sidebar-icon`) |
| **Bo góc chuẩn** | **~8px (`0.5rem`)** — Đã cấu hình tập trung qua `$border-radius: 0.5rem;` trong `resources/css/app.scss` (tự động áp dụng cho Card, Button, Input, Modal). |
| **Dark Mode** | **Attribute-based**: `data-bs-theme="dark"` hoặc `data-bs-theme="light"` trên thẻ `<html>`. **TUYỆT ĐỐI KHÔNG dùng class tiền tố `dark:` của Tailwind.** |
| **Animation** | Micro-interaction nhẹ: `.active-scale` (active scale 0.97), transition CSS chuẩn. |

---

## 2. BẢN MÀU & SEMANTIC UTILITIES (Bootstrap 5.3)

Hệ màu chính: **Modern Blue** (Primary: `#2563eb`, Secondary: `#64748b`, Success: `#10b981`, Warning: `#f59e0b`, Danger: `#ef4444`, Info: `#06b6d4`).

### 2.1 Màu nền (Backgrounds)
- **Nền trang chính:** `bg-body` hoặc biến `--bs-body-bg`
- **Nền Card / Header / Sidebar:** `bg-body` hoặc tự động theo component Bootstrap
- **Nền Section / Khối phụ:** `bg-body-tertiary` hoặc `bg-body-secondary`
- **Nền Subtle (Badge/Alert):** `bg-primary-subtle`, `bg-success-subtle`, `bg-warning-subtle`, `bg-danger-subtle`

### 2.2 Màu chữ (Text Colors)
- **Tiêu đề & Chữ chính:** `text-body` (tự động trắng ở Dark mode, đen ở Light mode)
- **Chữ phụ / Mô tả / Meta:** `text-body-secondary`
- **Màu chữ trạng thái:** `text-primary`, `text-success`, `text-warning`, `text-danger`, `text-body-tertiary`

### 2.3 Viền (Borders)
- Viền mặc định: `border` (sử dụng biến `--bs-border-color`)
- Phân cách: `border-top`, `border-bottom`
- Độ mờ viền: `border-opacity-25`, `border-opacity-50`

---

## 3. BỐ CỤC, LƯỚI & SPACING

### 3.1 Không gian & Khoảng cách (Spacing)
- **KHÔNG dùng** `space-y-*` hay `space-x-*` (của Tailwind).
- **Dùng Flexbox Gap:**
  - Nhóm dọc: `d-flex flex-column gap-3` hoặc `gap-4` (hoặc `vstack gap-3`).
  - Nhóm ngang: `d-flex align-items-center gap-2` (hoặc `hstack gap-2`).
- **Margin thông dụng:** `mb-2`, `mb-3`, `mb-4`.

### 3.2 Hệ thống Lưới (Grid 12 cột)
- Khung chia cột: `<div class="row g-4 align-items-start">`
- Form 2 cột (Content chính 8-9 phần, Sidebar 3-4 phần):
  - Cột chính: `<div class="col-md-8 col-lg-9 d-flex flex-column gap-4">`
  - Cột sidebar: `<div class="col-md-4 col-lg-3 d-flex flex-column gap-4">`
- Danh sách thẻ/stat: `<div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-4">`

---

## 4. QUY TẮC CÁC THÀNH PHẦN (Components)

### 4.1 Card (`<x-admin.card>`)
* **Lưu ý quan trọng:** Component `<x-admin.card>` **đã tự động có thẻ `<div class="card-body">` bên trong**.
* **TUYỆT ĐỐI KHÔNG** bọc thêm một thẻ `<div class="card-body">` bên trong slot nội dung, vì sẽ gây lỗi **double padding (32px)** khiến layout bị thụt lề sâu.
* Cú pháp đúng:
```blade
<x-admin.card title="Tiêu đề Card">
    <div class="d-flex flex-column gap-3">
        {{-- Nội dung ở đây --}}
    </div>
</x-admin.card>
```

### 4.2 Nút bấm (`<x-admin.button>`)
Hỗ trợ 4 biến thể: `primary`, `secondary`, `danger`, `outline`. Hỗ trợ size: `xs`, `sm`, `md`, `lg`.
```blade
<x-admin.button variant="primary">Lưu</x-admin.button>
<x-admin.button variant="secondary" href="...">Quay lại</x-admin.button>
<x-admin.button variant="outline">Lưu & Sửa</x-admin.button>
<x-admin.button variant="danger">Xóa</x-admin.button>
```

### 4.3 Badge Trạng thái
Dùng component `<x-admin.badge label="..." color="green|amber|red|blue|default" />` hoặc class Bootstrap:
```blade
<span class="badge text-bg-success bg-opacity-10 text-success border border-success border-opacity-25">Hoạt động</span>
<span class="badge text-bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Chờ duyệt</span>
```

### 4.4 Form Controls & Input
* Input: `form-control` hoặc component `<x-admin.input>`
* Select: `form-select` hoặc component `<x-admin.select>`
* Switch/Toggle: dùng component `<x-admin.toggle>` hoặc chuẩn Bootstrap `.form-check .form-switch`:
```blade
<div class="form-check form-switch">
    <input type="checkbox" role="switch" class="form-check-input" id="is_active" name="is_active">
    <label class="form-check-label small fw-medium" for="is_active">Kích hoạt</label>
</div>
```

### 4.5 Bảng dữ liệu & Hành động dòng (Tables & Row Actions)
* Bảng dùng `<x-admin.table>` với class `.table .table-hover .align-middle mb-0`.
* Hành động theo dòng (Edit / Delete) dùng `<x-admin.row-actions :actions="$actions" />` hoặc class `.row-hover-actions`.

### 4.6 Modal
Dùng component `<x-admin.modal id="..." title="...">` dựa trên Bootstrap 5 Modal API (`bootstrap.Modal.getOrCreateInstance()`).

---

## 5. CẤU TRÚC TRANG ADMIN CHUẨN (Page Template)

```blade
@extends('layouts.admin')
@section('title', 'Quản lý Tài nguyên')

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- 1. Header & Actions --}}
    <x-admin.page-header 
        title="Quản lý Tài nguyên" 
        subtitle="Danh sách và quản trị các tài nguyên trong hệ thống"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], 
            ['label' => 'Tài nguyên']
        ]"
    >
        <x-slot name="actions">
            <x-admin.button href="{{ route('admin.resources.create') }}" variant="primary">
                <span>+ Thêm mới</span>
            </x-admin.button>
        </x-slot>
    </x-admin.page-header>

    {{-- 2. Bộ lọc & Tìm kiếm --}}
    <x-admin.card>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm" style="width: auto;">
                    <option value="">Hành động hàng loạt</option>
                    <option value="delete">Xóa đã chọn</option>
                </select>
                <x-admin.button variant="secondary" size="sm">Áp dụng</x-admin.button>
            </div>
            <div class="ms-auto">
                <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm...">
            </div>
        </div>
    </x-admin.card>

    {{-- 3. Bảng dữ liệu --}}
    <x-admin.table :paginator="$items">
        <x-slot:head>
            <th style="width:40px;"><input type="checkbox" class="form-check-input"></th>
            <th>Tên tài nguyên</th>
            <th>Trạng thái</th>
            <th class="text-end">Ngày tạo</th>
        </x-slot:head>

        @forelse($items as $item)
            <tr>
                <td><input type="checkbox" class="form-check-input" value="{{ $item->id }}"></td>
                <td>
                    <div class="fw-semibold text-body">{{ $item->name }}</div>
                    <x-admin.row-actions :actions="[
                        ['label' => 'Sửa', 'route' => route('admin.resources.edit', $item->uuid), 'color' => 'blue'],
                        ['label' => 'Xóa', 'route' => route('admin.resources.destroy', $item->uuid), 'method' => 'DELETE', 'color' => 'red']
                    ]" />
                </td>
                <td><x-admin.badge :label="$item->status" color="green" /></td>
                <td class="text-end text-body-secondary small">{{ $item->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center py-4 text-body-secondary">Không có dữ liệu.</td>
            </tr>
        @endforelse
    </x-admin.table>

</div>
@endsection
```

---

## 6. JAVASCRIPT & SWEETALERT2

* Luôn dùng **SweetAlert2** cho thông báo và xác nhận xóa:
```javascript
// Thông báo
AdminUI.notify('success', 'Thao tác thành công!');

// Hoặc trực tiếp với Swal:
Swal.fire({
    icon: 'success',
    title: 'Thành công!',
    timer: 1500,
    showConfirmButton: false,
    background: '#0f172a',
    color: '#f8fafc'
});

// Xác nhận xóa
AdminUI.confirm({
    title: 'Xóa mục này?',
    text: 'Thao tác này không thể hoàn tác!',
    icon: 'warning',
    confirmText: 'Xóa ngay',
    cancelText: 'Hủy'
}, function() {
    // Submit form delete hoặc AJAX
});
```

---

## 7. CHECKLIST KIỂM TRA UI TRƯỚC KHI HOÀN THÀNH TASK

- [ ] Không dùng bất kỳ class Tailwind nào (`dark:`, `space-y-*`, `w-64`, `grid-cols-*`,...).
- [ ] Dùng đúng class Bootstrap 5 (`d-flex`, `gap-*`, `row`, `col-*`, `form-control`, `text-body-secondary`,...).
- [ ] Thẻ `<x-admin.card>` không bị lồng thêm `.card-body` dư thừa gây double padding.
- [ ] Dark Mode kiểm tra tương thích tự nhiên qua `data-bs-theme` (không can thiệp class thủ công).
- [ ] Chạy `npm run build` thành công không có lỗi SCSS/Vite.
