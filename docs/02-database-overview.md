# PHẦN 2: TỔNG QUAN CÁC NHÓM BẢNG DATABASE

- **CORE SYSTEM (11 bảng)**: `users`, `roles`, `permissions`, `permission_groups`, `role_permission`, `user_role`, `user_permission`, `data_scopes`, `password_reset_tokens`, `personal_access_tokens`, `sessions`
- **PRODUCT MANAGEMENT (14 bảng)**: `categories`, `category_translations`, `brands`, `brand_translations`, `products`, `product_translations`, `product_images`, `product_attributes`, `product_attribute_translations`, `product_attribute_values`, `product_attribute` (pivot), `product_attribute_value` (pivot), `product_variants`, `product_variant_attribute_values` (pivot), `product_tag` (pivot), `product_reviews`, `product_wishlists`
- **ORDER & CART (6 bảng)**: `carts`, `cart_items`, `shipping_addresses`, `orders`, `order_items`, `order_status_histories`
- **PAYMENT (3 bảng)**: `payment_methods`, `payments`, `transactions`
- **INVENTORY (3 bảng)**: `warehouses`, `inventories`, `inventory_histories`
- **PROMOTION (4 bảng)**: `coupons`, `coupon_users`, `flash_sales`, `flash_sale_products`
- **CMS (13 bảng)**: `posts`, `post_translations`, `post_categories`, `post_category_translations`, `post_category_post`, `tags`, `post_tag`, `pages`, `page_translations`, `banners`, `banner_translations`, `menus`, `menu_items`
- **SEO (1 bảng)**: `seo_metadata` (Polymorphic SEO cho Posts, Products, Pages, Categories...)

> **Ghi chú refactor Page module 09/2026**: `pages` bỏ `template`/`is_active`/`meta_*` (dùng
> `status` theo `ContentStatus` chung + `page_template` enum + `parent_id` cây phân cấp +
> `published_at` lịch đăng); SEO mọi nơi chỉ nằm ở **`seo_metadata`** per-locale (`<x-admin.seo-meta>`).
> Chi tiết cột: xem [03-database-details.md](03-database-details.md).
- **MEDIA (1 bảng)**: `media`
- **SETTINGS (4 bảng)**: `languages`, `settings`, `email_templates`, `sms_templates`
- **LOGS & TRACKING (3 bảng)**: `activity_logs`, `login_histories`, `notifications`

---

## TỔNG KẾT VÀ PHÂN BỔ THỰC TẾ (Cập nhật 10/2026)

- **Tổng số bảng thiết kế mục tiêu**: ~63 bảng.
- **Bảng đã có migration & dữ liệu hoạt động thật (Tầng 0–4)**: ~38 bảng (Core, RBAC, Media, Settings, Languages, CMS Posts/Pages/Tags/Banners/Menus, Catalog Categories/Brands/Attributes/Variants/Products, Pivots).
- **Bảng đang ở mức Shell stub hoặc chưa tạo migration (Tầng 5–7)**: ~25 bảng (Cart, Order, Payment, Promotion, Inventory, Logs).
- **Master Tracker theo dõi tiến độ chi tiết từng module**: [`docs/modules/00-module-status.md`](modules/00-module-status.md).