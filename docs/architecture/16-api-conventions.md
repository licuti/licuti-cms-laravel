# 16 — API Conventions

> Chuẩn response, error code, versioning cho mọi API endpoint.

---

## 1. Chuẩn Response Envelope

Mọi response JSON từ API **BẮT BUỘC** tuân thủ cấu trúc envelope thống nhất:

### Success Response

```json
{
    "success": true,
    "data": { ... } | [ ... ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "last_page": 10,
            "per_page": 15,
            "total": 142
        }
    },
    "message": "Thành công."
}
```

### Error Response

```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Dữ liệu không hợp lệ.",
        "details": {
            "field_name": ["Trường này là bắt buộc."]
        }
    }
}
```

---

## 2. Error Code chuẩn

| HTTP Status | Error Code | Ý nghĩa |
|---|---|---|
| 400 | `BAD_REQUEST` | Request không đúng định dạng |
| 401 | `UNAUTHORIZED` | Chưa đăng nhập / token hết hạn |
| 403 | `FORBIDDEN` | Không có quyền truy cập |
| 404 | `NOT_FOUND` | Resource không tồn tại |
| 405 | `METHOD_NOT_ALLOWED` | HTTP method không hỗ trợ |
| 422 | `VALIDATION_ERROR` | Dữ liệu không qua validate |
| 429 | `RATE_LIMIT_EXCEEDED` | Quá nhiều request |
| 500 | `INTERNAL_SERVER_ERROR` | Lỗi hệ thống |

Code tùy biến cho nghiệp vụ: `POST_NOT_FOUND`, `INSUFFICIENT_STOCK`, `COUPON_INVALID`... (xem `app/Exceptions/`).

---

## 3. Versioning

- URL prefix: `/api/v1/...`, `/api/v2/...`
- Khi phá vỡ response structure → bump version mới.
- Giữ version cũ ít nhất 6 tháng sau khi ra version mới.

### Ví dụ routing

```php
// routes/api.php
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('posts', Api\V1\PostController::class);
    });
});
```

URL mẫu: `GET /api/v1/posts`, `POST /api/v1/posts`, ...

---

## 4. Authentication

- Dùng **Laravel Sanctum** cho SPA + mobile app.
- Token truyền qua header: `Authorization: Bearer {token}`.
- Endpoint login trả về token + thông tin user:

```json
{
    "success": true,
    "data": {
        "user": { "id": 1, "name": "...", "email": "..." },
        "access_token": "1|abc123...",
        "token_type": "Bearer",
        "expires_at": "2026-12-31T00:00:00Z"
    }
}
```

---

## 5. FormRequest cho API

**BẮT BUỘC** kế thừa `App\Core\Base\BaseRequest` (không phải `Illuminate\Foundation\Http\FormRequest`):

```php
namespace App\Http\Requests\Api\V1\Post;

use App\Core\Base\BaseRequest;

class StorePostRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status'           => ['required', 'in:draft,published,archived'],
            'translations.vi.title' => ['required', 'string', 'max:255'],
            'translations.vi.slug'  => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

> `BaseRequest` tự động trả về JSON envelope `VALIDATION_ERROR` khi fail, không redirect.

---

## 6. API Resources

Dùng `App\Http\Resources\{Name}\{Name}Resource` để chuẩn hóa output:

```php
namespace App\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        $locale = app()->getLocale();

        return [
            'uuid'        => $this->uuid,
            'status'      => $this->status,
            'image_url'   => $this->image_url,
            'title'       => $this->translate($locale)?->title,
            'slug'        => $this->translate($locale)?->slug,
            'author'      => $this->author?->only(['uuid', 'name']),
            'published_at' => $this->published_at?->toISOString(),
            'created_at'  => $this->created_at?->toISOString(),
        ];
    }
}
```

Controller trả về:

```php
public function show(string $uuid): PostResource
{
    return new PostResource($this->repository->findByUuid($uuid));
}
```

---

## 7. Rate Limiting

Mặc định: 60 requests / phút cho mỗi user (theo Sanctum). Tùy chỉnh trong `App\Providers\RouteServiceProvider`:

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

Khi vượt rate limit → response 429 với `RATE_LIMIT_EXCEEDED`.

---

## 8. Documenting API

Khuyến nghị dùng **Scribe** hoặc **OpenAPI/Swagger** để generate docs tự động:

```bash
composer require --dev knuckleswtf/scribe
php artisan vendor:publish --tag=scribe-config
php artisan scribe:generate
```

Docs sẽ ở `/docs` (Laravel) hoặc export ra Postman collection.