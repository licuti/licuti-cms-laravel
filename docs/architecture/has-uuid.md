# HasUuid — UUID trong Eloquent Model

## Mục đích

`App\Models\Traits\HasUuid` là trait dùng cho các Eloquent Model cần:

- tự sinh UUID khi tạo bản ghi;
- dùng UUID làm định danh public trong URL và route model binding;
- vẫn giữ khóa chính số `id` cho quan hệ nội bộ và foreign key.

Trait nằm tại [`app/Models/Traits/HasUuid.php`](../../app/Models/Traits/HasUuid.php).

## Cách sử dụng

```php
namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasUuid;
}
```

Schema cần có cột UUID duy nhất:

```php
$table->uuid('uuid')->unique();
```

`uuid` không thay thế `id`. Mô hình chuẩn là:

```text
id   → khóa nội bộ, foreign key
uuid → định danh public, URL, API
```

## Các method trong trait

| Method | Nguồn | Tác dụng |
|---|---|---|
| `bootHasUuid()` | Convention của Laravel cho trait | Laravel tự gọi khi model khởi động trait |
| `creating()` | Eloquent event | Chạy trước khi INSERT |
| `getKeyName()` | Eloquent Model | Tên primary key nội bộ, mặc định là `id` |
| `getKey()` | Eloquent Model | Giá trị primary key nội bộ của instance |
| `getRouteKeyName()` | Eloquent Model, được override | Đổi route key từ `id` sang `uuid` |
| `getRouteKey()` | Eloquent Model | Giá trị cụ thể dùng khi tạo URL từ model |
| `resolveRouteBinding()` | Eloquent Model | Tìm model khi dùng implicit route model binding |
| `Str::uuid()` | Laravel Support | Sinh UUID v4 |

### Vì sao có `bootHasUuid()`?

Laravel có convention cho trait boot method:

```text
Trait HasUuid → bootHasUuid()
Trait HasSeo  → bootHasSeo()
```

Khi model dùng `HasUuid`, Laravel tự gọi `bootHasUuid()` trong quá trình khởi tạo model. Không cần gọi thủ công.

### Lifecycle tạo bản ghi

```text
Post::create(...)
    ↓
Eloquent firing creating
    ↓
bootHasUuid kiểm tra uuid
    ↓
Nếu uuid rỗng → sinh Str::uuid()
    ↓
INSERT vào database
```

Nếu code đã cấp UUID trước khi tạo, trait giữ lại giá trị đó. Database unique index vẫn là lớp bảo vệ cuối cùng chống trùng.

## Route model binding

Trait override:

```php
public function getRouteKeyName(): string
{
    return 'uuid';
}
```

Với route dùng implicit binding:

```php
Route::get('/posts/{post}', function (Post $post) {
    return $post;
});
```

Laravel sẽ tìm:

```sql
where uuid = ?
```

thay vì:

```sql
where id = ?
```

Khi tạo URL:

```php
route('posts.show', $post);
```

Laravel gọi `getRouteKey()` và đưa giá trị `$post->uuid` vào URL.

## Hai kiểu route đang tồn tại

### Implicit binding

```php
Route::get('/posts/{post}', [PostController::class, 'show']);

public function show(Post $post): View
{
    return view('posts.show', compact('post'));
}
```

`HasUuid::getRouteKeyName()` có tác dụng trực tiếp.

### Tự tìm qua service/repository

```php
Route::get('/admin/posts/{uuid}/edit', [PostController::class, 'edit']);

public function edit(string $uuid): View
{
    $post = $this->service->findByUuid($uuid);
}
```

Trong kiểu này, controller nhận chuỗi và repository tự dùng `where('uuid', ...)`. `getRouteKeyName()` không tham gia vào bước tìm record.

Không trộn hai kiểu trong cùng một endpoint nếu không có lý do rõ ràng.

## Các API Eloquent liên quan

### `getKeyName()` và `getKey()`

Đây là primary key nội bộ, không phải UUID:

```php
$post->getKeyName(); // id
$post->getKey();     // 123
```

### `getRouteKeyName()` và `getRouteKey()`

Đây là public route key:

```php
$post->getRouteKeyName(); // uuid
$post->getRouteKey();     // UUID của post
```

### `resolveRouteBinding()`

Laravel dùng method này trong implicit binding để resolve tham số route. Trait giữ implementation mặc định của Eloquent; chỉ thay đổi field thông qua `getRouteKeyName()`.

## Giới hạn quan trọng

`HasUuid` chỉ bảo vệ luồng Eloquent event:

```php
Post::create(...); // có creating event
$post->save();     // có model events
```

Các cách sau không chạy `bootHasUuid()`:

```php
DB::table('posts')->insert(...);
Post::where(...)->update(...);
Post::upsert(...);
```

Vì vậy:

- import/upsert phải tự tạo UUID;
- mọi bảng có UUID bắt buộc phải có unique index;
- dữ liệu UUID cũ cần được backfill trước khi đổi cột thành `NOT NULL`;
- không nên để `uuid` trong `$fillable` nếu request không được phép thay đổi public identifier.

## Checklist khi thêm Model mới

- [ ] Model dùng `use HasUuid;`.
- [ ] Migration có `$table->uuid('uuid')->unique()`.
- [ ] UUID là `NOT NULL` nếu model luôn public.
- [ ] Factory không tạo UUID thủ công nếu trait đã đảm nhiệm.
- [ ] Không đưa `uuid` vào `$fillable` nếu không cần import có kiểm soát.
- [ ] Route dùng `{model}` + type-hint nếu muốn implicit binding.
- [ ] Nếu route dùng `{uuid}`, service/repository phải dùng `findByUuid()`.
- [ ] Có test tạo UUID và test route key nếu model public.
