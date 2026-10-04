# PHẦN 7: CHECKLIST TRIỂN KHAI (LEGACY)

> ⚠️ **TÀI LIỆU LEGACY (v1.0 — 2024 / Cập nhật trạng thái 10/2026).**
> Checklist này theo dõi tiến độ theo **phase** của bản thiết kế ban đầu.
> **Bảng theo dõi tiến độ chuẩn hiện tại của dự án theo 8 tầng phụ thuộc:** [`modules/00-module-status.md`](modules/00-module-status.md).
>
> Khi làm việc thực tế:
> - Quy trình & thứ tự build module: [`07-development-process.md`](07-development-process.md) (Phần 2 — Build Order theo tầng phụ thuộc).
> - Checklist trước khi hoàn thành: [`architecture/14-checklist.md`](architecture/14-checklist.md).

### PHASE 1 - CORE SYSTEM:
- [x] Tạo migrations cho `users`, `roles`, `permissions`, pivots
- [x] Tạo models & relationships
- [x] Tạo `HasPermissions` trait / Spatie permission
- [x] Tạo middleware `CheckPermission` / `AdminMiddleware` + `admin.access` gate
- [x] Seed permissions & roles
- [x] Authentication (login, register, reset password)
- [x] Admin dashboard cơ bản

### PHASE 2 - PRODUCT & CATEGORY:
- [x] categories + translations
- [x] brands + translations
- [x] products + translations
- [x] product images, variants, attributes
- [x] Admin CRUD cho products (Revamp Form P0–P2, Architecture upgrade P0–P3)

### PHASE 3 - ORDER & CART:
- [ ] carts, cart_items (hiện mới có shell stub `carts`)
- [ ] shipping_addresses
- [ ] orders, order_items (hiện mới có shell stub `orders`)
- [ ] order_status_histories
- [ ] Checkout flow

### PHASE 4 - PAYMENT:
- [ ] payment_methods (hiện mới có shell stub)
- [ ] payments, transactions (hiện mới có shell stub `payments`)
- [ ] Payment gateway integration (VNPay, Momo...)

### PHASE 5 - CMS:
- [x] posts + translations
- [x] pages + translations
- [x] banners + translations
- [x] menus, menu_items
- [x] tags (dùng chung cho Post + Product)

### PHASE 6 - ADVANCED FEATURES:
- [ ] Inventory management (StockService groundwork hoàn thành, DB inventories hiện là shell stub)
- [ ] Coupons & promotions
- [ ] Flash sales
- [ ] Reports & analytics
- [ ] Activity logs

### PHASE 7 - OPTIMIZATION & HARDENING:
- [x] Database indexes (slug, locale, category_id, brand_id, product_translation indexes...)
- [x] Security hardening (admin.access gate, AuthorizesWithPermission, BulkActionRegistry)
- [x] Eager loading & N+1 fix (Product list, Media picker)
- [x] Catalog cache & Observers invalidation
- [ ] Full Redis / Memcached production setup
- [ ] API rate limiting production tuning

---

## DOCUMENT INFO
- **Version**: 1.2 (Đồng bộ theo Master Tracker 10/2026)
- **Last Updated**: 10/2026
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: MySQL 8.0+ / MariaDB 10.4+
