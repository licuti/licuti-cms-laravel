# 12 — Quy trình thêm trường dữ liệu mới & Layer Rules

---

## 6. QUY TRÌNH THÊM 1 TRƯỜNG DỮ LIỆU MỚI

Khi bổ sung trường mới, phải xác định trường đó thuộc **Bảng Chính (dữ liệu chung)** hay **Bảng Bản Dịch (`_translations`)**:

### 1. Migration & Model
- Tạo migration thêm cột vào bảng tương ứng (`articles` hoặc `article_translations`).
- Khai báo cột vào mảng `$fillable` của Model tương ứng.

### 2. DTO
- **Nếu là trường chung:** Khai báo property trong Constructor, lấy qua `$request->input('...')` trong `fromRequest()`, đưa vào `toArray()`.
- **Nếu là trường đa ngữ:** Nằm tự động trong mảng `translations` của DTO.

### 3. FormRequest
- Thêm rule validate vào `rules()` (VD: `author_id` ở bảng chính hoặc `translations.*.new_field`).

### 4. Service
- **Nếu là trường chung:** Tự động ghi vào DB qua `$dto->toArray()`.
- **Nếu là trường đa ngữ:** Cập nhật trong hàm `saveTranslations()`.

### 5. View
- Thêm `<x-admin.form-group>` vào `form.blade.php` (nếu là trường đa ngữ thì đặt bên trong vòng lặp `$activeLanguages`).

---

## 7. QUY TẮC VIẾT TỪNG LỚP (LAYER RULES)

### Controller
- Inject Service & Repository qua `__construct(private readonly ...)`.
- Tham số route nhận `string $uuid`.
- Xử lý redirect dựa theo `submit_action` (`save` hoặc `save_and_edit`).
- **KHÔNG** chứa query DB hoặc `if/else` nghiệp vụ phức tạp.
- *(Simple Profile được nới lỏng — xem [07](../07-development-process.md) mục 1.5.)*

### FormRequest
- Form Admin **luôn kế thừa** `Illuminate\Foundation\Http\FormRequest`.
- Form API **kế thừa** `App\Core\Base\BaseRequest`.
- Validate trạng thái bằng Enum: `['required', new Enum(ContentStatus::class)]`.
- Sử dụng `prepareForValidation()` để chuẩn hóa / bảo vệ dữ liệu nhạy cảm trước khi validate.

### DTO
- Luôn khai báo `class` với các thuộc tính `public readonly`.
- Hàm tạo tĩnh `fromRequest(Request $request): self`.
- Tách riêng `translations` (mảng đa ngữ) khỏi các trường chung.

### Service
- Luôn kế thừa `BaseService`.
- Bọc các lệnh ghi DB liên quan trong `$this->handleTransaction(...)`.
- Luôn gọi `$this->generateUniqueSlug(...)` để làm sạch và chống trùng slug.
- Inject `RepositoryInterface` (không inject class cụ thể).
- **KHÔNG** phụ thuộc vào `Request` / `Response` HTTP.

### Repository
- Kế thừa `BaseRepository`, implement Interface tương ứng.
- Đặt tại `app/Repositories/Eloquent/{Name}Repository.php`.
- Sử dụng Eager Loading `with([...])` để ngăn chặn triệt để lỗi N+1 Query.
- **KHÔNG** chứa business logic.

### Model
- Dùng `HasUuid` để tự sinh UUID khi tạo bản ghi.
- Dùng `HasSeo` nếu thực thể có SEO metadata.
- Cast đúng kiểu (`datetime`, `boolean`, `integer`, `array`, `json`).
- Eager load relations trong accessor nếu cần (xem `Post::getImageUrlAttribute`).

### Enum
- Mỗi trạng thái là 1 `case` với `value` là string lưu DB.
- Có method `label()` trả về chuỗi i18n hiển thị.
- Có method `color()` nếu cần badge màu (`ContentStatus::color()`).