# TIÊU CHUẨN CODE DỰ ÁN LARAVEL E-COMMERCE CMS

> Tài liệu này được đọc và tuân thủ bởi tất cả các AI Agent trong mọi phiên làm việc.
> Mọi code được tạo ra hoặc chỉnh sửa đều PHẢI tuân theo các quy tắc dưới đây.

---

## 1. KIẾN TRÚC & PHÂN LỚP (Architecture & Layering)

Dự án áp dụng mô hình **Service - Repository - Action - DTO**. Mỗi lớp chỉ được biết và gọi lớp ngay bên dưới nó.

```
Controller → Service → Repository → Model
Controller → Action  → Service    → Repository
```

### Quy tắc từng lớp:

| Lớp | Trách nhiệm | KHÔNG được làm |
|---|---|---|
| **Controller** | Nhận request, gọi Service/Action, trả response | Chứa business logic, query DB trực tiếp |
| **FormRequest** | Validate & authorize request | Chứa logic nghiệp vụ |
| **Service** | Business logic, điều phối các tác vụ | Query DB trực tiếp (phải qua Repository) |
| **Action** | Thực hiện một hành động đơn lẻ, có thể reuse | Chứa nhiều hơn 1 mục tiêu |
| **Repository** | Truy vấn CSDL, trả về data | Chứa business logic |
| **Model** | Định nghĩa quan hệ, scope, cast | Chứa business logic |
| **DTO** | Truyền data giữa các lớp theo kiểu mạnh | Chứa logic |

---

## 2. NGUYÊN TẮC CLEAN CODE

### 2.1 Đặt tên (Naming Convention)

- **Class**: `PascalCase` → `ProductService`, `PlaceOrderAction`
- **Method / Function**: `camelCase` → `getActiveProducts()`, `placeOrder()`
- **Variable**: `camelCase` → `$orderTotal`, `$userId`
- **Constant / Enum**: `UPPER_SNAKE_CASE` → `ORDER_STATUS_PENDING`
- **Database column**: `snake_case` → `order_number`, `created_at`
- **Route name**: `kebab-case` với dấu chấm phân cấp → `admin.products.index`
- **Tên phải diễn đạt ý nghĩa**, tuyệt đối không dùng tên chung chung như `$data`, `$arr`, `$temp`, `$val`.

```php
// ❌ SAI
$d = Product::where('s', 1)->get();

// ✅ ĐÚNG
$activeProducts = $this->productRepository->getActiveProducts();
```

### 2.2 Hàm / Method

- Mỗi hàm **chỉ làm một việc** (Single Responsibility).
- Độ dài hàm **tối đa 20-30 dòng**. Nếu dài hơn, hãy tách hàm.
- **Số lượng tham số** tối đa là 3. Nếu nhiều hơn, đóng gói vào DTO hoặc array.
- Tránh **side effects** không rõ ràng.
- Sử dụng **Early Return** thay vì nested `if/else` để giảm độ phức tạp.

```php
// ❌ SAI - nested if, làm nhiều việc
public function processOrder($userId, $productId, $qty, $coupon, $address) {
    if ($userId) {
        if ($productId) {
            // ... 50 dòng logic ...
        }
    }
}

// ✅ ĐÚNG - Early Return + DTO
public function processOrder(CreateOrderDTO $dto): Order
{
    if (!$this->inventoryService->isAvailable($dto->productId, $dto->quantity)) {
        throw new InsufficientStockException();
    }

    return $this->placeOrderAction->execute($dto);
}
```

### 2.3 Tránh Magic Numbers / Strings

Luôn dùng **Enum** hoặc **Constant** thay vì giá trị cứng (hardcode).

```php
// ❌ SAI
if ($order->status === 'cancelled') { ... }
if ($user->role === 1) { ... }

// ✅ ĐÚNG
if ($order->status === OrderStatus::CANCELLED) { ... }
if ($user->hasRole(Role::ADMIN)) { ... }
```

---

## 3. NGUYÊN TẮC SOLID

| Nguyên tắc | Áp dụng trong dự án |
|---|---|
| **S** - Single Responsibility | Mỗi class/method chỉ một nhiệm vụ. `PlaceOrderAction` chỉ lo đặt hàng. |
| **O** - Open/Closed | Mở rộng qua Interface. Thêm cổng thanh toán mới không sửa code cũ. |
| **L** - Liskov Substitution | Các Gateway (`VNPayGateway`, `StripeGateway`) thay thế được cho nhau qua `PaymentGatewayInterface`. |
| **I** - Interface Segregation | Tách `BaseRepositoryInterface` ra các interface nhỏ theo module. |
| **D** - Dependency Inversion | Inject Interface, không inject Class cụ thể. Bind qua `RepositoryServiceProvider`. |

```php
// ✅ Dependency Inversion - Inject Interface, không inject Class cụ thể
public function __construct(
    private readonly ProductRepositoryInterface $productRepository,
    private readonly PaymentGatewayInterface $paymentGateway,
) {}
```

---

## 4. TIÊU CHUẨN LARAVEL CỤ THỂ

### 4.1 Eloquent & Database

- **Luôn dùng Eager Loading** để tránh N+1 problem. Dùng `with()` khi biết trước quan hệ cần dùng.
- **Luôn dùng Chunk** hoặc **Cursor** khi xử lý số lượng bản ghi lớn (> 1000).
- **Luôn dùng Database Transaction** cho các tác vụ ghi nhiều bảng liên quan.
- **Không dùng `DB::statement` hoặc raw query** trừ khi bắt buộc và phải có comment giải thích.
- **Sử dụng Query Scope** cho các điều kiện query tái sử dụng.

```php
// ❌ SAI - N+1 problem
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name; // Query N lần
}

// ✅ ĐÚNG
$orders = Order::with(['user', 'items.product'])->paginate(20);

// ✅ Transaction cho tác vụ phức tạp
DB::transaction(function () use ($dto) {
    $order = $this->orderRepository->create($dto->toArray());
    $this->inventoryService->decreaseStock($dto->items);
    $this->cartService->clear($dto->userId);
});
```

### 4.2 API Response

Mọi API response đều dùng `ApiResponse` Trait với cấu trúc chuẩn:

```json
// Success
{
    "success": true,
    "message": "Thao tác thành công",
    "data": { ... }
}

// Error
{
    "success": false,
    "message": "Mô tả lỗi",
    "errors": { ... }
}
```

### 4.3 Xử lý Exception

- **Không dùng `try/catch` để nuốt lỗi** mà không log.
- **Tạo Custom Exception** cho từng loại lỗi nghiệp vụ cụ thể.
- **Xử lý tập trung** tại `app/Exceptions/Handler.php`.

```php
// ❌ SAI - nuốt lỗi
try {
    $this->paymentService->charge($order);
} catch (\Exception $e) {
    return false;
}

// ✅ ĐÚNG - Custom Exception rõ ràng
try {
    $this->paymentService->charge($order);
} catch (PaymentFailedException $e) {
    Log::error('Payment failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
    throw $e; // Để Handler xử lý và trả response phù hợp
}
```

### 4.4 Cache

- Mọi query nặng hoặc dữ liệu ít thay đổi đều **phải cache**.
- Dùng **Cache Tags** để dễ xóa nhóm cache liên quan.
- Tên cache key phải dùng hằng số từ `CacheKey` constant class.

```php
// ✅ Cache với tag và key chuẩn
$categories = Cache::tags(['categories'])->remember(
    CacheKey::CATEGORY_TREE,
    now()->addHours(24),
    fn() => $this->categoryRepository->getTree()
);

// Xóa cache khi update
Cache::tags(['categories'])->flush();
```

### 4.5 Validation

- Luôn dùng **FormRequest** riêng biệt cho mỗi action (Store/Update tách nhau).
- Validation message phải **đa ngôn ngữ** qua file `lang/`.
- Thêm `authorize()` để kiểm tra quyền ngay tại tầng Request.

```php
// ✅ FormRequest chuẩn
class StoreProductRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.create');
    }

    public function rules(): array
    {
        return [
            'sku'   => ['required', 'string', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
```

---

## 5. TIÊU CHUẨN KHẢ NĂNG MỞ RỘNG (Scalability)

### 5.1 Đa ngôn ngữ (i18n)

- **Mọi text hiển thị** cho người dùng đều phải qua helper `__()` hoặc `trans()`.
- Cấu trúc dữ liệu đa ngôn ngữ: Bảng cha lưu data chung, bảng `_translations` lưu nội dung theo ngôn ngữ.
- **Không hardcode ngôn ngữ** vào query (`locale = 'vi'`), luôn lấy từ `app()->getLocale()`.

### 5.2 Queue & Jobs

- **Mọi tác vụ tốn thời gian** (gửi email, SMS, generate PDF, sync dữ liệu) đều phải đưa vào Queue.
- Đặt Job vào đúng Queue name theo mức độ ưu tiên: `critical`, `default`, `low`.

```php
// ✅ Dispatch job vào queue
SendOrderConfirmationJob::dispatch($order)
    ->onQueue('default')
    ->delay(now()->addSeconds(5));
```

### 5.3 Event-Driven

- Các tác vụ phát sinh từ sự kiện (sau khi tạo user → gửi email chào mừng) phải dùng **Event & Listener**, không gọi trực tiếp trong Service.
- Điều này giúp dễ dàng thêm/bớt hành vi mà không sửa code hiện có.

```php
// ✅ Sau khi đăng ký, fire event - không gọi EmailService trực tiếp
event(new UserRegistered($user));

// Listener tự xử lý
class SendWelcomeEmail implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new WelcomeMail($event->user));
    }
}
```

### 5.4 Interface-First

- Mọi Service và Repository phức tạp đều phải có **Interface** tương ứng.
- Binding Interface ↔ Implementation trong `RepositoryServiceProvider`.
- Điều này cho phép hoán đổi implementation (ví dụ: chuyển từ MySQL sang Elasticsearch cho search) mà không ảnh hưởng tầng Controller/Service.

---

## 6. TIÊU CHUẨN TÁI SỬ DỤNG (Reusability)

- **Trait** cho các hành vi dùng chung trên nhiều Model: `HasUuid`, `Sluggable`, `Filterable`.
- **Base Classes** (`BaseController`, `BaseService`, `BaseRepository`) chứa logic chung, các class con kế thừa.
- **Shared Services** trong `app/Services/Shared/` được dùng bởi cả Frontend, Admin và API.
- **Blade Components** cho UI element tái sử dụng.
- **API Resources** để transform data nhất quán, không dùng `$model->toArray()` trực tiếp.

---

## 7. BẢO MẬT (Security)

- **Không bao giờ** expose `id` integer ra ngoài API. Luôn dùng `uuid`.
- **Luôn validate** mọi input từ người dùng qua FormRequest.
- **Luôn authorize** mọi action qua Policy hoặc Permission.
- **Không log** thông tin nhạy cảm (password, token, API key).
- **Luôn dùng** `$fillable` trong Model, tuyệt đối không dùng `$guarded = []`.
- Mọi query có điều kiện từ user input phải dùng **Eloquent/Query Builder** (không nối chuỗi SQL thủ công).

---

## 8. TIÊU CHUẨN GIT & CODE REVIEW

- **Commit message** theo chuẩn Conventional Commits: `feat:`, `fix:`, `refactor:`, `docs:`, `test:`
- **Mỗi Pull Request** chỉ giải quyết một vấn đề.
- Code mới phải có **Unit Test hoặc Feature Test** tương ứng.
- **Không commit** file `.env`, `vendor/`, `node_modules/`, `storage/logs/`.

---

## 9. CHECKLIST TRƯỚC KHI HOÀN THÀNH MỌI TASK

Trước khi kết thúc bất kỳ task code nào, Agent phải tự kiểm tra:

- [ ] Code có đúng lớp trách nhiệm chưa? (Controller không chứa business logic)
- [ ] Có N+1 query nào không? (Kiểm tra Eager Loading)
- [ ] Có hardcode string/number nào không? (Dùng Enum/Constant)
- [ ] Các tác vụ nặng đã đưa vào Queue chưa?
- [ ] Input đã được validate qua FormRequest chưa?
- [ ] Authorization đã được kiểm tra chưa?
- [ ] Exception được xử lý đúng cách chưa? (Không nuốt lỗi)
- [ ] Cache có cần thiết không? Và đã được implement chưa?
- [ ] Các text hiển thị đã qua hàm dịch `__()` chưa?
