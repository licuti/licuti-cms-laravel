# Module: Order

> ✅ **Hoàn thành** | Profile: Full | Tầng 6 — Đơn hàng & Thanh toán
> Route: `admin.orders.*` | Views: `resources/views/admin/orders/`
> Spec: [`07` §3.3](../07-development-process.md) (MODULE: Orders)

---

## 1. Hiện trạng & Cấu trúc Dữ liệu (Cập nhật 10/2026)

Toàn bộ hệ thống quản lý đơn hàng đã được nâng cấp hoàn thiện theo mô hình **Service - Repository - DTO** và tuân thủ tuyệt đối quy chuẩn [AGENTS.md](../../.agents/AGENTS.md).

### 1.1 Cơ sở dữ liệu (4 bảng liên kết)

| Bảng | Chức năng | Chi tiết khóa & index |
|---|---|---|
| `orders` | Bảng chính quản lý đơn hàng | `id`, `uuid` (unique), `order_number` (unique), `user_id` (FK, nullable), `payment_method_id` (FK, nullable), `status` (enum string), `subtotal`, `discount_amount`, `shipping_fee`, `tax_amount`, `total_amount`, `currency`, `notes`, `cancel_reason`, `paid_at`, `shipped_at`, `completed_at`, `cancelled_at`, `deleted_at` (SoftDeletes). Index composite: `(status, created_at)`. |
| `order_items` | Snapshot sản phẩm tại thời điểm mua | `id`, `uuid` (unique), `order_id` (FK cascade), `product_id` (FK nullable), `product_variant_id` (FK nullable), `product_name`, `product_sku`, `unit_price`, `quantity`, `discount_amount`, `subtotal`, `meta` (JSON lưu thuộc tính biến thể). |
| `shipping_addresses` | Thông tin địa chỉ nhận hàng | `id`, `uuid` (unique), `order_id` (FK cascade), `recipient_name`, `phone`, `email`, `address_line`, `ward`, `district`, `province`, `country`, `zip_code`. |
| `order_status_histories` | Nhật ký thay đổi trạng thái | `id`, `order_id` (FK cascade), `from_status` (nullable), `to_status`, `note`, `changed_by_user_id` (FK nullable). |

---

## 2. Các Lớp Kiến trúc (Architecture Layers)

### 2.1 Enums & State Machine
- **`app/Enums/OrderStatus.php`**:
  - Các trạng thái: `PENDING`, `CONFIRMED`, `PROCESSING`, `SHIPPING`, `DELIVERED`, `COMPLETED`, `CANCELLED`, `REFUNDED`.
  - Helper `label()` đa ngôn ngữ qua `__()`.
  - Helper `badgeClass()` chuẩn Bootstrap 5.3.
  - State Machine `canTransitionTo(OrderStatus $newStatus)`: Quy định luồng chuyển đổi trạng thái hợp lệ, chặn việc nhảy cóc hoặc đổi trạng thái khi đơn đã ở trạng thái kết thúc (`CANCELLED`, `REFUNDED`).

### 2.2 Models
- **`app/Models/Order.php`**:
  - Trait: `HasFactory`, `HasUuid`, `SoftDeletes`.
  - Cấu hình `$fillable` chặt chẽ (không dùng `$guarded`).
  - Casts: `status => OrderStatus::class`, các trường tiền tệ `decimal:2`, các trường thời gian `datetime`.
  - Quan hệ: `user()`, `paymentMethod()`, `shippingAddress()`, `items()`, `statusHistories()`, `payments()`.
  - Scopes: `scopePending()`, `scopeCompleted()`, `scopeCancelled()`, `scopeByStatus()`, `scopeSearch()`.
  - Helpers: `isPaid()`, `canCancel()`, `hasAvailableTransitions()`, `generateOrderNumber()`.
- **`app/Models/OrderItem.php`**: Model snapshot với quan hệ `order()`, `product()`, `variant()`.
- **`app/Models/ShippingAddress.php`**: Lưu địa chỉ giao hàng với accessor `full_address`.
- **`app/Models/OrderStatusHistory.php`**: Ghi vết lịch sử với quan hệ `order()`, `changedBy()`.

### 2.3 Repository
- **`app/Repositories/Interfaces/OrderRepositoryInterface.php`**:
  - `getFilteredPaginated(array $filters, int $perPage)`: Phân trang kèm bộ lọc đa điều kiện.
  - `findByOrderNumber(string $orderNumber)`: Tìm kiếm đơn hàng theo mã.
  - `findByUuidWithRelations(string $uuid)`: Lấy chi tiết đơn kèm eager load đầy đủ quan hệ.
  - `getStatusCounts()`: Thống kê số lượng đơn theo từng trạng thái bằng 1 câu `GROUP BY` tối ưu O(1).
- **`app/Repositories/Eloquent/OrderRepository.php`**: Implement interface, kế thừa `BaseRepository`.

### 2.4 DTO & Service
- **`app/DTOs/Order/OrderDTO.php`**:
  - Strongly-typed `readonly` properties: `userId`, `paymentMethodId`, `shippingAddress`, `items`, `subtotal`, `discountAmount`, `shippingFee`, `taxAmount`, `totalAmount`, `notes`, `status`, `currency`.
  - Factory method `fromRequest(Request $request)` tự động tính toán tổng tiền trên server.
- **`app/Services/Admin/Order/OrderService.php`**:
  - Kế thừa `BaseService`.
  - Tích hợp caching cho `getStatusCounts` (60s) và `getActivePaymentMethods` (300s).
  - Tự động xóa cache (`flushStatusCountsCache()`) sau mỗi thao tác thêm/sửa/hủy/xóa đơn.
  - Toàn bộ thao tác `create()` và `updateStatus()` được bọc trong `DB::transaction()`.
  - Tự động cập nhật các mốc thời gian: `shipped_at`, `completed_at`, `paid_at`, `cancelled_at`.

### 2.5 Controller & FormRequests
- **`app/Http/Controllers/Admin/OrderController.php`**:
  - Kế thừa `BaseController`.
  - Các actions: `index`, `show`, `create`, `store`, `updateStatus`, `cancel`, `destroy`.
  - Không truy vấn CSDL trực tiếp, tất cả thông qua `OrderService`.
- **`app/Http/Requests/Admin/Order/StoreOrderRequest.php`**: Validate thông tin khách hàng, địa chỉ giao hàng và mảng danh sách sản phẩm snapshot.
- **`app/Http/Requests/Admin/Order/UpdateOrderStatusRequest.php`**: Validate trạng thái theo enum `OrderStatus`.

---

## 3. Giao diện Admin (Bootstrap 5.3)

| View file | Mô tả chức năng |
|---|---|
| `resources/views/admin/orders/index.blade.php` | - Tab đếm số lượng đơn hàng theo từng trạng thái (tích hợp badge màu).<br>- Bộ lọc kết hợp: từ khóa tìm kiếm (mã đơn/khách hàng/SĐT), cổng thanh toán, khoảng ngày đặt hàng.<br>- Bảng danh sách đơn hàng có hiển thị trạng thái thanh toán, thời gian đặt, tổng tiền, hành động. |
| `resources/views/admin/orders/show.blade.php` | - Layout 2 cột chuẩn: Cột chính hiển thị chi tiết danh sách sản phẩm (đơn giá, số lượng, giảm giá, thành tiền), bảng tính tổng tiền và Timeline lịch sử thay đổi trạng thái kèm người thực hiện.<br>- Cột phụ hiển thị form xử lý chuyển đổi trạng thái (chỉ hiện các trạng thái hợp lệ theo State Machine), form hủy đơn (nếu được phép) kèm lý do, thẻ thông tin người nhận và thẻ phương thức thanh toán. |
| `resources/views/admin/orders/form.blade.php` | Form tạo đơn hàng thủ công dành cho Quản trị viên (hỗ trợ nhập nhiều sản phẩm, địa chỉ giao hàng và phương thức thanh toán). |

---

## 4. Kiểm thử Tự động (Automated Tests)

File test: `tests/Feature/Admin/OrderCrudTest.php` (**8/8 test cases PASS**, 30 assertions):
- `test_guest_cannot_access_orders_index`: Kiểm tra chặn khách vãng lai truy cập.
- `test_admin_can_view_orders_index`: Kiểm tra Admin hiển thị danh sách đơn hàng.
- `test_admin_can_create_order_with_items_and_address`: Kiểm tra tạo đơn hàng hoàn chỉnh gồm items snapshot, shipping address và lịch sử trạng thái ban đầu.
- `test_admin_can_view_order_show`: Kiểm tra hiển thị trang chi tiết đơn hàng.
- `test_admin_can_update_order_status_with_history`: Kiểm tra chuyển trạng thái hợp lệ và lưu log lịch sử.
- `test_invalid_status_transition_is_rejected`: Kiểm tra từ chối các bước chuyển trạng thái vi phạm State Machine.
- `test_admin_can_cancel_order`: Kiểm tra hủy đơn và lưu lý do hủy.
- `test_admin_can_soft_delete_order`: Kiểm tra xóa mềm (Soft Delete) đơn hàng.
