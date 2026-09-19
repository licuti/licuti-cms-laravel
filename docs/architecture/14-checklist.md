# 14 — Checklist kiểm tra trước khi hoàn thành

> Trước khi hoàn thành bất kỳ tính năng hoặc module nào, hãy tự kiểm tra theo danh sách dưới. Có thể copy nội dung này vào **PR template** trên GitHub để đảm bảo mọi contributor đều check.

---

## 1. Kiến trúc & Logic

- [ ] Đã chọn đúng **Profile** cho module (Full vs Simple — xem [07](../07-development-process.md) mục 1.5) và áp dụng đồng nhất, không pha trộn.
- [ ] Nếu là Simple Profile: không tạo DTO, không có Service method chỉ chuyển tiếp (≤3 dòng), mọi ghi trong Controller đều bọc `DB::transaction()`.
- [ ] Controller không chứa query DB hoặc logic nghiệp vụ rẽ nhánh (trừ ngoại lệ Simple Profile ở trên).
- [ ] Service kế thừa `BaseService`, inject `RepositoryInterface` (không inject class).
- [ ] Bọc các thao tác ghi dữ liệu nhiều bảng trong `$this->handleTransaction(...)`.
- [ ] Gọi `$this->generateUniqueSlug(...)` khi lưu bản dịch bài viết/danh mục/trang.
- [ ] Sử dụng `Enum` cho các trạng thái, không hardcode string.
- [ ] Dùng `uuid` làm định danh route ngoài view/URL, không dùng `id`.
- [ ] Repository đặt tại `app/Repositories/Eloquent/`, binding trong `RepositoryServiceProvider` trỏ đúng namespace.
- [ ] Method bulk delete dùng `deleteByIds()` (không phải `deleteManyByIds()`).
- [ ] **Không để scaffold dở**: schema DB đúng, model có `$fillable` — không tạo file không chạy được (xem [07](../07-development-process.md) mục 1.6).

## 2. FormRequest & Bảo mật

- [ ] FormRequest cho Admin kế thừa `Illuminate\Foundation\Http\FormRequest`.
- [ ] FormRequest cho API kế thừa `App\Core\Base\BaseRequest`.
- [ ] Áp dụng `prepareForValidation()` để bảo vệ các trường nhạy cảm (`author_id`).
- [ ] Kiểm tra phân quyền trong hàm `authorize()`.
- [ ] Trường slug được validate với regex chuẩn URL.

## 3. Giao diện & Blade

- [ ] Sử dụng 100% class **Bootstrap 5.3** (không có class Tailwind).
- [ ] Form dùng chung 1 file `form.blade.php` cho cả create và edit.
- [ ] Bọc toàn bộ input/select trong `<x-admin.form-group>`.
- [ ] Xóa bản ghi dùng cơ chế toàn cục `form-confirm` hoặc mảng `actions` của `<x-admin.row-actions>`, không viết script SweetAlert2 thủ công.
- [ ] Khai báo thao tác hàng loạt qua `BulkActionRegistry`.
- [ ] Sử dụng component Blade từ danh mục chuẩn (xem [13-ui-conventions.md](13-ui-conventions.md)), không tự chế.

## 4. Database

- [ ] Migration có cả bảng chính + bảng `_translations` (nếu là thực thể đa ngữ).
- [ ] Index trên `slug` và unique trên `['foreign_id', 'locale']`.
- [ ] Có foreign key với cascadeOnDelete / nullOnDelete phù hợp.
- [ ] SoftDeletes nếu bảng cần khôi phục.
- [ ] Chạy `php artisan migrate` thành công, không có warning.

## 5. SEO & i18n

- [ ] Có bảng `_translations` cho mọi trường hiển thị đa ngữ.
- [ ] Component `<x-admin.seo-meta>` được nhúng trong form.
- [ ] Service gọi `$model->saveSeoTranslations(...)` trong transaction.

## 6. Testing & Documentation

- [ ] Có ít nhất 1 test cho Service chính (create / update / delete).
- [ ] Có Feature test cho flow CRUD cơ bản.
- [ ] Cập nhật `docs/02-database-overview.md` nếu thêm bảng mới.
- [ ] Cập nhật `docs/04-permissions.md` nếu thêm quyền mới.
- [ ] Cập nhật `README.md` / `DEVELOPER.md` nếu thay đổi cách cài đặt.