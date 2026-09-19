# PHẦN 7: CHECKLIST TRIỂN KHAI (LEGACY)

> ⚠️ **TÀI LIỆU LEGACY (v1.0 — 2024).** Checklist này theo dõi tiến độ theo **phase** của bản
> thiết kế ban đầu và hiện không còn được dùng để kiểm tra chất lượng. Khi làm việc thực tế:
>
> - Thứ tự build module: [`architecture/07-development-process.md`](architecture/07-development-process.md) (Phần 2 — Build Order theo tầng phụ thuộc).
> - Checklist trước khi hoàn thành: [`architecture/14-checklist.md`](architecture/14-checklist.md).
>
> File này chỉ giữ lại để theo dõi tiến độ tổng thể (check ✅/⬜ theo hiện trạng dự án).

### PHASE 1 - CORE SYSTEM:
- [ ] Tạo migrations cho `users`, `roles`, `permissions`, pivots
- [ ] Tạo models & relationships
- [ ] Tạo `HasPermissions` trait
- [ ] Tạo middleware `CheckPermission`
- [ ] Seed permissions & roles
- [ ] Authentication (login, register, reset password)
- [ ] Admin dashboard cơ bản

### PHASE 2 - PRODUCT & CATEGORY:
- [ ] categories + translations
- [ ] brands + translations
- [ ] products + translations
- [ ] product images, variants, attributes
- [ ] Admin CRUD cho products

### PHASE 3 - ORDER & CART:
- [ ] carts, cart_items
- [ ] shipping_addresses
- [ ] orders, order_items
- [ ] order_status_histories
- [ ] Checkout flow

### PHASE 4 - PAYMENT:
- [ ] payment_methods
- [ ] payments, transactions
- [ ] Payment gateway integration (VNPay, Momo...)

### PHASE 5 - CMS:
- [ ] posts + translations
- [ ] pages + translations
- [ ] banners + translations
- [ ] menus, menu_items

### PHASE 6 - ADVANCED FEATURES:
- [ ] Inventory management
- [ ] Coupons & promotions
- [ ] Flash sales
- [ ] Reports & analytics
- [ ] Activity logs

### PHASE 7 - OPTIMIZATION:
- [ ] Cache (Redis/Memcached)
- [ ] Database indexes
- [ ] API rate limiting
- [ ] Security hardening
- [ ] Performance tuning

---

## DOCUMENT INFO
- **Version**: 1.0
- **Last Updated**: 2024
- **Framework**: Laravel 10+/11+
- **Database**: MySQL 8.0+ / PostgreSQL 13+
