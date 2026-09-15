# 09 — Base Classes & Nền tảng cốt lõi

> Mọi Service / Repository / FormRequest / Controller **BẮT BUỘC** kế thừa base tương ứng dưới đây. Tự viết mới ngoài base = vi phạm chuẩn.

---

## 3.1 `BaseService` (`app/Core/Base/BaseService.php`)

Mọi Service nghiệp vụ **bắt buộc kế thừa** `BaseService`:

```php
abstract class BaseService
{
    // Bọc logic ghi nhiều bảng trong DB Transaction, tự log & rethrow nếu lỗi
    protected function handleTransaction(callable $callback): mixed;

    // Log lỗi theo chuẩn hệ thống
    protected function handleException(Throwable $e, string $context = ''): never;

    // Sinh slug duy nhất cho bảng translations, tự động thêm suffix -1, -2 nếu trùng
    protected function generateUniqueSlug(
        string $translationTable,
        string $locale,
        ?string $slug,
        string $title,
        ?int $ignoreForeignId = null,
        string $foreignKey = 'post_id'
    ): string;
}
```

---

## 3.2 `BaseRepository` (`app/Repositories/BaseRepository.php`)

Đã tích hợp sẵn các phương thức: `all()`, `find()`, `findByUuid()`, `findWhere()`, `paginate()`, `create()`, `update()`, `delete()`, `count()`, `findManyByUuids()`, `updateByUuids()`.

**Chỉ viết thêm các hàm query đặc thù** vào Repository con (ví dụ: `getFiltered()`, `countByStatus()`, `deleteByIds()`).

> **Lưu ý về bulk delete:** Method chuẩn là `deleteByIds(array $ids): int` (không phải `deleteManyByIds`). Xem ví dụ tại `app/Repositories/Eloquent/PostRepository.php:55`.

---

## 3.3 Quy tắc kế thừa `FormRequest`: Admin Form vs API Form

| Loại | Class kế thừa | Hành vi khi validation fail |
|---|---|---|
| **Form nhập liệu Admin** (`app/Http/Requests/Admin/...`) | `Illuminate\Foundation\Http\FormRequest` | Tự động redirect về trang trước + kèm `$errors` để hiển thị trên Blade. |
| **Form API** (`app/Http/Requests/Api/...`) | `App\Core\Base\BaseRequest` | Ném `HttpResponseException` trả về cấu trúc JSON lỗi chuẩn của API. |

> ⚠️ **KHÔNG dùng `BaseRequest` cho Form nhập liệu Blade Admin** — sẽ gây lỗi vì API trả JSON thay vì redirect.

---

## 3.4 `BaseController` (`app/Core/Base/BaseController.php`)

Kế thừa khi Controller cần trả về cấu trúc phản hồi API (`successResponse`, `errorResponse`). Controller Admin trả View thông thường **không bắt buộc** kế thừa, nhưng nên kế thừa để có sẵn các helper.