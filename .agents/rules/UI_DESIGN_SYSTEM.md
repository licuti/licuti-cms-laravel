# Quy tắc UI Admin — Licuti CMS

> **BẮT BUỘC**: Trước khi viết hoặc sửa BẤT KỲ file Blade, SCSS hoặc JS nào
> trong khu vực Admin (`resources/views/admin/`, `resources/views/components/admin/`,
> `resources/css/`), Agent PHẢI đọc skill `admin-ui` để nắm đầy đủ component
> catalog, page template chuẩn và coding standard.

## Tech Stack

| Thành phần | Chuẩn |
|---|---|
| Framework UI | **Bootstrap 5.3** + SCSS (**KHÔNG dùng Tailwind**) |
| Template | Laravel Blade + Component `<x-admin.*>` |
| Font | Outfit (Google Fonts: 300–700) |
| Icon | Heroicons SVG inline (**KHÔNG** FontAwesome) |
| JS | jQuery + SweetAlert2 |
| Dark Mode | `data-bs-theme="dark"` (Bootstrap native, **KHÔNG** `dark:*`) |
| Bo góc | `0.5rem` (8px) — tự động qua SCSS variable |

## Bảng màu

Primary `#2563eb` · Secondary `#64748b` · Success `#10b981` · Warning `#f59e0b` · Danger `#ef4444` · Info `#06b6d4`

## Quy tắc CẤM tuyệt đối

- **CẤM** dùng bất kỳ class Tailwind: `space-y-*`, `dark:*`, `grid-cols-*`, `w-64`, `gap-y-*`
- **CẤM** lồng `<div class="card-body">` trong `<x-admin.card>` (gây double padding)
- **CẤM** viết HTML thô khi đã có component `<x-admin.*>` tương ứng
- **CẤM** dùng FontAwesome `<i class="fa-*">` — dùng SVG Heroicons
- **CẤM** hardcode hex color trong Blade — dùng class Bootstrap hoặc `var(--bs-*)`
- **CẤM** dùng `<x-admin.scripts.seo-preview>` (deprecated) — dùng `<x-admin.seo-meta>`
- **CẤM** dùng `<x-admin.language-switcher-widget>` cho form mới — dùng `<x-admin.lang-tabs>`
