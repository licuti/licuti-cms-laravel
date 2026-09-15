# PHẦN 5: SƠ ĐỒ QUAN HỆ & PHẦN 6: THỐNG KÊ TỔNG HỢP

## PHẦN 5: SƠ ĐỒ QUAN HỆ (SIMPLIFIED)

### CORE SYSTEM:
`users` ↔ `user_role` ↔ `roles` ↔ `role_permission` ↔ `permissions` ↔ `permission_groups`
`users` ↔ `user_permission` ↔ `permissions`
`users` ↔ `data_scopes`

### PRODUCT MANAGEMENT:
`categories` ↔ `category_translations`
`brands` ↔ `brand_translations`
`products` ↔ `product_translations`
`products` ↔ `categories`
`products` ↔ `brands`
`products` ↔ `product_images`
`products` ↔ `product_variants` ↔ `product_variant_attributes` ↔ `product_attributes`
`product_attributes` ↔ `product_attribute_translations`
`products` ↔ `product_reviews` ↔ `users`
`products` ↔ `product_wishlists` ↔ `users`

### ORDER & CART:
`users` ↔ `carts` ↔ `cart_items` ↔ `products/variants`
`users` ↔ `shipping_addresses`
`users` ↔ `orders` ↔ `order_items` ↔ `products/variants`
`orders` ↔ `order_status_histories`
`orders` ↔ `payments` ↔ `transactions`

### INVENTORY:
`warehouses` ↔ `inventories` ↔ `products/variants`
`warehouses` ↔ `inventory_histories` ↔ `products/variants`

### PROMOTION:
`coupons` ↔ `coupon_users` ↔ `users/orders`
`flash_sales` ↔ `flash_sale_products` ↔ `products/variants`

### CMS:
`post_categories` ↔ `post_category_translations`
`post_categories` ↔ `post_categories` (self-reference parent_id)
`posts` ↔ `post_translations`
`posts` ↔ `post_category_post` ↔ `post_categories` (Many-to-Many pivot)
`posts` ↔ `users (author)`
`posts` ↔ `media` (ảnh đại diện qua UUID / path)
`posts` ↔ `post_tag` ↔ `tags`
`pages` ↔ `page_translations`
`banners` ↔ `banner_translations`
`menus` ↔ `menu_items` (self-reference)

### SEO (POLYMORPHIC):
`posts` / `products` / `post_categories` / `pages` ↔ `seo_metadata` (qua trait HasSeo)

---

## PHẦN 6: THỐNG KÊ TỔNG HỢP

- **TỔNG SỐ BẢNG**: ~62 bảng
- **TỔNG SỐ PERMISSIONS**: ~60 permissions

### PHÂN BỔ BẢNG:
- Core System: 11 bảng
- Product Management: 13 bảng
- Order & Cart: 6 bảng
- Payment: 3 bảng
- Inventory: 3 bảng
- Promotion: 4 bảng
- CMS: 13 bảng
- SEO: 1 bảng
- Media: 1 bảng
- Settings: 4 bảng
- Logs & Tracking: 3 bảng
