# TÀI LIỆU PHÁT TRIỂN DỰ ÁN LICUTI CMS

> Tài liệu hướng dẫn phát triển, vận hành và mở rộng dự án **Licuti CMS** (Laravel 12, Bootstrap 5.3 + SCSS, Service - Repository - DTO).
> File này là **mục lục tổng** — chi tiết từng phần nằm trong các file con trong `docs/`.
>
> 📌 **Mức ưu tiên khi có xung đột:** bộ `docs/architecture/*` (08–18) là chuẩn hiện hành.
> Các file `docs/01` → `docs/06` là **legacy v1.0** — chỉ mang tính tham khảo lịch sử.

---

## KHỞI ĐỘNG NHANH (Quickstart)

### Yêu cầu môi trường

| Tool | Version |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 20+ |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Extensions PHP | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` |

### Cài đặt lần đầu

```bash
# 1. Cài dependency PHP
composer install

# 2. Cài dependency JS + build assets
npm install && npm run build

# 3. Cấu hình môi trường
cp .env.example .env
php artisan key:generate

# 4. Import database (database/licuti-cms-laravel.sql)
#    Cấu hình DB_* trong .env trước khi import

# 5. Liên kết storage
php artisan storage:link

# 6. Chạy thử
php artisan serve   # http://127.0.0.1:8000
```

### Tài khoản admin mặc định (từ SQL)

- Email: `admin@licuti.com`
- Password: xem `database/seeders/UserSeeder.php` (nếu có) hoặc chạy `php artisan tinker` để reset:
  ```php
  \App\Models\User::first()->update(['password' => bcrypt('password')]);
  ```

---

## TÀI LIỆU CHI TIẾT

### 1. Cấu trúc dự án & Kiến trúc
- ⚠️ [docs/01-project-structure.md](docs/01-project-structure.md) — **LEGACY v1.0**. Sơ đồ thư mục cũ, nhiều điểm đã sai khác thực tế (đã chú thích `[FIXED]`).
- [README.md](README.md) — Giới thiệu dự án, tech stack, quickstart, cấu trúc thực tế.
- [docs/architecture/08-architecture-overview.md](docs/architecture/08-architecture-overview.md) — Kiến trúc phân lớp Service - Repository - DTO, nguyên tắc vàng, cấu trúc thư mục thực tế.
- [docs/architecture/09-base-classes.md](docs/architecture/09-base-classes.md) — `BaseService`, `BaseRepository`, `BaseRequest`, `BaseController`.

### 2. Cơ chế hệ thống cốt lõi
- [docs/architecture/10-core-mechanisms.md](docs/architecture/10-core-mechanisms.md) — i18n & SEO, sinh slug duy nhất, `BulkActionRegistry`, `form-confirm` toàn cục.
- [docs/architecture/18-hook-registry-system.md](docs/architecture/18-hook-registry-system.md) — Hook Registry (mở rộng hệ thống không phá vỡ core). Chỉ `BulkActionRegistry` đã hoạt động, còn lại TODO.

### 3. Database
- [docs/02-database-overview.md](docs/02-database-overview.md) — Phân bổ các nhóm bảng (Core, Product, Order, CMS…).
- [docs/03-database-details.md](docs/03-database-details.md) — Định nghĩa trường, khóa ngoại từng bảng.
- [docs/05-er-diagram-and-statistics.md](docs/05-er-diagram-and-statistics.md) — Sơ đồ quan hệ & số liệu thống kê.

### 4. Phân quyền
- [docs/04-permissions.md](docs/04-permissions.md) — Danh sách mã quyền trong hệ thống RBAC (kèm ghi chú nợ kỹ thuật chưa enforce).

### 5. Quy trình phát triển
- [docs/architecture/11-module-tutorial.md](docs/architecture/11-module-tutorial.md) — Xây dựng 1 module mới từ A→Z (Migration → Model → Repository → DTO → Service → Controller → View).
- [docs/architecture/12-extending-schema.md](docs/architecture/12-extending-schema.md) — Quy trình thêm trường dữ liệu mới + Layer Rules.
- [docs/architecture/13-ui-conventions.md](docs/architecture/13-ui-conventions.md) — Chuẩn Bootstrap 5.3 + danh mục Blade Component.
- [docs/architecture/14-checklist.md](docs/architecture/14-checklist.md) — Checklist kiểm tra trước khi hoàn thành tính năng.

### 6. Bổ sung
- [docs/architecture/15-testing.md](docs/architecture/15-testing.md) — Viết test cho Service / Repository / Feature.
- [docs/architecture/16-api-conventions.md](docs/architecture/16-api-conventions.md) — Chuẩn response API, error code, versioning.
- [docs/architecture/17-git-workflow.md](docs/architecture/17-git-workflow.md) — Quy ước branch, commit message, PR template.

### 7. Triển khai
- ⚠️ [docs/06-implementation-checklist.md](docs/06-implementation-checklist.md) — **LEGACY v1.0**. Checklist theo phase, chỉ dùng theo dõi tiến độ tổng thể. Kiểm tra chất lượng dùng `14-checklist.md`.
- [docs/modules/00-module-status.md](docs/modules/00-module-status.md) — **Bảng kê trạng thái tất cả module** (✅ hoàn thành / 🚧 shell / ⬜ chưa làm) + lỗi cấu trúc + thứ tự ưu tiên. Mỗi module có file chi tiết `docs/modules/{module}.md`.
- [docs/07-development-process.md](docs/07-development-process.md) — Quy trình phát triển chi tiết: thứ tự build, đặc tả module, **Full vs Simple Profile** (1.5), kỷ luật scaffold (1.6).
