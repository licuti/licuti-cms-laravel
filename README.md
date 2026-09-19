# Licuti CMS

Hệ thống Quản trị E-Commerce (Admin Panel + API) xây trên **Laravel 12**, kiến trúc phân lớp
**Service → Repository → DTO**, giao diện **Bootstrap 5.3 + SCSS** (không dùng Tailwind).

> **Trạng thái:** Tầng Core (users/roles/permissions, media), Tầng 1 (languages, settings) và
> nhóm CMS (posts, pages, post-categories, banners, menus) đã hoàn thiện. Nhóm Catalog/
> Sales/ Payments đang triển khai. Giao diện **Frontend chưa xây dựng** (mọi route `/` hiện
> chuyển về `/admin/login`).

---

## Tech Stack

| Thành phần | Phiên bản |
|---|---|
| PHP | 8.2+ |
| Laravel | 12 |
| Database | MySQL 8.0+ / MariaDB 10.3+ |
| CSS Framework | Bootstrap 5.3.3 + SCSS |
| JS | jQuery 4, SweetAlert2 11 (qua Vite 7) |
| Rich-text Editor | TinyMCE 6.8.3 (CDN, `<x-admin.scripts.tinymce />`) |
| Auth API | Laravel Sanctum |

## Khởi động nhanh

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
# Import database/licuti-cms-laravel.sql (cấu hình DB_* trong .env trước)
php artisan storage:link
php artisan serve   # http://127.0.0.1:8000
```

Trong quá trình phát triển, chạy `npm run dev` để Vite hot-reload.

Tài khoản admin mặc định: `admin@licuti.com` (mật khẩu xem `database/seeders/UserSeeder.php`).

## Cấu trúc chính

```
app/
├── Core/              # Base classes, Enums, Traits, BulkAction Registry
├── DTOs/{Module}/     # Data Transfer Object (readonly, fromRequest)
├── Http/
│   ├── Controllers/Admin/
│   ├── Requests/Admin/{Module}/
│   └── Resources/     # API Resources
├── Models/            # Eloquent + {Model}Translation.php
├── Repositories/
│   ├── Interfaces/     # Hợp đồng (contract) — Service chỉ type-hint interface này
│   └── Eloquent/       # TOÀN BỘ implementation (kể cả Tầng 0/1: User, Role, Media, Language, Setting)
├── Services/Admin/{Module}/
resources/
├── css/app.scss       # SCSS duy nhất (build qua Vite)
├── js/app.js          # Entry JS + admin/ui-bridge, admin/table-utils
└── views/
    ├── admin/{module}/        # index.blade.php + form.blade.php (create/edit chung)
    ├── components/admin/      # Blade Component chuẩn
    └── layouts/
routes/
├── web.php            # Tất cả route Admin (prefix /admin)
├── api.php            # API (prefix /api/v1)
└── console.php
```

## Tài liệu

Toàn bộ quy chuẩn nằm trong **[DEVELOPER.md](DEVELOPER.md)** — đọc file đó trước khi viết code.
Các tài liệu quan trọng nhất:

- Kiến trúc & nguyên tắc phân lớp: [`docs/architecture/08`](docs/architecture/08-architecture-overview.md)
- Xây dựng 1 module mới A→Z: [`docs/architecture/11`](docs/architecture/11-module-tutorial.md)
- Chuẩn UI & Blade Component: [`docs/architecture/13`](docs/architecture/13-ui-conventions.md)
- Checklist trước khi xong: [`docs/architecture/14`](docs/architecture/14-checklist.md)
- Cấu trúc DB: [`docs/02`](docs/02-database-overview.md) → [`docs/03`](docs/03-database-details.md)

## Lệnh hữu ích

```bash
php artisan migrate --seed   # Chạy migration + seed
php artisan test             # Chạy test (xem docs/architecture/15-testing.md)
npm run build                # Build production
npm run dev                  # Vite dev server
```

## License

MIT.
