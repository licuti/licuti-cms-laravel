# PHẦN 3: CHI TIẾT CÁC BẢNG DATABASE

## NHÓM 1: CORE SYSTEM - HỆ THỐNG CỐT LÕI

### TABLE: users
- **id**: Primary key
- **uuid**: UUID cho bảo mật (public thay vì id)
- **name**: Họ tên
- **email**: Email (unique)
- **email_verified_at**: Thời gian xác thực email
- **phone**: Số điện thoại
- **phone_verified_at**: Thời gian xác thực SĐT
- **password**: Mật khẩu (hashed)
- **avatar**: Đường dẫn ảnh đại diện
- **gender**: Giới tính (male/female/other)
- **birthday**: Ngày sinh
- **status**: Trạng thái (active/inactive/banned)
- **is_admin**: Có phải admin không
- **google_id**: Google OAuth ID
- **facebook_id**: Facebook OAuth ID
- **last_login_at**: Lần đăng nhập cuối
- **last_login_ip**: IP đăng nhập cuối
- **remember_token**: Token remember me
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
- **deleted_at**: Soft delete

### TABLE: roles
- **id**: Primary key
- **name**: Tên role (admin, editor, viewer...)
- **display_name**: Tên hiển thị
- **description**: Mô tả
- **level**: Cấp độ (1-100, cao hơn = quyền lớn hơn)
- **is_system**: Role hệ thống (không được xóa)
- **guard_name**: Guard (web/api)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: permissions
- **id**: Primary key
- **group_id**: FK → permission_groups
- **name**: Tên quyền (users.view, products.create...)
- **display_name**: Tên hiển thị
- **description**: Mô tả
- **guard_name**: Guard (web/api)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: permission_groups
- **id**: Primary key
- **name**: Tên nhóm (User Management, Products...)
- **code**: Mã nhóm (users, products...)
- **icon**: Icon
- **description**: Mô tả
- **display_order**: Thứ tự hiển thị
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: role_permission (Pivot)
- **role_id**: FK → roles
- **permission_id**: FK → permissions

### TABLE: user_role (Pivot)
- **user_id**: FK → users
- **role_id**: FK → roles

### TABLE: user_permission (Override)
- **id**: Primary key
- **user_id**: FK → users
- **permission_id**: FK → permissions
- **type**: Loại (allow/deny)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: data_scopes
- **id**: Primary key
- **user_id**: FK → users
- **scope_type**: Loại (department/region/store/category...)
- **scope_value**: Giá trị (sales/hanoi/store-1...)
- **resource_type**: Model (Product/Order/Customer...)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: password_reset_tokens
- **email**: Email (Primary key)
- **token**: Token reset
- **created_at**: Ngày tạo

### TABLE: personal_access_tokens (Sanctum)
- **id**: Primary key
- **tokenable_type**: Model type (App\Models\User)
- **tokenable_id**: Model ID
- **name**: Tên token
- **token**: Token (hashed)
- **abilities**: Quyền của token (JSON)
- **last_used_at**: Lần sử dụng cuối
- **expires_at**: Thời gian hết hạn
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: sessions
- **id**: Session ID (Primary key)
- **user_id**: FK → users
- **ip_address**: Địa chỉ IP
- **user_agent**: User agent
- **payload**: Session data
- **last_activity**: Hoạt động cuối

---

## NHÓM 2: PRODUCT MANAGEMENT - QUẢN LÝ SẢN PHẨM

### TABLE: categories
- **id**: Primary key
- **parent_id**: FK → categories (self-reference)
- **image**: Ảnh danh mục
- **icon**: Icon
- **is_active**: Trạng thái hoạt động
- **display_order**: Thứ tự hiển thị
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
- **deleted_at**: Soft delete

### TABLE: category_translations
- **id**: Primary key
- **category_id**: FK → categories
- **locale**: Ngôn ngữ (vi/en/ja...)
- **name**: Tên danh mục
- **slug**: Đường dẫn URL
- **description**: Mô tả
- **meta_title**: SEO title
- **meta_description**: SEO description
- **meta_keywords**: SEO keywords
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: brands
- **id**: Primary key
- **logo**: Logo
- **website**: Website
- **is_active**: Trạng thái hoạt động
- **display_order**: Thứ tự hiển thị
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
- **deleted_at**: Soft delete

### TABLE: brand_translations
- **id**: Primary key
- **brand_id**: FK → brands
- **locale**: Ngôn ngữ
- **name**: Tên thương hiệu
- **slug**: Đường dẫn URL
- **description**: Mô tả
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: products
- **id**: Primary key
- **uuid**: UUID
- **category_id**: FK → categories
- **brand_id**: FK → brands
- **sku**: Mã sản phẩm (unique)
- **barcode**: Mã vạch
- **price**: Giá gốc
- **sale_price**: Giá khuyến mãi
- **cost_price**: Giá vốn
- **weight**: Trọng lượng (gram)
- **length**: Chiều dài (cm)
- **width**: Chiều rộng (cm)
- **height**: Chiều cao (cm)
- **stock_quantity**: Số lượng tồn kho
- **low_stock_threshold**: Ngưỡng cảnh báo hết hàng
- **thumbnail**: Ảnh thumbnail
- **status**: Trạng thái (draft/published/out_of_stock)
- **is_featured**: Sản phẩm nổi bật
- **is_active**: Hoạt động
- **view_count**: Lượt xem
- **sold_count**: Số lượng đã bán
- **rating_average**: Điểm đánh giá trung bình
- **rating_count**: Số lượng đánh giá
- **published_at**: Ngày xuất bản
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
- **deleted_at**: Soft delete

### TABLE: product_translations
- **id**: Primary key
- **product_id**: FK → products
- **locale**: Ngôn ngữ
- **name**: Tên sản phẩm
- **slug**: Đường dẫn URL
- **short_description**: Mô tả ngắn
- **description**: Mô tả chi tiết
- **meta_title**: SEO title
- **meta_description**: SEO description
- **meta_keywords**: SEO keywords
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: product_images
- **id**: Primary key
- **product_id**: FK → products
- **image_path**: Đường dẫn ảnh
- **alt_text**: Alt text
- **display_order**: Thứ tự hiển thị
- **is_primary**: Ảnh chính
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: product_attributes
- **id**: Primary key
- **type**: Loại (text/select/color/number)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: product_attribute_translations
- **id**: Primary key
- **attribute_id**: FK → product_attributes
- **locale**: Ngôn ngữ
- **name**: Tên thuộc tính (Màu sắc, Kích thước...)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: product_variants
- **id**: Primary key
- **product_id**: FK → products
- **sku**: Mã biến thể (unique)
- **name**: Tên biến thể (256GB - Black)
- **price**: Giá riêng (nếu khác)
- **sale_price**: Giá khuyến mãi
- **stock_quantity**: Số lượng tồn kho
- **image**: Ảnh biến thể
- **is_active**: Hoạt động
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: product_variant_attributes
- **id**: Primary key
- **variant_id**: FK → product_variants
- **attribute_id**: FK → product_attributes
- **value**: Giá trị (256GB, Black, XL...)

### TABLE: product_reviews
- **id**: Primary key
- **product_id**: FK → products
- **user_id**: FK → users
- **order_id**: FK → orders (chỉ cho phép review khi đã mua)
- **rating**: Điểm (1-5)
- **title**: Tiêu đề đánh giá
- **comment**: Nội dung đánh giá
- **images**: Ảnh đánh giá (JSON)
- **is_approved**: Đã duyệt
- **approved_by**: FK → users (admin duyệt)
- **approved_at**: Thời gian duyệt
- **helpful_count**: Số lượt hữu ích
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
- **deleted_at**: Soft delete

### TABLE: product_wishlists
- **id**: Primary key
- **user_id**: FK → users
- **product_id**: FK → products
- **created_at**: Ngày tạo

---

## NHÓM 3: ORDER & CART - ĐƠN HÀNG & GIỎ HÀNG

### TABLE: carts
- **id**: Primary key
- **user_id**: FK → users (nullable cho guest)
- **session_id**: Session ID (cho guest)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: cart_items
- **id**: Primary key
- **cart_id**: FK → carts
- **product_id**: FK → products
- **variant_id**: FK → product_variants
- **quantity**: Số lượng
- **price**: Giá tại thời điểm thêm vào giỏ
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: shipping_addresses
- **id**: Primary key
- **user_id**: FK → users
- **full_name**: Họ tên người nhận
- **phone**: Số điện thoại
- **address_line1**: Địa chỉ dòng 1
- **address_line2**: Địa chỉ dòng 2
- **ward**: Phường/Xã
- **district**: Quận/Huyện
- **city**: Tỉnh/Thành phố
- **postal_code**: Mã bưu điện
- **country**: Quốc gia
- **is_default**: Địa chỉ mặc định
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
- **deleted_at**: Soft delete

### TABLE: orders
- **id**: Primary key
- **order_number**: Mã đơn hàng (ORD-20240115-0001)
- **user_id**: FK → users
- **shipping_full_name**: Họ tên người nhận
- **shipping_phone**: SĐT người nhận
- **shipping_address_line1**: Địa chỉ dòng 1
- **shipping_address_line2**: Địa chỉ dòng 2
- **shipping_ward**: Phường/Xã
- **shipping_district**: Quận/Huyện
- **shipping_city**: Tỉnh/Thành phố
- **shipping_postal_code**: Mã bưu điện
- **shipping_country**: Quốc gia
- **billing_full_name**: Họ tên thanh toán
- **billing_phone**: SĐT thanh toán
- **billing_address**: Địa chỉ thanh toán
- **subtotal**: Tổng tiền hàng
- **shipping_fee**: Phí vận chuyển
- **tax**: Thuế
- **discount**: Giảm giá
- **total**: Tổng cộng
- **coupon_code**: Mã giảm giá
- **coupon_discount**: Số tiền giảm từ coupon
- **payment_method**: Phương thức thanh toán
- **payment_status**: Trạng thái thanh toán (pending/paid/failed/refunded)
- **paid_at**: Thời gian thanh toán
- **status**: Trạng thái đơn (pending/confirmed/processing/shipped/delivered/completed/cancelled/refunded)
- **customer_note**: Ghi chú của khách
- **admin_note**: Ghi chú của admin
- **tracking_number**: Mã vận đơn
- **confirmed_at**: Thời gian xác nhận
- **shipped_at**: Thời gian giao hàng
- **delivered_at**: Thời gian nhận hàng
- **completed_at**: Thời gian hoàn thành
- **cancelled_at**: Thời gian hủy
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: order_items
- **id**: Primary key
- **order_id**: FK → orders
- **product_id**: FK → products (nullable nếu xóa product)
- **variant_id**: FK → product_variants
- **product_name**: Snapshot tên sản phẩm
- **product_sku**: Snapshot SKU
- **variant_name**: Snapshot tên biến thể
- **product_image**: Snapshot ảnh
- **price**: Giá tại thời điểm mua
- **quantity**: Số lượng
- **subtotal**: Thành tiền
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: order_status_histories
- **id**: Primary key
- **order_id**: FK → orders
- **from_status**: Trạng thái trước
- **to_status**: Trạng thái sau
- **note**: Ghi chú
- **changed_by**: FK → users (người thay đổi)
- **created_at**: Ngày tạo

---

## NHÓM 4: PAYMENT - THANH TOÁN

### TABLE: payment_methods
- **id**: Primary key
- **name**: Tên (COD, VNPay, Momo...)
- **code**: Mã
- **description**: Mô tả
- **logo**: Logo
- **config**: Cấu hình (JSON - API keys...)
- **is_active**: Hoạt động
- **display_order**: Thứ tự hiển thị
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: payments
- **id**: Primary key
- **order_id**: FK → orders
- **payment_method**: Phương thức
- **amount**: Số tiền
- **currency**: Đơn vị tiền tệ
- **status**: Trạng thái (pending/processing/completed/failed/refunded)
- **transaction_id**: ID từ payment gateway
- **gateway_response**: Response đầy đủ (JSON)
- **ip_address**: IP người thanh toán
- **user_agent**: User agent
- **paid_at**: Thời gian thanh toán
- **refunded_at**: Thời gian hoàn tiền
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: transactions
- **id**: Primary key
- **transactionable_type**: Model type (polymorphic)
- **transactionable_id**: Model ID
- **type**: Loại (payment/refund/adjustment)
- **amount**: Số tiền
- **currency**: Đơn vị tiền tệ
- **description**: Mô tả
- **reference**: Mã tham chiếu
- **created_at**: Ngày tạo

---

## NHÓM 5: INVENTORY - KHO HÀNG

### TABLE: warehouses
- **id**: Primary key
- **name**: Tên kho
- **code**: Mã kho
- **address**: Địa chỉ
- **phone**: Số điện thoại
- **manager_name**: Tên người quản lý
- **is_active**: Hoạt động
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: inventories
- **id**: Primary key
- **warehouse_id**: FK → warehouses
- **product_id**: FK → products
- **variant_id**: FK → product_variants
- **quantity**: Tổng số lượng
- **reserved_quantity**: Số lượng đang giữ (cho đơn hàng)
- **available_quantity**: Số lượng có thể bán
- **updated_at**: Ngày cập nhật

### TABLE: inventory_histories
- **id**: Primary key
- **warehouse_id**: FK → warehouses
- **product_id**: FK → products
- **variant_id**: FK → product_variants
- **type**: Loại (import/export/adjustment/return)
- **quantity**: Số lượng
- **reason**: Lý do
- **reference_type**: Model type tham chiếu (Order, PurchaseOrder...)
- **reference_id**: Model ID tham chiếu
- **user_id**: FK → users (người thực hiện)
- **created_at**: Ngày tạo

---

## NHÓM 6: PROMOTION - KHUYẾN MÃI

### TABLE: coupons
- **id**: Primary key
- **code**: Mã coupon (unique)
- **name**: Tên coupon
- **description**: Mô tả
- **type**: Loại (percentage/fixed/free_shipping)
- **value**: Giá trị giảm
- **min_order_value**: Giá trị đơn hàng tối thiểu
- **max_discount_amount**: Số tiền giảm tối đa
- **usage_limit**: Tổng số lần sử dụng
- **usage_per_user**: Số lần sử dụng/user
- **used_count**: Số lần đã sử dụng
- **start_date**: Ngày bắt đầu
- **end_date**: Ngày kết thúc
- **is_active**: Hoạt động
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: coupon_users
- **id**: Primary key
- **coupon_id**: FK → coupons
- **user_id**: FK → users
- **order_id**: FK → orders
- **discount_amount**: Số tiền đã giảm
- **used_at**: Thời gian sử dụng

### TABLE: flash_sales
- **id**: Primary key
- **name**: Tên flash sale
- **description**: Mô tả
- **start_time**: Thời gian bắt đầu
- **end_time**: Thời gian kết thúc
- **is_active**: Hoạt động
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: flash_sale_products
- **id**: Primary key
- **flash_sale_id**: FK → flash_sales
- **product_id**: FK → products
- **variant_id**: FK → product_variants
- **original_price**: Giá gốc
- **sale_price**: Giá flash sale
- **quantity_limit**: Giới hạn số lượng
- **sold_quantity**: Số lượng đã bán

---

## NHÓM 7: CMS - QUẢN LÝ NỘI DUNG

### TABLE: posts
- **id**: Primary key
- **uuid**: UUID cho bảo mật (public URL thay vì id)
- **author_id**: FK → users (tác giả bài viết)
- **image**: Ảnh đại diện (đường dẫn storage hoặc Media UUID)
- **status**: Trạng thái (draft / published / archived)
- **is_featured**: Nổi bật (boolean)
- **view_count**: Lượt xem
- **published_at**: Ngày giờ xuất bản
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
- **deleted_at**: Soft delete

### TABLE: post_category_post (Pivot)
- **post_id**: FK → posts (cascade on delete)
- **post_category_id**: FK → post_categories (cascade on delete)
- *Ghi chú*: Cho phép một bài viết thuộc nhiều chuyên mục (Many-to-Many). Khóa duy nhất tổ hợp `(post_id, post_category_id)`.

### TABLE: post_translations
- **id**: Primary key
- **post_id**: FK → posts
- **locale**: Ngôn ngữ
- **title**: Tiêu đề
- **slug**: Đường dẫn URL
- **excerpt**: Tóm tắt
- **content**: Nội dung
- **meta_title**: SEO title
- **meta_description**: SEO description
- **meta_keywords**: SEO keywords
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: post_categories
- **id**: Primary key
- **parent_id**: FK → post_categories (self-reference)
- **image**: Ảnh
- **is_active**: Hoạt động
- **display_order**: Thứ tự hiển thị
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: post_category_translations
- **id**: Primary key
- **category_id**: FK → post_categories
- **locale**: Ngôn ngữ
- **name**: Tên
- **slug**: Đường dẫn URL
- **description**: Mô tả
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: tags
- **id**: Primary key
- **name**: Tên tag
- **slug**: Đường dẫn URL
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: post_tag (Pivot)
- **post_id**: FK → posts
- **tag_id**: FK → tags

### TABLE: pages
> Module **Trang tĩnh (Page)** — dùng chung `ContentStatus` enum với Post (**không** còn cột
> `template`/`is_active`/`meta_*` như thiết kế cũ; dữ liệu xem `database/migrations/2026_09_1*`).

- **id**: Primary key (auto-increment)
- **uuid**: CHAR(36) UNIQUE — route key công khai (`/admin/pages/{uuid}/edit`), không lộ `id`
- **parent_id**: FK nullable self-ref → pages (`nullOnDelete`) — phân cấp trang cha/con; UI chặn chọn subtree (chống cycle)
- **page_template**: VARCHAR(50), default `default` — enum `App\Core\Enums\PageTemplate` (default/full_width/landing/contact_us)
- **published_at**: TIMESTAMP nullable — **ngày đăng nghiệp vụ / lịch đăng** (cron `<= now()` khi publish). Không dùng `created_at`/`updated_at` vì chúng là audit auto-managed; đổi lịch phải là cột riêng (pattern giống `posts`)
- **image**: VARCHAR nullable — ảnh đại diện (UUID Media / path)
- **display_order**: INT default 0 — sắp xếp
- **status**: VARCHAR(20), default `draft`, INDEX — enum chung `ContentStatus` (published/draft/archived)
- **created_at / updated_at**: Eloquent timestamps kiểm toán entity
- **deleted_at**: SoftDeletes

### TABLE: page_translations
> Nội dung **đa ngôn ngữ** của trang. SEO **không nằm ở đây** — xem `seo_metadata`.

- **id**: Primary key
- **page_id**: FK cascade → pages
- **locale**: VARCHAR(10) — `UNIQUE(page_id, locale)`
- **title**: VARCHAR(255) — bắt buộc (default locale) khi lưu form
- **slug**: VARCHAR nullable, INDEX — tự sinh từ title nếu để trống (`generateUniqueSlug`, suffix `-1/-2` chống trùng locale); validate regex `^[a-z0-9]+(?:-[a-z0-9]+)*$`
- **excerpt**: TEXT nullable — **mô tả ngắn** (khớp cột `excerpt`); dùng làm fallback meta_description trong SEO preview
- **content**: LONGTEXT — nội dung HTML (TinyMCE)
- **created_at / updated_at**: **audit per-locale** — mỗi bản dịch có vòng đời chỉnh sửa riêng (không phải trùng lặp với cha: đổi `status`/`parent_id` không làm `updated_at` bản dịch nhảy; dịch ngôn ngữ khác không đụng timestamp bản dịch này)

### TABLE: banners
- **id**: Primary key
- **position**: Vị trí (home_slider/sidebar/header...)
- **link**: Đường dẫn khi click
- **display_order**: Thứ tự hiển thị
- **start_date**: Ngày bắt đầu
- **end_date**: Ngày kết thúc
- **is_active**: Hoạt động
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: banner_translations
- **id**: Primary key
- **banner_id**: FK → banners
- **locale**: Ngôn ngữ
- **title**: Tiêu đề
- **image**: Ảnh banner
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: menus
- **id**: Primary key
- **name**: Tên menu
- **location**: Vị trí (header/footer/sidebar...)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: menu_items
- **id**: Primary key
- **menu_id**: FK → menus
- **parent_id**: FK → menu_items (self-reference)
- **title**: Tiêu đề
- **url**: Đường dẫn
- **target**: Target (_self/_blank)
- **icon**: Icon
- **display_order**: Thứ tự hiển thị
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

---

## NHÓM 8: MEDIA - QUẢN LÝ FILE

### TABLE: media
- **id**: Primary key
- **uuid**: UUID nhận diện công khai
- **user_id**: FK → users (người upload)
- **folder_id**: FK → media_folders (thư mục chứa file)
- **disk**: Disk storage (public/s3...)
- **file_path**: Đường dẫn file gốc
- **thumb_path**: Đường dẫn ảnh thumbnail
- **file_name**: Tên file lưu trên disk
- **original_name**: Tên file gốc ban đầu
- **mime_type**: Loại file (image/jpeg, application/pdf...)
- **size**: Kích thước (bytes)
- **alt_text**: Alt text cho hình ảnh (hỗ trợ SEO)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

---

## NHÓM 9: SEO - TỐI ƯU HÓA TÌM KIẾM (POLYMORPHIC)

> **Nguồn duy nhất (single source of truth)** của SEO metadata mọi thực thể là bảng này, per-locale.
> Từ refactor module Page (09/2026), các bảng cha (`pages`, `posts`...) **không còn cột
> `meta_title`/`meta_description`/`meta_keywords`** — form nhập SEO qua
> `<x-admin.seo-meta>` → `translations[xx][meta_*]` → ghi vào đây (`HasSeo::saveSeoTranslations`).

### TABLE: seo_metadata
- **id**: Primary key
- **seoable_type**: Model class đa hình (Post, Product, PostCategory, Page...)
- **seoable_id**: Model ID tương ứng
- **locale**: Mã ngôn ngữ (vi, en...)
- **meta_title**: Tiêu đề trang (SEO title, tối đa ~60 ký tự)
- **meta_description**: Mô tả tóm tắt cho Google (tối đa ~160 ký tự)
- **meta_keywords**: Từ khóa SEO (phân tách bởi dấu phẩy)
- **og_title**: Tiêu đề chia sẻ mạng xã hội (OpenGraph / Facebook / Zalo)
- **og_description**: Mô tả chia sẻ mạng xã hội
- **og_image**: Ảnh đại diện chia sẻ MXH (liên kết Media Library hoặc URL)
- **canonical_url**: URL gốc chuẩn hóa
- **schema_type**: Loại Schema.org Structured Data (Article, NewsArticle, BlogPosting, WebPage, Product...)
- **robots_index**: Cho phép Google index (boolean, mặc định true)
- **robots_follow**: Cho phép Google cào liên kết (boolean, mặc định true)
- **focus_keyword**: Từ khóa mục tiêu chính
- **seo_score**: Điểm đánh giá chất lượng SEO (0 - 100)
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
- *Ghi chú*: Sử dụng tự động qua Trait `App\Traits\HasSeo`. Khóa duy nhất tổ hợp `(seoable_type, seoable_id, locale)`.

---

## NHÓM 10: SETTINGS - CẤU HÌNH

### TABLE: languages
- **id**: Primary key
- **code**: Mã ngôn ngữ (vi/en/ja...)
- **name**: Tên (Vietnamese, English...)
- **native_name**: Tên bản địa (Tiếng Việt, English...)
- **flag**: URL cờ quốc gia
- **is_default**: Ngôn ngữ mặc định
- **is_active**: Hoạt động
- **display_order**: Thứ tự hiển thị
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: settings
- **id**: Primary key
- **group**: Nhóm (general/email/payment...)
- **key**: Key (site_name, site_logo...)
- **value**: Giá trị
- **type**: Kiểu (text/textarea/number/boolean/image/json)
- **description**: Mô tả
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: email_templates
- **id**: Primary key
- **name**: Tên template (order_confirmation, welcome...)
- **subject**: Tiêu đề email
- **body**: Nội dung email
- **variables**: Biến sử dụng (JSON)
- **is_active**: Hoạt động
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

### TABLE: sms_templates
- **id**: Primary key
- **name**: Tên template
- **content**: Nội dung SMS
- **variables**: Biến sử dụng (JSON)
- **is_active**: Hoạt động
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật

---

## NHÓM 11: LOGS & TRACKING - NHẬT KÝ

### TABLE: activity_logs
- **id**: Primary key
- **log_name**: Tên log (auth/product/order...)
- **description**: Mô tả hoạt động
- **subject_type**: Model type bị tác động
- **subject_id**: Model ID bị tác động
- **causer_type**: Model type người thực hiện
- **causer_id**: Model ID người thực hiện
- **properties**: Chi tiết (JSON - old/new values)
- **created_at**: Ngày tạo

### TABLE: login_histories
- **id**: Primary key
- **user_id**: FK → users
- **ip_address**: Địa chỉ IP
- **user_agent**: User agent
- **device**: Thiết bị
- **browser**: Trình duyệt
- **platform**: Hệ điều hành
- **login_at**: Thời gian đăng nhập
- **logout_at**: Thời gian đăng xuất
- **created_at**: Ngày tạo

### TABLE: notifications
- **id**: UUID Primary key
- **type**: Loại notification
- **notifiable_type**: Model type nhận thông báo
- **notifiable_id**: Model ID nhận thông báo
- **data**: Dữ liệu (JSON)
- **read_at**: Thời gian đọc
- **created_at**: Ngày tạo
- **updated_at**: Ngày cập nhật
