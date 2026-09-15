# PHẦN 2: TỔNG QUAN CÁC NHÓM BẢNG DATABASE

- **CORE SYSTEM (11 bảng)**: `users`, `roles`, `permissions`, `permission_groups`, `role_permission`, `user_role`, `user_permission`, `data_scopes`, `password_reset_tokens`, `personal_access_tokens`, `sessions`
- **PRODUCT MANAGEMENT (13 bảng)**: `categories`, `category_translations`, `brands`, `brand_translations`, `products`, `product_translations`, `product_images`, `product_attributes`, `product_attribute_translations`, `product_variants`, `product_variant_attributes`, `product_reviews`, `product_wishlists`
- **ORDER & CART (6 bảng)**: `carts`, `cart_items`, `shipping_addresses`, `orders`, `order_items`, `order_status_histories`
- **PAYMENT (3 bảng)**: `payment_methods`, `payments`, `transactions`
- **INVENTORY (3 bảng)**: `warehouses`, `inventories`, `inventory_histories`
- **PROMOTION (4 bảng)**: `coupons`, `coupon_users`, `flash_sales`, `flash_sale_products`
- **CMS (13 bảng)**: `posts`, `post_translations`, `post_categories`, `post_category_translations`, `post_category_post`, `tags`, `post_tag`, `pages`, `page_translations`, `banners`, `banner_translations`, `menus`, `menu_items`
- **SEO (1 bảng)**: `seo_metadata` (Polymorphic SEO cho Posts, Products, Pages, Categories...)
- **MEDIA (1 bảng)**: `media`
- **SETTINGS (4 bảng)**: `languages`, `settings`, `email_templates`, `sms_templates`
- **LOGS & TRACKING (3 bảng)**: `activity_logs`, `login_histories`, `notifications`

**TỔNG CỘNG**: ~62 bảng