# PHẦN 1: CẤU TRÚC THƯ MỤC DỰ ÁN

> ⚠️ **TÀI LIỆU LEGACY (v1.0 — 2024).** Sơ đồ dưới đây mô tả cấu trúc *dự định ban đầu*,
> hiện **đã không còn chính xác**. Khi có xung đột, ưu tiên theo:
>
> 1. [`architecture/08-architecture-overview.md`](architecture/08-architecture-overview.md) — cấu trúc thực tế.
> 2. Cấu trúc thư mục trong [`README.md`](../README.md).
>
> Các điểm đã sai khác thực tế (đã sửa trong bản này, xem ghi chú `[FIXED]`):
> - Vị trí Repository implementation.
> - File route Admin.
> - Cấu trúc `resources/` (CSS/JS) và build tool.
> - Thư mục `resources/views/frontend/` và `lang/` **chưa tồn tại**.
>
> File này được giữ lại làm tham khảo sơ đồ tổng thể, sẽ được viết lại khi dự án hoàn thiện.

```text
laravel-project/
│
├── app/
│   │
│   ├── Console/
│   │   └── Commands/
│   │       ├── User/
│   │       │   └── SyncUserCommand.php
│   │       └── Order/
│   │           └── CleanExpiredOrderCommand.php
│   │
│   ├── Core/                                    # ✅ PHẦN TỰ PHÁT TRIỂN - CORE RIÊNG
│   │   ├── Traits/
│   │   │   ├── ApiResponse.php
│   │   │   ├── Uploadable.php
│   │   │   └── Cacheable.php
│   │   │
│   │   ├── Helpers/
│   │   │   ├── helpers.php                      # Global helpers
│   │   │   ├── StringHelper.php
│   │   │   ├── DateHelper.php
│   │   │   └── NumberHelper.php
│   │   │
│   │   ├── Constants/
│   │   │   ├── AppConstant.php
│   │   │   ├── CacheKey.php
│   │   │   └── PermissionConstant.php
│   │   │
│   │   ├── Enums/
│   │   │   ├── UserStatus.php
│   │   │   ├── OrderStatus.php
│   │   │   ├── PaymentStatus.php
│   │   │   └── PaymentMethod.php
│   │   │
│   │   └── Base/                                # Base classes cho toàn dự án
│   │       ├── BaseController.php
│   │       ├── BaseService.php
│   │       ├── BaseRepository.php
│   │       └── BaseRequest.php
│   │
│   ├── Exceptions/
│   │   ├── Handler.php                          # Laravel default
│   │   ├── BusinessException.php
│   │   ├── User/
│   │   │   ├── UserNotFoundException.php
│   │   │   └── UserPermissionDeniedException.php
│   │   └── Order/
│   │       └── InsufficientStockException.php
│   │
│   ├── Http/
│   │   │
│   │   ├── Controllers/
│   │   │   │
│   │   │   ├── Frontend/                        # ✅ KHU VỰC FRONTEND
│   │   │   │   ├── HomeController.php
│   │   │   │   ├── AboutController.php
│   │   │   │   ├── ContactController.php
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── LoginController.php
│   │   │   │   │   ├── RegisterController.php
│   │   │   │   │   └── ForgotPasswordController.php
│   │   │   │   ├── User/
│   │   │   │   │   ├── ProfileController.php
│   │   │   │   │   ├── SettingController.php
│   │   │   │   │   └── OrderController.php
│   │   │   │   ├── Product/
│   │   │   │   │   ├── ProductController.php
│   │   │   │   │   └── CategoryController.php
│   │   │   │   ├── Cart/
│   │   │   │   │   └── CartController.php
│   │   │   │   └── Checkout/
│   │   │   │       └── CheckoutController.php
│   │   │   │
│   │   │   ├── Admin/                           # ✅ KHU VỰC ADMIN
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── Auth/
│   │   │   │   │   └── AdminLoginController.php
│   │   │   │   ├── User/
│   │   │   │   │   ├── UserController.php
│   │   │   │   │   └── RoleController.php
│   │   │   │   ├── Product/
│   │   │   │   │   ├── ProductController.php
│   │   │   │   │   ├── CategoryController.php
│   │   │   │   │   └── BrandController.php
│   │   │   │   ├── Order/
│   │   │   │   │   ├── OrderController.php
│   │   │   │   │   └── OrderReportController.php
│   │   │   │   ├── Blog/
│   │   │   │   │   ├── PostController.php
│   │   │   │   │   └── CategoryController.php
│   │   │   │   ├── Setting/
│   │   │   │   │   ├── GeneralSettingController.php
│   │   │   │   │   ├── PaymentSettingController.php
│   │   │   │   │   └── EmailSettingController.php
│   │   │   │   └── Media/
│   │   │   │       └── MediaController.php
│   │   │   │
│   │   │   └── Api/                             # ✅ KHU VỰC API
│   │   │       │
│   │   │       ├── V1/                          # API Version 1
│   │   │       │   ├── Auth/
│   │   │       │   │   ├── AuthController.php
│   │   │       │   │   └── VerificationController.php
│   │   │       │   ├── User/
│   │   │       │   │   ├── UserController.php
│   │   │       │   │   └── ProfileController.php
│   │   │       │   ├── Product/
│   │   │       │   │   ├── ProductController.php
│   │   │       │   │   └── CategoryController.php
│   │   │       │   ├── Order/
│   │   │       │   │   └── OrderController.php
│   │   │       │   └── Cart/
│   │   │       │       └── CartController.php
│   │   │       │
│   │   │       └── V2/                          # API Version 2 (nếu cần)
│   │   │           └── User/
│   │   │               └── UserController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php                 # Laravel default
│   │   │   ├── RedirectIfAuthenticated.php
│   │   │   ├── AdminAuthenticate.php            # ✅ Custom cho admin
│   │   │   ├── CheckPermission.php
│   │   │   ├── CheckRole.php
│   │   │   ├── ForceJsonResponse.php            # ✅ Cho API
│   │   │   ├── SetLocale.php
│   │   │   └── LogActivity.php
│   │   │
│   │   ├── Requests/
│   │   │   │
│   │   │   ├── Frontend/                        # ✅ Request cho Frontend
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── LoginRequest.php
│   │   │   │   │   └── RegisterRequest.php
│   │   │   │   ├── Profile/
│   │   │   │   │   └── UpdateProfileRequest.php
│   │   │   │   └── Order/
│   │   │   │       └── CreateOrderRequest.php
│   │   │   │
│   │   │   ├── Admin/                           # ✅ Request cho Admin
│   │   │   │   ├── User/
│   │   │   │   │   ├── StoreUserRequest.php
│   │   │   │   │   └── UpdateUserRequest.php
│   │   │   │   ├── Product/
│   │   │   │   │   ├── StoreProductRequest.php
│   │   │   │   │   └── UpdateProductRequest.php
│   │   │   │   └── Setting/
│   │   │   │       └── UpdateSettingRequest.php
│   │   │   │
│   │   │   └── Api/                             # ✅ Request cho API
│   │   │       └── V1/
│   │   │           ├── Auth/
│   │   │           │   └── LoginRequest.php
│   │   │           └── User/
│   │   │               ├── StoreUserRequest.php
│   │   │               └── UpdateUserRequest.php
│   │   │
│   │   └── Resources/                           # ✅ API Resources (Transformer)
│   │       ├── User/
│   │       │   ├── UserResource.php
│   │       │   └── UserCollection.php
│   │       ├── Product/
│   │       │   ├── ProductResource.php
│   │       │   └── ProductCollection.php
│   │       └── Order/
│   │           ├── OrderResource.php
│   │           └── OrderCollection.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Brand.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Cart.php
│   │   ├── CartItem.php
│   │   ├── Post.php
│   │   ├── Setting.php
│   │   ├── Media.php
│   │   │
│   │   ├── Traits/                              # Model Traits
│   │   │   ├── HasUuid.php
│   │   │   ├── Sluggable.php
│   │   │   ├── Filterable.php
│   │   │   └── SoftDeletesWithTrashed.php
│   │   │
│   │   └── Scopes/                              # Query Scopes
│   │       ├── ActiveScope.php
│   │       └── PublishedScope.php
│   │
│   ├── Services/                                # ✅ BUSINESS LOGIC LAYER
│   │   │
│   │   ├── Frontend/                            # Services riêng cho Frontend
│   │   │   ├── Auth/
│   │   │   │   └── AuthService.php
│   │   │   ├── Product/
│   │   │   │   └── ProductService.php
│   │   │   ├── Cart/
│   │   │   │   └── CartService.php
│   │   │   └── Order/
│   │   │       └── OrderService.php
│   │   │
│   │   ├── Admin/                               # Services riêng cho Admin
│   │   │   ├── User/
│   │   │   │   ├── UserService.php
│   │   │   │   └── RoleService.php
│   │   │   ├── Product/
│   │   │   │   ├── ProductService.php
│   │   │   │   └── CategoryService.php
│   │   │   ├── Order/
│   │   │   │   ├── OrderService.php
│   │   │   │   └── OrderReportService.php
│   │   │   └── Setting/
│   │   │       └── SettingService.php
│   │   │
│   │   ├── Shared/                              # ✅ Services dùng chung
│   │   │   ├── Upload/
│   │   │   │   └── UploadService.php
│   │   │   ├── Notification/
│   │   │   │   ├── EmailService.php
│   │   │   │   └── SmsService.php
│   │   │   ├── Payment/
│   │   │   │   ├── PaymentService.php
│   │   │   │   └── Gateways/
│   │   │   │       ├── PaymentGatewayInterface.php
│   │   │   │       ├── StripeGateway.php
│   │   │   │       ├── VNPayGateway.php
│   │   │   │       └── PayPalGateway.php
│   │   │   └── External/
│   │   │       ├── GoogleMapService.php
│   │   │       └── SocialLoginService.php
│   │   │
│   │   └── Api/                                 # Services riêng cho API (nếu khác)
│   │       └── V1/
│   │           └── AuthService.php
│   │
│   ├── Repositories/                            # ✅ DATA ACCESS LAYER
│   │   ├── Interfaces/
│   │   │   ├── BaseRepositoryInterface.php
│   │   │   ├── UserRepositoryInterface.php
│   │   │   ├── ProductRepositoryInterface.php
│   │   │   ├── OrderRepositoryInterface.php
│   │   │   └── CategoryRepositoryInterface.php
│   │   │
│   │   ├── BaseRepository.php
│   │   │
│   │   └── Eloquent/                               # [FIXED] TOÀN BỘ implementation nằm tại đây
│   │       ├── UserRepository.php                  #    (cả Tầng 0/1: User, Role, Media,
│   │       ├── RoleRepository.php                  #     MediaFolder, Language, Setting)
│   │       ├── MediaRepository.php
│   │       ├── MediaFolderRepository.php
│   │       ├── LanguageRepository.php
│   │       ├── SettingRepository.php
│   │       ├── PostRepository.php
│   │       ├── PageRepository.php
│   │       ├── ProductRepository.php
│   │       ├── OrderRepository.php
│   │       └── CategoryRepository.php
│   │
│   ├── DTOs/                                    # ✅ Data Transfer Objects
│   │   ├── User/
│   │   │   ├── CreateUserDTO.php
│   │   │   └── UpdateUserDTO.php
│   │   ├── Product/
│   │   │   ├── CreateProductDTO.php
│   │   │   └── UpdateProductDTO.php
│   │   └── Order/
│   │       ├── CreateOrderDTO.php
│   │       └── OrderItemDTO.php
│   │
│   ├── Actions/                                 # ✅ Single Action Classes
│   │   ├── User/
│   │   │   ├── CreateUserAction.php
│   │   │   ├── UpdateUserAction.php
│   │   │   └── DeleteUserAction.php
│   │   └── Order/
│   │       ├── PlaceOrderAction.php
│   │       ├── CancelOrderAction.php
│   │       └── RefundOrderAction.php
│   │
│   ├── Events/                                  # ✅ Events
│   │   ├── User/
│   │   │   ├── UserRegistered.php
│   │   │   ├── UserUpdated.php
│   │   │   └── UserDeleted.php
│   │   └── Order/
│   │       ├── OrderPlaced.php
│   │       ├── OrderShipped.php
│   │       └── OrderCompleted.php
│   │
│   ├── Listeners/                               # ✅ Event Listeners
│   │   ├── User/
│   │   │   ├── SendWelcomeEmail.php
│   │   │   └── CreateDefaultSettings.php
│   │   └── Order/
│   │       ├── SendOrderConfirmation.php
│   │       └── NotifyAdminNewOrder.php
│   │
│   ├── Jobs/                                    # ✅ Queue Jobs
│   │   ├── SendEmailJob.php
│   │   ├── ProcessPaymentJob.php
│   │   ├── GenerateInvoiceJob.php
│   │   └── SyncInventoryJob.php
│   │
│   ├── Mail/                                    # ✅ Mailables
│   │   ├── User/
│   │   │   ├── WelcomeMail.php
│   │   │   └── ResetPasswordMail.php
│   │   └── Order/
│   │       ├── OrderConfirmationMail.php
│   │       └── OrderShippedMail.php
│   │
│   ├── Notifications/                           # ✅ Notifications
│   │   ├── User/
│   │   │   └── VerifyEmailNotification.php
│   │   └── Order/
│   │       └── OrderStatusNotification.php
│   │
│   ├── Observers/                               # ✅ Model Observers
│   │   ├── UserObserver.php
│   │   ├── ProductObserver.php
│   │   └── OrderObserver.php
│   │
│   ├── Policies/                                # ✅ Authorization Policies
│   │   ├── UserPolicy.php
│   │   ├── ProductPolicy.php
│   │   └── OrderPolicy.php
│   │
│   ├── Rules/                                   # ✅ Custom Validation Rules
│   │   ├── PhoneNumber.php
│   │   ├── StrongPassword.php
│   │   └── UniqueSlug.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   ├── RepositoryServiceProvider.php        # ✅ Binding Repository
│   │   └── ViewServiceProvider.php
│   │
│   │   # [FIXED] Component là ANONYMOUS Blade component (.blade.php), nằm trong
│   │   # resources/views/components/admin/ — KHÔNG có class PHP tại app/View/Components.
│   │   # Xem danh mục đầy đủ: architecture/13-ui-conventions.md
│   │
├── bootstrap/
│   ├── app.php
│   └── cache/
│
├── config/                                      # Configuration files
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php                             # ✅ Third-party services
│   └── permission.php                           # ✅ Custom config
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── ProductFactory.php
│   │   └── OrderFactory.php
│   │
│   ├── migrations/
│   │   ├── 2024_01_01_000000_create_users_table.php
│   │   ├── 2024_01_02_000000_create_products_table.php
│   │   ├── 2024_01_03_000000_create_orders_table.php
│   │   └── ...
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── RoleSeeder.php
│       ├── PermissionSeeder.php
│       ├── ProductSeeder.php
│       └── SettingSeeder.php
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── robots.txt
│   │
│   ├── assets/                                  # [FIXED] KHÔNG dùng — Vite xuất thẳng vào public/build/
│   │
│   └── uploads/                                 # ✅ User uploaded files
│       ├── products/
│       ├── users/
│       └── posts/
│
├── resources/                                    # [FIXED] Build tool là Vite, không tách frontend/admin
│   │
│   ├── css/
│   │   └── app.scss                              # ✅ SCSS duy nhất, build qua Vite (resources/css/app.scss)
│   │
│   ├── js/
│   │   ├── app.js                                # ✅ Entry duyệt: import bootstrap, jQuery, SweetAlert2
│   │   ├── bootstrap.js
│   │   └── admin/                                # JS riêng của Admin (require qua resources/js/app.js)
│   │       ├── ui-bridge.js
│   │       └── table-utils.js
│   │
│   ├── views/
│   │   │
│   │   ├── admin/                                # ✅ ADMIN VIEWS (xem 08-architecture-overview.md)
│   │   │   ├── {module}/
│   │   │   │   ├── index.blade.php
│   │   │   │   └── form.blade.php                # Dùng chung cho create + edit
│   │   │   └── auth/
│   │   │       └── login.blade.php
│   │   │
│   │   ├── components/admin/                     # ✅ Blade Component chuẩn (xem 13-ui-conventions.md)
│   │   │   ├── card.blade.php
│   │   │   ├── table.blade.php
│   │   │   └── ...
│   │   │
│   │   ├── layouts/
│   │   │   ├── admin.blade.php
│   │   │   └── auth.blade.php
│   │   │
│   │   └── welcome.blade.php
│   │
│   │   # ⚠️ resources/views/frontend/ CHƯA tồn tại — Frontend chưa xây dựng
│   │
│   └── lang/                                    # ⚠️ CHƯA tồn tại — i18n hiện dùng bảng `languages` trong DB
│       └── (vi/, en/)                           #    + trans()/__() với key viết trực tiếp trong view
│
├── routes/                                      # [FIXED] Chỉ có 3 file, KHÔNG có admin.php
│   ├── web.php                                  # ✅ Toàn bộ route Admin (prefix /admin + middleware auth, admin)
│   ├── api.php                                  # ✅ API routes (prefix /api/v1)
│   └── console.php                              # Console routes
│
├── storage/
│   ├── app/
│   │   ├── private/
│   │   └── public/
│   │       └── uploads/                         # Symlink to public/uploads
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
│       └── laravel.log
│
├── tests/
│   ├── Feature/
│   │   ├── Frontend/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginTest.php
│   │   │   │   └── RegisterTest.php
│   │   │   └── Product/
│   │   │       └── ProductTest.php
│   │   │
│   │   ├── Admin/
│   │   │   ├── Auth/
│   │   │   │   └── AdminLoginTest.php
│   │   │   ├── User/
│   │   │   │   └── UserCrudTest.php
│   │   │   └── Product/
│   │   │       └── ProductCrudTest.php
│   │   │
│   │   └── Api/
│   │       └── V1/
│   │           ├── Auth/
│   │           │   └── AuthTest.php
│   │           └── User/
│   │               └── UserTest.php
│   │
│   ├── Unit/
│   │   ├── Services/
│   │   │   ├── UserServiceTest.php
│   │   │   └── ProductServiceTest.php
│   │   ├── Repositories/
│   │   │   └── UserRepositoryTest.php
│   │   └── Models/
│   │       └── UserTest.php
│   │
│   ├── TestCase.php
│   └── CreatesApplication.php
│
├── vendor/                                      # ⚠️ KHÔNG BAO GIỜ SỬA CODE Ở ĐÂY
│   └── ...
│
├── .env                                         # Environment variables
├── .env.example
├── .gitignore
├── artisan                                      # Artisan CLI
├── composer.json                                # PHP dependencies
├── composer.lock
├── package.json                                 # Node dependencies
├── package-lock.json
├── phpunit.xml                                  # PHPUnit config
├── vite.config.js                               # Vite config
└── README.md
```
