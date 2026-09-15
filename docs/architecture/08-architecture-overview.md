# 08 — Kiến trúc tổng quan & Cấu trúc thư mục

> **Mô tả chính xác** cấu trúc thực tế của Licuti CMS. Mọi module mới **PHẢI** tuân thủ.

---

## 1. KIẾN TRÚC TỔNG QUAN

Dự án áp dụng mô hình **Layered Architecture: Service - Repository - DTO**. Mỗi lớp chỉ được biết và giao tiếp với lớp ngay bên dưới nó.

```
HTTP Request
    │
    ▼
[FormRequest]  ← Validate + Authorize + Sanitize dữ liệu (prepareForValidation)
    │
    ▼
[Controller]   ← Nhận Request sạch, chuyển qua DTO, gọi Service, trả View / Redirect
    │
    ▼
[DTO]          ← Đóng gói dữ liệu kiểu mạnh, bất biến (readonly), bóc tách data & translations
    │
    ▼
[Service]      ← Toàn bộ business logic, transaction, bảo vệ nghiệp vụ, sinh slug, gán quan hệ
    │
    ▼
[Repository]   ← Toàn bộ query DB, filter, eager loading, phân trang, cache (qua Interface)
    │
    ▼
[Model]        ← Định nghĩa bảng, cột ($fillable, $casts), relations, scopes, traits (HasUuid, HasSeo)
```

### Nguyên tắc vàng

1. **Controller:** KHÔNG chứa query DB trực tiếp, KHÔNG chứa if/else logic nghiệp vụ phức tạp.
2. **Service:** KHÔNG phụ thuộc vào HTTP Request / Response; chỉ nhận DTO hoặc tham số nguyên thủy.
3. **Repository:** KHÔNG chứa business logic (chỉ query và trả về Collection/Model/Paginator).
4. **Định danh an toàn:** Dùng `uuid` làm định danh công khai ra ngoài route, API, URL. **KHÔNG lộ `id` tự tăng.**
5. **Giao diện:** Chuẩn giao diện là **Bootstrap 5.3 + SCSS**. Tuyệt đối KHÔNG dùng Tailwind CSS.

---

## 2. CẤU TRÚC THƯ MỤC

```
app/
├── Core/
│   ├── Base/
│   │   ├── BaseController.php         ← Controller cơ sở (chứa ApiResponse)
│   │   ├── BaseRequest.php            ← DÀNH RIÊNG CHO API (ném JSON khi validate fail)
│   │   └── BaseService.php            ← Base cho mọi Service (handleTransaction, generateUniqueSlug...)
│   ├── BulkAction/
│   │   └── BulkActionRegistry.php     ← Quản lý tập trung các bulk action của hệ thống
│   ├── Enums/                         ← Enum PHP 8.1+ (ContentStatus, UserStatus, OrderStatus...)
│   └── Traits/                        ← Trait dùng chung (ApiResponse, HasUuid, Sluggable...)
│
├── DTOs/
│   └── {Module}/                      ← Chứa DTO của module (VD: PostDTO.php, CategoryDTO.php)
│
├── Exceptions/                        ← Custom Exception nghiệp vụ
│
├── Http/
│   ├── Controllers/Admin/             ← Controller Admin Panel (PostController, CategoryController...)
│   ├── Requests/Admin/{Module}/       ← FormRequest Admin (kế thừa FormRequest để redirect khi fail)
│   └── Resources/                     ← API Resources khi trả JSON
│
├── Models/                            ← Eloquent Models & {Model}Translation.php
│   └── Traits/                        ← HasUuid.php, HasSeo.php...
│
├── Providers/
│   ├── BulkActionServiceProvider.php  ← Đăng ký bulk action cho từng module
│   └── RepositoryServiceProvider.php  ← Bind RepositoryInterface ↔ Repository
│
├── Repositories/
│   ├── BaseRepository.php             ← CRUD cơ sở
│   ├── Interfaces/                    ← BaseRepositoryInterface & {Module}RepositoryInterface
│   └── Eloquent/                      ← Tất cả Repository cụ thể đặt tại đây (Xem ghi chú bên dưới)
│
└── Services/
    └── Admin/{Module}/
        └── {Module}Service.php        ← Logic nghiệp vụ module

resources/
├── js/admin/
│   └── table-utils.js                 ← Xử lý global: check-all, bulk apply, form-confirm (SweetAlert2)
├── scss/admin/                        ← SCSS giao diện Admin
└── views/
    ├── admin/{module}/
    │   ├── index.blade.php            ← Trang danh sách (Table, Search, Filter, Bulk)
    │   └── form.blade.php             ← Dùng CHUNG cho cả Create và Edit
    └── components/admin/              ← Blade Components tái sử dụng (chuẩn Bootstrap 5.3)
```

> **Ghi chú quan trọng về vị trí Repository:**
>
> Tất cả Repository implementation đặt tại `app/Repositories/Eloquent/{Module}Repository.php` (không phải `app/Repositories/{Module}Repository.php`). Quy ước này đã được chốt trong `RepositoryServiceProvider.php`. Khi tạo Repository mới, binding phải trỏ đúng namespace:
>
> ```php
> $this->app->bind(
>     \App\Repositories\Interfaces\ArticleRepositoryInterface::class,
>     \App\Repositories\Eloquent\ArticleRepository::class
> );
> ```