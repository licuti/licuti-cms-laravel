# Kiến trúc Hook & Registry của CMS (Core Architecture)

Tài liệu này mô tả cách hệ thống CMS quản lý các điểm mở rộng (Extension points) để duy trì cấu trúc mã nguồn gọn gàng, khả năng mở rộng không giới hạn mà không cần sửa đổi Core Controller/Views. Hệ thống này hoạt động tương tự như cơ chế Hook/Filter của WordPress nhưng được xây dựng bằng thiết kế hướng đối tượng (OOP) của Laravel.

## 1. Tại sao lại cần Registry/Hook?

Khi xây dựng một CMS có nhiều module (Post, Product, User, Order...), có rất nhiều chức năng chung lặp lại:
- Thao tác hàng loạt (Bulk Actions).
- Render các cột trong bảng dữ liệu (Table Columns).
- Hiển thị bộ lọc (Filters).
- Hiển thị Widget trên Dashboard.
- Cấu hình Menu Sidebar.

**Nếu không có Registry**: Code sẽ bị hard-code vào các file Controller/Blade. Khi một plugin mới hoặc module mới muốn thêm 1 cột vào bảng, họ phải sửa code trực tiếp của core file → Phá vỡ nguyên tắc Open/Closed (Mở để mở rộng, đóng để sửa đổi).

**Có Registry**: Core Controller/Blade chỉ định nghĩa cái "khung" (Frame) và lặp qua các Registry để hiển thị. Các module chỉ cần "đăng ký" bản thân mình vào Registry trong quá trình boot.

## 2. Bản đồ các Registry (Core Registries)

Hệ thống có các thành phần Registry lõi sau, đặt tại `app/Core/`:

1. **`BulkActionRegistry`** (Đã hoạt động): Đăng ký và xử lý các thao tác hàng loạt (Xóa, Đổi trạng thái, Cập nhật...) cho mọi module.
2. **`TableColumnRegistry`**: Khai báo các cột sẽ hiển thị trên bảng danh sách của một module.
3. **`FilterRegistry`**: Khai báo các bộ lọc (Tìm kiếm, Trạng thái, Ngày tháng...) trên đầu bảng danh sách.
4. **`SidebarMenuRegistry`**: Đăng ký các mục menu cho thanh điều hướng bên trái.
5. **`ExportRegistry`**: Cung cấp các công cụ xuất định dạng (CSV, Excel, PDF).
6. **`WidgetRegistry`**: Đăng ký hiển thị thống kê lên Dashboard.
7. **`SearchRegistry`**: Gắn các logic tìm kiếm vào thanh Global Search trên topbar.

## 3. Ví dụ luồng hoạt động (Mô hình)

### B1: Định nghĩa Registry
Core cung cấp lớp Singleton (vd: `BulkActionRegistry`).

### B2: Đăng ký tại Module
Mỗi module tự đăng ký thông qua `ServiceProvider` (vd: `BulkActionServiceProvider`):
```php
public function boot(): void
{
    $registry = $this->app->make(BulkActionRegistry::class);

    // Móc action 'status_1' vào module 'post_categories'
    $registry->register('post_categories', 'status_1', function (array $ids) {
        // Logic nghiệp vụ...
    });
}
```

### B3: Core Controller thực thi
Controller dùng chung hoặc FormRequest gọi Registry ra:
```php
public function bulk(BulkActionRequest $request, BulkActionRegistry $registry)
{
    $registry->dispatch(
        $request->input('bulk_module'),
        $request->input('action'), 
        $request->input('ids')
    );
}
```

## 4. Hướng dẫn phát triển các TODO Registry

Với các file Registry đang đánh dấu `TODO` (như `TableColumnRegistry`, `FilterRegistry`...), lộ trình phát triển như sau:

**1. Khai báo các phương thức đăng ký & lấy dữ liệu**
- `register(string $module, string $key, array $config)`
- `get(string $module): array`

**2. Liên kết vào Giao diện (Blade Components)**
Thay vì hard-code:
```blade
<th>Tên</th>
<th>Trạng thái</th>
```
Blade component (`x-admin.table`) sẽ lấy ra từ registry:
```blade
@php
    $columns = app(\App\Core\Table\TableColumnRegistry::class)->getColumns($module);
@endphp

@foreach($columns as $key => $column)
    <th>{{ $column['label'] }}</th>
@endforeach
```

**3. Khai báo module**
Module sẽ khai báo vào `TableColumnServiceProvider` (hoặc ServiceProvider của chính module đó):
```php
$columnRegistry->register('posts', 'title', ['label' => 'Tiêu đề', 'sortable' => true]);
```

Nhờ mô hình này, hệ thống CMS có thể phát triển lên 100+ modules hoặc cho phép bên thứ 3 viết Plugins mà vẫn duy trì kiến trúc cực kỳ Clean và Solid.
