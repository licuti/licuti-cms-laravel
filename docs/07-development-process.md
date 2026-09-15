# AGENT DEVELOPMENT GUIDE — LARAVEL E-COMMERCE CMS

- **Version**: 2.0
- **Framework**: Laravel 10+/11+
- **Architecture**: Service → Repository → Action → DTO

> Tài liệu này là kim chỉ nam BẮT BUỘC cho Agent khi phát triển bất kỳ module nào.
> Đọc toàn bộ trước khi bắt đầu viết code.

---

## PHẦN 1: LUỒNG THỰC THI CHUẨN (STRICT AGENT EXECUTION FLOW)

### 1.1 Nguyên tắc cốt lõi

Agent KHÔNG được bỏ qua bất kỳ bước nào, KHÔNG được viết class trước khi class phụ thuộc của nó tồn tại.

Sai thứ tự sẽ dẫn đến lỗi Dependency → phải sửa đi sửa lại.

### 1.2 Thứ tự thực thi BẮT BUỘC trong 1 module

```
BƯỚC 1 → Migration (Schema DB)
BƯỚC 2 → Eloquent Model
BƯỚC 3 → FormRequest (Validation rules)
BƯỚC 4 → DTO (Data Transfer Object)
BƯỚC 5 → Repository Interface → Repository Implementation → Bind vào ServiceProvider
BƯỚC 6 → Service (stub các hàm, chưa cần logic đầy đủ)
BƯỚC 7 → Controller (stub tất cả các method)
BƯỚC 8 → Route (đăng ký resource route + cập nhật sidebar)
BƯỚC 9 → View: index.blade.php (trang danh sách với dữ liệu thật)
BƯỚC 10 → View: form.blade.php (trang tạo/sửa với đủ fields)
--- DỪNG LẠI → Giao cho User kiểm tra UX/UI ---
BƯỚC 11 → Hoàn thiện logic chi tiết trong Service
BƯỚC 12 → Xử lý edge cases, cache, event
```

### 1.3 Quy tắc đặt tên (Naming Convention)

| Thành phần | Quy tắc | Ví dụ |
|---|---|---|
| Migration | `snake_case` timestamp prefix | `2024_01_01_000000_create_post_categories_table` |
| Model | `PascalCase` số ít | `PostCategory`, `Product` |
| DTO | `PascalCase` + DTO | `PostCategoryDTO`, `ProductDTO` |
| FormRequest | `[Action][Model]Request` | `StorePostCategoryRequest`, `UpdatePostRequest` |
| Repository Interface | `[Model]RepositoryInterface` | `PostCategoryRepositoryInterface` |
| Repository Class | `[Model]Repository` | `PostCategoryRepository` |
| Service | `[Model]Service` | `PostCategoryService`, `ProductService` |
| Controller | `Admin\[Model]Controller` | `Admin\PostCategoryController` |
| Action | `[Action][Noun]Action` | `PlaceOrderAction`, `GenerateVariantsAction` |
| View folder | `kebab-case` | `admin/post-categories/`, `admin/flash-sales/` |
| Route name | `kebab-case` với dot | `admin.post-categories.index` |

### 1.4 Quy tắc đặt tên hàm (Method Names)

| Lớp | Hàm chuẩn | Ghi chú |
|---|---|---|
| Repository | `getAll()`, `findById($id)`, `findByUuid($uuid)`, `create(array $data)`, `update($id, array $data)`, `delete($id)` | Kế thừa từ BaseRepository |
| Repository (custom) | `getActive()`, `getPaginated($perPage)`, `getBySlug($slug)`, `getTree()` | Định nghĩa thêm trong Interface |
| Service | `getList(array $filters)`, `create([Model]DTO $dto)`, `update($uuid, [Model]DTO $dto)`, `delete($uuid)` | Nhận DTO, trả Model |
| Controller | `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()` | 6 resource methods chuẩn |

---

## PHẦN 2: THỨ TỰ PHÁT TRIỂN MODULE (BUILD ORDER)

Module phải được phát triển theo thứ tự phụ thuộc từ thấp lên cao.
Module phía sau KHÔNG thể tồn tại nếu module phía trước chưa xong.

```
TẦNG 0 — CORE (Đã hoàn thiện)
├── users, roles, permissions (Auth & RBAC)
└── media (Upload file, thư viện ảnh)

TẦNG 1 — NỀN TẢNG (Foundation - Không phụ thuộc module nào)
├── languages   ✅ (Đã hoàn thiện)
└── settings    ✅ (Đã hoàn thiện)

TẦNG 2 — NỘI DUNG CMS (Phụ thuộc: media, languages)
├── post_categories + post_category_translations
├── posts + post_translations + tags + post_tag
├── pages + page_translations
├── banners + banner_translations
└── menus + menu_items

TẦNG 3 — DANH MỤC SẢN PHẨM (Phụ thuộc: media, languages)
├── product_categories + product_category_translations
├── brands + brand_translations
└── attributes + attribute_values

TẦNG 4 — SẢN PHẨM (Phụ thuộc: Tầng 3)
└── products + product_translations + product_variants + product_variant_attributes

TẦNG 5 — GIAO DỊCH (Phụ thuộc: Tầng 4 + users)
├── carts + cart_items
├── shipping_addresses
├── coupons + coupon_users
└── flash_sales + flash_sale_products

TẦNG 6 — ĐƠN HÀNG & THANH TOÁN (Phụ thuộc: Tầng 5)
├── orders + order_items + order_status_histories
├── payment_methods + payments + transactions
└── email_templates + sms_templates

TẦNG 7 — LOGGING (Phụ thuộc: Tất cả)
├── activity_logs
├── login_histories
└── notifications
```

---

## PHẦN 3: ĐẶC TẢ CHI TIẾT TỪNG MODULE

### 3.1 Nhóm Nền tảng (Foundation) — Ưu tiên cao nhất

---

#### MODULE: Post Categories (Danh mục bài viết)
**Phụ thuộc vào:** `media` (ảnh danh mục), `languages` (tên đa ngôn ngữ)
**Được dùng bởi:** `posts`, `menus` (trong tương lai)

**Bảng DB:**
- `post_categories`: `id`, `parent_id`, `image`, `is_active`, `display_order`
- `post_category_translations`: `id`, `category_id`, `locale`, `name`, `slug`, `description`

**Files cần tạo (theo thứ tự):**
```
database/migrations/..._create_post_categories_table.php
database/migrations/..._create_post_category_translations_table.php
app/Models/PostCategory.php
app/DTOs/PostCategory/PostCategoryDTO.php
app/Http/Requests/Admin/PostCategory/StorePostCategoryRequest.php
app/Http/Requests/Admin/PostCategory/UpdatePostCategoryRequest.php
app/Repositories/Interfaces/PostCategoryRepositoryInterface.php
app/Repositories/Eloquent/PostCategoryRepository.php
app/Services/Admin/PostCategory/PostCategoryService.php
app/Http/Controllers/Admin/PostCategoryController.php
resources/views/admin/post-categories/index.blade.php
resources/views/admin/post-categories/form.blade.php
```

**Hàm trong từng file:**
```php
// PostCategory Model
$fillable = ['parent_id', 'image', 'is_active', 'display_order']
$casts = ['is_active' => 'boolean']
parent()       // belongsTo(PostCategory)
children()     // hasMany(PostCategory)
translations() // hasMany(PostCategoryTranslation)
scopeActive()  // where is_active = true
getFlagUrlAttribute() // accessor cho ảnh

// PostCategoryDTO
fromRequest(StorePostCategoryRequest|UpdatePostCategoryRequest $request): self
toArray(): array

// PostCategoryRepositoryInterface
getTree(): Collection              // Lấy cây danh mục đa cấp
getActivePaginated(int $per): LengthAwarePaginator
getBySlug(string $slug): ?PostCategory
getAllForSelect(): Collection      // Dùng cho <select> ở form Posts

// PostCategoryService
getList(array $filters): LengthAwarePaginator
create(PostCategoryDTO $dto): PostCategory
update(string $uuid, PostCategoryDTO $dto): PostCategory
delete(string $uuid): bool

// PostCategoryController
index()   // Danh sách phân trang + filter
create()  // Form tạo mới
store()   // Lưu mới (dùng StorePostCategoryRequest → DTO → Service)
edit()    // Form sửa
update()  // Cập nhật
destroy() // Xóa (kiểm tra còn post con không)
```

**Form (HTML Fields):**
| Field | Component | Name attribute | Ghi chú |
|---|---|---|---|
| Tên danh mục (vi) | `<x-admin.input type="text">` | `translations[vi][name]` | Đa ngôn ngữ |
| Tên danh mục (en) | `<x-admin.input type="text">` | `translations[en][name]` | Đa ngôn ngữ |
| Slug | `<x-admin.input type="text">` | `translations[vi][slug]` | Auto-gen từ tên |
| Mô tả | `<textarea>` | `translations[vi][description]` | |
| Hình đại diện | `<x-admin.image-upload>` | `image_media_uuid` | MediaPicker |
| Danh mục cha | `<x-admin.select>` | `parent_id` | Options từ `getAllForSelect()` |
| Thứ tự hiển thị | `<x-admin.input type="number">` | `display_order` | Default 0 |
| Trạng thái | `<x-admin.toggle>` | `is_active` | Default true |

---

#### MODULE: Posts (Bài viết)
**Phụ thuộc vào:** `post_categories`, `media`, `languages`, `users` (author)
**Được dùng bởi:** Frontend blog, SEO sitemap, menus (trong tương lai)

**Bảng DB:**
- `posts`: `id`, `uuid`, `category_id`, `author_id`, `featured_image`, `status`, `is_featured`, `view_count`, `published_at`, `deleted_at`
- `post_translations`: `id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content`, `meta_title`, `meta_description`, `meta_keywords`
- `tags`: `id`, `name`, `slug`
- `post_tag`: `post_id`, `tag_id` (pivot)

**Files cần tạo (theo thứ tự):**
```
database/migrations/..._create_posts_table.php
database/migrations/..._create_post_translations_table.php
database/migrations/..._create_tags_table.php
database/migrations/..._create_post_tag_table.php
app/Models/Post.php
app/Models/Tag.php
app/DTOs/Post/PostDTO.php
app/Http/Requests/Admin/Post/StorePostRequest.php
app/Http/Requests/Admin/Post/UpdatePostRequest.php
app/Repositories/Interfaces/PostRepositoryInterface.php
app/Repositories/Eloquent/PostRepository.php
app/Services/Admin/Post/PostService.php
app/Http/Controllers/Admin/PostController.php
resources/views/admin/posts/index.blade.php
resources/views/admin/posts/form.blade.php
```

**Hàm trong từng file:**
```php
// Post Model
$fillable = ['uuid','category_id','author_id','featured_image','status','is_featured','view_count','published_at']
$casts = ['is_featured'=>'boolean', 'published_at'=>'datetime']
category()      // belongsTo(PostCategory)
author()        // belongsTo(User)
translations()  // hasMany(PostTranslation)
tags()          // belongsToMany(Tag, 'post_tag')
scopePublished()
scopeFeatured()

// PostDTO
fromRequest($request): self
toArray(): array
// Xử lý tags: tách chuỗi tag text → tìm hoặc tạo Tag records

// PostRepositoryInterface
getPaginated(array $filters, int $perPage): LengthAwarePaginator
findByUuid(string $uuid): Post
getByCategory(int $categoryId): Collection
getLatestPublished(int $limit): Collection
incrementViewCount(int $id): void

// PostService
getList(array $filters): LengthAwarePaginator
create(PostDTO $dto): Post
update(string $uuid, PostDTO $dto): Post
delete(string $uuid): bool
syncTags(Post $post, array $tagNames): void   // tìm/tạo Tag, rồi sync pivot

// PostController
index()            // filter theo category, status, search
create()
store()
edit()
update()
destroy()
bulkAction()       // bulk publish/draft/delete
```

**Form (HTML Fields):**
| Field | Component | Name | Ghi chú |
|---|---|---|---|
| Tiêu đề (vi/en) | `<x-admin.input type="text">` | `translations[vi][title]` | Required |
| Slug (vi/en) | `<x-admin.input type="text">` | `translations[vi][slug]` | Auto-gen |
| Tóm tắt | `<textarea rows="3">` | `translations[vi][excerpt]` | |
| Nội dung | Rich-text (TinyMCE) | `translations[vi][content]` | |
| Ảnh đại diện | `<x-admin.image-upload>` | `featured_image_media_uuid` | |
| Danh mục | `<x-admin.select>` | `category_id` | |
| Thẻ (Tags) | `<x-admin.select multiple>` | `tags[]` | |
| Nổi bật | `<x-admin.toggle>` | `is_featured` | |
| Trạng thái | `<x-admin.select>` | `status` | draft/published/scheduled |
| Ngày xuất bản | `<x-admin.input type="datetime-local">` | `published_at` | Hiện khi chọn scheduled |
| SEO Title | `<x-admin.input type="text">` | `translations[vi][meta_title]` | |
| SEO Description | `<textarea>` | `translations[vi][meta_description]` | |

---

#### MODULE: Pages (Trang tĩnh)
**Phụ thuộc vào:** `media`, `languages`
**Được dùng bởi:** menus, frontend routing

**Bảng DB:**
- `pages`: `id`, `uuid`, `template`, `is_active`
- `page_translations`: `id`, `page_id`, `locale`, `title`, `slug`, `content`, `meta_title`, `meta_description`

**Files cần tạo:**
```
app/Models/Page.php
app/DTOs/Page/PageDTO.php
app/Http/Requests/Admin/Page/StorePageRequest.php
app/Http/Requests/Admin/Page/UpdatePageRequest.php
app/Repositories/Interfaces/PageRepositoryInterface.php
app/Repositories/Eloquent/PageRepository.php
app/Services/Admin/Page/PageService.php
app/Http/Controllers/Admin/PageController.php
resources/views/admin/pages/index.blade.php
resources/views/admin/pages/form.blade.php
```

**Hàm trong từng file:**
```php
// PageRepositoryInterface
getBySlug(string $slug, string $locale): ?Page
getActivePaginated(int $perPage): LengthAwarePaginator

// PageService
create(PageDTO $dto): Page
update(string $uuid, PageDTO $dto): Page
delete(string $uuid): bool

// PageController
index(), create(), store(), edit(), update(), destroy()
```

**Form (HTML Fields):**
| Field | Component | Name |
|---|---|---|
| Tiêu đề (vi/en) | `<x-admin.input type="text">` | `translations[vi][title]` |
| Slug | `<x-admin.input type="text">` | `translations[vi][slug]` |
| Nội dung | Rich-text (TinyMCE) | `translations[vi][content]` |
| Template | `<x-admin.select>` | `template` (default/about/contact/landing) |
| SEO Title | `<x-admin.input type="text">` | `translations[vi][meta_title]` |
| SEO Description | `<textarea>` | `translations[vi][meta_description]` |
| Trạng thái | `<x-admin.toggle>` | `is_active` |

---

#### MODULE: Banners (Quảng cáo)
**Phụ thuộc vào:** `media`, `languages`
**Được dùng bởi:** Frontend layout (header slider, sidebar)

**Bảng DB:**
- `banners`: `id`, `uuid`, `position`, `link`, `display_order`, `start_date`, `end_date`, `is_active`
- `banner_translations`: `id`, `banner_id`, `locale`, `title`, `image`

**Files cần tạo:**
```
app/Models/Banner.php
app/DTOs/Banner/BannerDTO.php
app/Http/Requests/Admin/Banner/StoreBannerRequest.php
app/Http/Requests/Admin/Banner/UpdateBannerRequest.php
app/Repositories/Interfaces/BannerRepositoryInterface.php
app/Repositories/Eloquent/BannerRepository.php
app/Services/Admin/Banner/BannerService.php
app/Http/Controllers/Admin/BannerController.php
resources/views/admin/banners/index.blade.php
resources/views/admin/banners/form.blade.php
```

**Hàm trong từng file:**
```php
// BannerRepositoryInterface
getActivePaginated(int $perPage): LengthAwarePaginator
getActiveByPosition(string $position): Collection  // Dùng cho Frontend

// BannerService
create(BannerDTO $dto): Banner
update(string $uuid, BannerDTO $dto): Banner
delete(string $uuid): bool

// BannerController
index(), create(), store(), edit(), update(), destroy()
```

**Form (HTML Fields):**
| Field | Component | Name |
|---|---|---|
| Tiêu đề (vi/en) | `<x-admin.input type="text">` | `translations[vi][title]` |
| Hình ảnh | `<x-admin.image-upload>` | `translations[vi][image_media_uuid]` |
| Link đích khi click | `<x-admin.input type="text">` | `link` |
| Vị trí hiển thị | `<x-admin.select>` | `position` (home_slider/sidebar/header/footer) |
| Ngày bắt đầu | `<x-admin.input type="date">` | `start_date` |
| Ngày kết thúc | `<x-admin.input type="date">` | `end_date` |
| Thứ tự | `<x-admin.input type="number">` | `display_order` |
| Trạng thái | `<x-admin.toggle>` | `is_active` |

---

#### MODULE: Menus (Điều hướng)
**Phụ thuộc vào:** `pages`, `post_categories` (để gắn link nội dung)
**Được dùng bởi:** Frontend layout (header nav, footer nav)
> ⚠️ Xây dựng SAU khi Pages và Post Categories đã hoàn thiện.

**Bảng DB:**
- `menus`: `id`, `uuid`, `name`, `location`
- `menu_items`: `id`, `menu_id`, `parent_id`, `title`, `url`, `target`, `icon`, `display_order`

**Files cần tạo:**
```
app/Models/Menu.php
app/Models/MenuItem.php
app/DTOs/Menu/MenuDTO.php
app/DTOs/Menu/MenuItemDTO.php
app/Http/Requests/Admin/Menu/StoreMenuRequest.php
app/Http/Requests/Admin/Menu/UpdateMenuRequest.php
app/Repositories/Interfaces/MenuRepositoryInterface.php
app/Repositories/Eloquent/MenuRepository.php
app/Services/Admin/Menu/MenuService.php
app/Http/Controllers/Admin/MenuController.php
resources/views/admin/menus/index.blade.php
resources/views/admin/menus/edit.blade.php  ← đặc biệt: chỉ có Edit, không có Create/Show riêng
```

**Hàm trong từng file:**
```php
// MenuRepositoryInterface
getWithItems(string $uuid): Menu
getByLocation(string $location): ?Menu  // Dùng cho Frontend

// MenuService
create(MenuDTO $dto): Menu
updateItems(string $menuUuid, array $treeData): bool  // Lưu toàn bộ cây items từ drag & drop

// MenuController
index(), create(), store(), edit(), update(), destroy()
saveItems()  // POST endpoint nhận JSON cây items
```

**Form (HTML Fields):**
| Field | Component | Name |
|---|---|---|
| Tên menu | `<x-admin.input type="text">` | `name` |
| Vị trí | `<x-admin.select>` | `location` (header/footer/sidebar) |
| Items (kéo thả) | Nestable.js tree | JSON submit `items_json` |
| Thêm Item: Tiêu đề | `<x-admin.input type="text">` | `item[title]` |
| Thêm Item: Loại | `<x-admin.select>` | `item[type]` (custom/page/category) |
| Thêm Item: Target | `<x-admin.select>` | `item[target]` (_self/_blank) |

---

### 3.2 Nhóm Sản phẩm (Catalog)

---

#### MODULE: Product Categories & Brands
**Phụ thuộc vào:** `media`, `languages`
**Được dùng bởi:** `products`

> Cấu trúc tương tự Post Categories, chỉ thay prefix.
> Tên class: `ProductCategory`, `Brand`
> Repository: `getTree()`, `getAllForSelect()`, `getActiveBrands()`

**Form Product Category:** Giống Post Category (Tên, Slug, Mô tả, Ảnh, Danh mục cha, Thứ tự, Trạng thái)
**Form Brand:** Tên, Slug, Logo (`<x-admin.image-upload>`), Mô tả, Trạng thái.

---

#### MODULE: Products (Sản phẩm & Biến thể)
**Phụ thuộc vào:** `product_categories`, `brands`, `attributes`, `media`, `languages`
**Được dùng bởi:** `carts`, `orders`, `flash_sales`, `coupons`
> ⚠️ Module phức tạp nhất. Xây dựng CUỐI CÙNG trong nhóm Catalog.

**Bảng DB:**
- `products`: `id`, `uuid`, `category_id`, `brand_id`, `sku`, `price`, `compare_price`, `is_active`, `is_featured`
- `product_translations`: `id`, `product_id`, `locale`, `name`, `slug`, `short_description`, `description`, `meta_*`
- `product_attributes`: `id`, `name` (Màu sắc, Size...)
- `product_variants`: `id`, `product_id`, `sku`, `price`, `stock_quantity`, `is_active`
- `product_variant_attributes`: `variant_id`, `attribute_id`, `value`

**Hàm chính:**
```php
// ProductRepositoryInterface
getPaginated(array $filters, int $perPage): LengthAwarePaginator
findByUuid(string $uuid): Product
getWithVariants(string $uuid): Product
searchBySku(string $sku): ?ProductVariant
getOutOfStock(): Collection

// ProductService
create(ProductDTO $dto): Product
update(string $uuid, ProductDTO $dto): Product
delete(string $uuid): bool
generateVariants(Product $product, array $attributeMatrix): void
updateVariantStock(string $variantUuid, int $qty): bool
```

**Form (HTML Fields):**
| Field | Component | Name |
|---|---|---|
| Tên SP (vi/en) | `<x-admin.input type="text">` | `translations[vi][name]` |
| Slug | `<x-admin.input type="text">` | `translations[vi][slug]` |
| Mô tả ngắn | `<textarea>` | `translations[vi][short_description]` |
| Mô tả chi tiết | Rich-text (TinyMCE) | `translations[vi][description]` |
| Danh mục | `<x-admin.select>` | `category_id` |
| Thương hiệu | `<x-admin.select>` | `brand_id` |
| SKU gốc | `<x-admin.input type="text">` | `sku` |
| Giá bán | `<x-admin.input type="number">` | `price` |
| Giá gốc (gạch ngang) | `<x-admin.input type="number">` | `compare_price` |
| Tồn kho (nếu đơn giản) | `<x-admin.input type="number">` | `stock_quantity` |
| Thư viện ảnh | `<x-admin.image-upload multiple>` | `image_uuids[]` |
| Trọng lượng (gram) | `<x-admin.input type="number">` | `weight` |
| Kích thước | `<x-admin.input type="text">` | `dimensions` (D×R×C cm) |
| Nổi bật | `<x-admin.toggle>` | `is_featured` |
| Trạng thái | `<x-admin.toggle>` | `is_active` |
| Biến thể | Dynamic JS form | `variants[0][sku]`, `variants[0][price]`, ... |
| SEO | `<x-admin.input>`, `<textarea>` | `translations[vi][meta_*]` |

---

### 3.3 Nhóm Giao dịch (Sales & Orders)

---

#### MODULE: Cart (Giỏ hàng)
**Phụ thuộc vào:** `products`, `product_variants`, `users`
**Được dùng bởi:** `PlaceOrderAction`
> Module này không có giao diện Admin. Chỉ có Service + Repository dùng cho Frontend/API.

**Bảng DB:** `carts`, `cart_items`

**Hàm chính:**
```php
// CartService
getOrCreateCart(Request $request): Cart           // Lấy cart theo user_id hoặc session_id
addItem(Cart $cart, int $variantId, int $qty): CartItem
updateItemQty(int $cartItemId, int $qty): CartItem
removeItem(int $cartItemId): bool
clear(int $cartId): bool
mergeGuestToUser(string $sessionId, int $userId): void  // Gọi sau khi user login
calculateSummary(Cart $cart): array               // Trả về subtotal, item_count
```

---

#### MODULE: Orders (Đơn hàng)
**Phụ thuộc vào:** `carts`, `users`, `products`, `shipping_addresses`, `coupons`, `payment_methods`
**Được dùng bởi:** `payments`, `activity_logs`, email notifications

**Bảng DB:** `orders`, `order_items`, `order_status_histories`

**Hàm chính:**
```php
// OrderRepositoryInterface
getPaginated(array $filters, int $perPage): LengthAwarePaginator
findByOrderNumber(string $orderNumber): ?Order
getPendingOrders(): Collection
getRevenueByPeriod(Carbon $from, Carbon $to): float

// OrderService
updateStatus(string $orderUuid, string $status, ?string $note): Order
cancelOrder(string $orderUuid, string $reason): Order
printInvoice(string $orderUuid): string    // Trả về HTML hoặc PDF path

// PlaceOrderAction
execute(Cart $cart, User|null $user, CheckoutDTO $dto): Order
// Bên trong: validate stock → tính total → DB::transaction → create order → create items → decrement stock → clear cart → fire OrderPlaced event
```

**View Admin:**
- `admin/orders/index.blade.php` — Danh sách + filter (status, date, customer)
- `admin/orders/show.blade.php` — Chi tiết đơn hàng (không có form edit toàn bộ, chỉ cập nhật status)

---

#### MODULE: Coupons & Flash Sales (Khuyến mãi)
**Phụ thuộc vào:** `products`, `product_variants`, `orders`, `users`
**Được dùng bởi:** `PlaceOrderAction` (apply coupon), Frontend (hiển thị countdown)

**Bảng DB:** `coupons`, `coupon_users`, `flash_sales`, `flash_sale_products`

**Hàm chính:**
```php
// CouponService
validate(string $code, float $cartTotal, int $userId): CouponValidationResult
apply(string $code, Order $order): float             // Trả về discount_amount
deductUsage(int $couponId, int $userId, int $orderId): void

// FlashSaleService
getActiveSales(): Collection
getProductsOnSale(): Collection
getCurrentPriceForVariant(int $variantId): ?float    // Override giá nếu đang flash sale
```

**Form Coupon (HTML Fields):**
| Field | Component | Name |
|---|---|---|
| Mã coupon | `<x-admin.input type="text">` | `code` |
| Loại giảm | `<x-admin.select>` | `type` (percent/fixed) |
| Giá trị | `<x-admin.input type="number">` | `value` |
| Giá trị đơn tối thiểu | `<x-admin.input type="number">` | `min_order_value` |
| Giảm tối đa (nếu %) | `<x-admin.input type="number">` | `max_discount_amount` |
| Tổng lần dùng | `<x-admin.input type="number">` | `usage_limit` |
| Lần/user | `<x-admin.input type="number">` | `usage_per_user` |
| Ngày bắt đầu | `<x-admin.input type="date">` | `start_date` |
| Ngày kết thúc | `<x-admin.input type="date">` | `end_date` |
| Trạng thái | `<x-admin.toggle>` | `is_active` |

---

### 3.4 Nhóm Thanh toán (Payments)

#### MODULE: Payments (Cổng thanh toán)
**Phụ thuộc vào:** `orders`
**Được dùng bởi:** Frontend checkout, Webhook handler

**Hàm chính:**
```php
// PaymentGatewayInterface (Contract)
charge(Order $order, array $paymentData): PaymentResult
refund(Payment $payment, float $amount): RefundResult
handleWebhook(Request $request): void

// Implementations
VNPayGateway implements PaymentGatewayInterface
StripeGateway implements PaymentGatewayInterface
CODGateway implements PaymentGatewayInterface

// PaymentService
processPayment(string $orderUuid, string $gateway, array $data): PaymentResult
handleGatewayWebhook(string $gateway, Request $request): void
```

---

### 3.5 Nhóm Logging & Audit

#### MODULE: Activity Logs
**Phụ thuộc vào:** TẤT CẢ module
**Cách hoạt động:** Observer tự động, không cần gọi thủ công

```php
// Observers gắn vào các Model quan trọng
ProductObserver::created/updated/deleted → ghi vào activity_logs
OrderObserver::updated (status change) → ghi vào activity_logs
UserObserver::updated → ghi vào activity_logs

// ActivityLogService
log(string $logName, string $description, Model $subject, ?User $causer): void
```

---

## PHẦN 4: CHECKLIST SAU KHI HOÀN THÀNH 1 MODULE

Agent tự kiểm tra trước khi báo hoàn thành:

- [ ] Migration đã chạy thành công?
- [ ] Model có đủ `$fillable`, `$casts`, relations?
- [ ] Repository đã bind trong `RepositoryServiceProvider`?
- [ ] Route đã đăng ký và có tên chuẩn (`admin.xxx.index`)?
- [ ] Sidebar menu đã cập nhật?
- [ ] View `index.blade.php` có dữ liệu thật từ DB?
- [ ] View `form.blade.php` có đủ fields theo spec?
- [ ] Không có N+1 Query (dùng `with()` trong Repository)?
- [ ] Không hardcode text, đã dùng `__('key')` cho label?
- [ ] Eager loading cho translations đã có?
- [ ] Xử lý trường hợp xóa item có dữ liệu liên quan (cascade hoặc báo lỗi)?
