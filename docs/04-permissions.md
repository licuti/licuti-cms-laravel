# PHáº¦N 4: DANH SÃCH PERMISSIONS

### ADMIN ACCESS
- `admin.access`: Quyá»n truy cáº­p khu vá»±c Quáº£n trá»‹ (gate táº¡i `User::canAccessAdmin()`, dÃ¹ng trong `AdminMiddleware` + `AuthController`). Cáº¥p cho `super-admin` / `admin` / `editor`.

### USERS
- `users.view`: Xem danh sÃ¡ch ngÆ°á»i dÃ¹ng
- `users.view-detail`: Xem chi tiáº¿t ngÆ°á»i dÃ¹ng
- `users.create`: Táº¡o ngÆ°á»i dÃ¹ng
- `users.update`: Sá»­a ngÆ°á»i dÃ¹ng
- `users.delete`: XÃ³a ngÆ°á»i dÃ¹ng
- `users.restore`: KhÃ´i phá»¥c ngÆ°á»i dÃ¹ng
- `users.force-delete`: XÃ³a vÄ©nh viá»…n
- `users.export`: Xuáº¥t dá»¯ liá»‡u
- `users.import`: Nháº­p dá»¯ liá»‡u

### ROLES
- `roles.view`: Xem vai trÃ²
- `roles.create`: Táº¡o vai trÃ²
- `roles.update`: Sá»­a vai trÃ²
- `roles.delete`: XÃ³a vai trÃ²

### PERMISSIONS
- `permissions.view`: Xem quyá»n
- `permissions.assign`: GÃ¡n quyá»n
- `permissions.revoke`: Thu há»“i quyá»n

### CATEGORIES
- `categories.view`: Xem danh má»¥c
- `categories.create`: Táº¡o danh má»¥c
- `categories.update`: Sá»­a danh má»¥c
- `categories.delete`: XÃ³a danh má»¥c

### POST CATEGORIES (danh má»¥c bÃ i viáº¿t)
- `post-categories.view`: Xem danh má»¥c bÃ i viáº¿t
- `post-categories.create`: Táº¡o danh má»¥c bÃ i viáº¿t
- `post-categories.update`: Sá»­a danh má»¥c bÃ i viáº¿t
- `post-categories.delete`: XÃ³a danh má»¥c bÃ i viáº¿t

### TAGS
- `tags.view`: Xem tháº» tag
- `tags.create`: Táº¡o tháº» tag
- `tags.update`: Sá»­a tháº» tag
- `tags.delete`: XÃ³a tháº» tag

### MENUS
- `menus.view`: Xem menu
- `menus.create`: Táº¡o menu
- `menus.update`: Sá»­a menu
- `menus.delete`: XÃ³a menu

### BRANDS
- `brands.view`: Xem thÆ°Æ¡ng hiá»‡u
- `brands.create`: Táº¡o thÆ°Æ¡ng hiá»‡u
- `brands.update`: Sá»­a thÆ°Æ¡ng hiá»‡u
- `brands.delete`: XÃ³a thÆ°Æ¡ng hiá»‡u

### PRODUCTS
- `products.view`: Xem sáº£n pháº©m
- `products.view-detail`: Xem chi tiáº¿t sáº£n pháº©m
- `products.create`: Táº¡o sáº£n pháº©m
- `products.update`: Sá»­a sáº£n pháº©m
- `products.delete`: XÃ³a sáº£n pháº©m
- `products.publish`: Xuáº¥t báº£n
- `products.approve`: Duyá»‡t sáº£n pháº©m
- `products.export`: Xuáº¥t dá»¯ liá»‡u
- `products.import`: Nháº­p dá»¯ liá»‡u

### PRODUCT ATTRIBUTES
- `product-attributes.view`: Xem thuộc tính sản phẩm
- `product-attributes.create`: Tạo thuộc tính sản phẩm
- `product-attributes.update`: Sửa thuộc tính sản phẩm
- `product-attributes.delete`: Xóa thuộc tính sản phẩm

### ORDERS
- `orders.view`: Xem Ä‘Æ¡n hÃ ng
- `orders.view-all`: Xem táº¥t cáº£ Ä‘Æ¡n hÃ ng
- `orders.view-detail`: Xem chi tiáº¿t Ä‘Æ¡n hÃ ng
- `orders.update`: Cáº­p nháº­t Ä‘Æ¡n hÃ ng
- `orders.cancel`: Há»§y Ä‘Æ¡n hÃ ng
- `orders.refund`: HoÃ n tiá»n
- `orders.export`: Xuáº¥t dá»¯ liá»‡u

### INVENTORY
- `inventory.view`: Xem tá»“n kho
- `inventory.import`: Nháº­p kho
- `inventory.export`: Xuáº¥t kho
- `inventory.adjust`: Äiá»u chá»‰nh

### COUPONS
- `coupons.view`: Xem mÃ£ giáº£m giÃ¡
- `coupons.create`: Táº¡o mÃ£ giáº£m giÃ¡
- `coupons.update`: Sá»­a mÃ£ giáº£m giÃ¡
- `coupons.delete`: XÃ³a mÃ£ giáº£m giÃ¡

### POSTS
- `posts.view`: Xem bÃ i viáº¿t
- `posts.create`: Táº¡o bÃ i viáº¿t
- `posts.update`: Sá»­a bÃ i viáº¿t
- `posts.delete`: XÃ³a bÃ i viáº¿t
- `posts.publish`: Xuáº¥t báº£n

### PAGES
- `pages.view`: Xem trang
- `pages.create`: Táº¡o trang
- `pages.update`: Sá»­a trang
- `pages.delete`: XÃ³a trang

> **Cáº­p nháº­t 09/2026 (Pha 6):** 4 quyá»n trÃªn Ä‘Ã£ Ä‘Æ°á»£c enforce Ä‘áº§y Ä‘á»§ â€” FormRequest
> dÃ¹ng `AuthorizesWithPermission` (`pages.create`/`pages.update`), bulk action
> gate qua `BulkActionRegistry` (`delete` â†’ `pages.delete`, Ä‘á»•i tráº¡ng thÃ¡i â†’
> `pages.update`). KhÃ´ng cÃ³ (vÃ  khÃ´ng cáº§n) quyá»n `pages.bulk` riÃªng.

### BANNERS
- `banners.view`: Xem banner
- `banners.create`: Táº¡o banner
- `banners.update`: Sá»­a banner
- `banners.delete`: XÃ³a banner

### MEDIA
- `media.view`: Xem media
- `media.upload`: Upload file
- `media.delete`: XÃ³a file

### REPORTS
- `reports.sales`: BÃ¡o cÃ¡o doanh sá»‘
- `reports.inventory`: BÃ¡o cÃ¡o tá»“n kho
- `reports.customers`: BÃ¡o cÃ¡o khÃ¡ch hÃ ng
- `reports.export`: Xuáº¥t bÃ¡o cÃ¡o

### SETTINGS
- `settings.view`: Xem cáº¥u hÃ¬nh
- `settings.update`: Cáº­p nháº­t cáº¥u hÃ¬nh
- `settings.general`: CÃ i Ä‘áº·t chung
- `settings.payment`: CÃ i Ä‘áº·t thanh toÃ¡n
- `settings.email`: CÃ i Ä‘áº·t email
- `settings.sms`: CÃ i Ä‘áº·t SMS

### LOGS
- `logs.view`: Xem nháº­t kÃ½
- `logs.clear`: XÃ³a nháº­t kÃ½
