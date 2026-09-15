# LICUTI CMS — HƯỚNG DẪN PHÁT TRIỂN CHUẨN

> Tài liệu này MÔ TẢ CHÍNH XÁC cấu trúc và quy chuẩn thực tế của dự án Licuti CMS (Laravel 12, Bootstrap 5.3 + SCSS, Kiến trúc Service - Repository - DTO).
> Mọi tính năng và module mới PHẢI tuân thủ nghiêm ngặt quy trình và cấu trúc file được hướng dẫn ở đây.

---

## MỤC LỤC

1. [Kiến trúc tổng quan](#1-kiến-trúc-tổng-quan)
2. [Cấu trúc thư mục](#2-cấu-trúc-thư-mục)
3. [Base Classes & Nền tảng cốt lõi](#3-base-classes--nền-tảng-cốt-lõi)
4. [Cơ chế hệ thống cốt lõi (Core Mechanisms)](#4-cơ-chế-hệ-thống-cốt-lõi)
   - 4.1 [Hệ thống Đa ngôn ngữ (i18n) & SEO](#41-hệ-thống-đa-ngôn-ngữ-i18n--seo)
   - 4.2 [Cơ chế Sinh Slug duy nhất (`generateUniqueSlug`)](#42-cơ-chế-sinh-slug-duy-nhất-generateuniqueslug)
   - 4.3 [Xử lý Thao tác hàng loạt (`BulkActionRegistry`)](#43-xử-lý-thao-tác-hàng-loạt-bulkactionregistry)
   - 4.4 [Hệ thống Xác nhận Xóa toàn cục (`form-confirm`)](#44-hệ-thống-xác-nhận-xóa-toàn-cục-form-confirm)
5. [Quy trình xây dựng 1 MODULE MỚI từ A-Z](#5-quy-trình-xây-dựng-1-module-mới)
6. [Quy trình thêm 1 TRƯỜNG DỮ LIỆU mới](#6-quy-trình-thêm-1-trường-dữ-liệu-mới)
7. [Quy tắc viết từng lớp (Layer Rules)](#7-quy-tắc-viết-từng-lớp)
8. [Quy chuẩn Giao diện (Bootstrap 5.3) & Danh mục Blade Component](#8-quy-chuẩn-giao-diện-bootstrap-53--danh-mục-blade-component)
9. [Checklist kiểm tra trước khi hoàn thành](#9-checklist-kiểm-tra-trước-khi-hoàn-thành)

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

### Nguyên tắc vàng:
1. **Controller:** KHÔNG chứa query DB trực tiếp, KHÔNG chứa if/else logic nghiệp vụ phức tạp.
2. **Service:** KHÔNG phụ thuộc vào HTTP Request / Response; chỉ nhận DTO hoặc tham số nguyên thủy.
3. **Repository:** KHÔNG chứa business logic (chỉ query và trả về Collection/Model/Paginator).
4. **Định danh an toàn:** Dùng `uuid` làm định danh công khai ra ngoài route, API, URL. KHÔNG lộ `id` tự tăng.
5. **Giao diện:** Chuẩn giao diện là **Bootstrap 5.3 + SCSS**. Tuyệt đối KHÔNG dùng các class Tailwind CSS.

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
│   ├── Enums/                         ← Enum PHP 8.1+ (PostStatus, UserStatus, OrderStatus...)
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
│   └── {Module}Repository.php
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

---

## 3. BASE CLASSES & NỀN TẢNG CỐT LÕI

### 3.1 BaseService (`app/Core/Base/BaseService.php`)
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

### 3.2 BaseRepository (`app/Repositories/BaseRepository.php`)
Đã tích hợp sẵn các phương thức: `all()`, `find()`, `findByUuid()`, `findWhere()`, `paginate()`, `create()`, `update()`, `delete()`, `count()`, `findManyByUuids()`, `updateByUuids()`. Chỉ viết thêm các hàm query đặc thù vào Repository con.

### 3.3 Quy tắc kế thừa FormRequest: Admin Form vs API Form
- **FormRequest cho Admin Web (`app/Http/Requests/Admin/...`):**
  > **BẮT BUỘC kế thừa:** `Illuminate\Foundation\Http\FormRequest`.
  > Khi validation thất bại, Laravel sẽ tự động redirect về trang trước và kèm theo `$errors` để hiển thị trên giao diện Blade.
- **FormRequest cho API (`app/Http/Requests/Api/...`):**
  > **Mới kế thừa:** `App\Core\Base\BaseRequest`.
  > `BaseRequest` ghi đè `failedValidation` để ném `HttpResponseException` trả về cấu trúc JSON lỗi chuẩn của API. KHÔNG dùng `BaseRequest` cho Form nhập liệu Blade Admin.

### 3.4 BaseController (`app/Core/Base/BaseController.php`)
Kế thừa khi Controller cần trả về cấu trúc phản hồi API (`successResponse`, `errorResponse`).

---

## 4. CƠ CHẾ HỆ THỐNG CỐT LÕI

### 4.1 Hệ thống Đa ngôn ngữ (i18n) & SEO
Toàn bộ các thực thể nội dung (Post, Category, Page, Product...) đều áp dụng cấu trúc đa ngữ:
- **Bảng cha (`posts`, `categories`, `pages`):** Lưu dữ liệu phi ngôn ngữ (`uuid`, `status`, `image`, `author_id`, `published_at`).
- **Bảng dịch (`post_translations`, `category_translations`):** Lưu dữ liệu theo locale (`locale`, `title`, `slug`, `excerpt`, `content`).
  - Đánh chỉ mục: `$table->unique(['post_id', 'locale']);` và `$table->index('slug');`.
- **SEO Metadata (`seo_translations`):**
  - Model sử dụng Trait `App\Traits\HasSeo`.
  - Form Blade nhúng component: `<x-admin.seo-meta :model="$post ?? null" :locales="$activeLanguages" :defaultLocale="$defaultLocale" />`.
  - Service gọi: `$model->saveSeoTranslations($dto->translations);`.

### 4.2 Cơ chế Sinh Slug duy nhất (`generateUniqueSlug`)
Khi lưu bản dịch, luôn gọi `generateUniqueSlug` từ `BaseService`. Nếu người dùng dán hoặc nhập một chuỗi bất kỳ, hàm sẽ làm sạch bằng `Str::slug()` và kiểm tra xem locale đó đã tồn tại slug chưa; nếu trùng sẽ tự động thêm hậu tố `-1`, `-2`:
```php
$data['slug'] = $this->generateUniqueSlug(
    translationTable: 'post_translations',
    locale: $locale,
    slug: $data['slug'] ?? null,
    title: $data['title'],
    ignoreForeignId: $post->id,
    foreignKey: 'post_id'
);
```

### 4.3 Xử lý Thao tác hàng loạt (`BulkActionRegistry`)
Không tự viết các hàm xử lý bulk riêng lẻ trong Controller. Sử dụng kiến trúc Registry tập trung:
1. **Đăng ký Action** trong `app/Providers/BulkActionServiceProvider.php`:
   ```php
   $registry->register('posts', 'delete', 'Xóa đã chọn', function (array $ids) {
       app(PostRepositoryInterface::class)->deleteManyByIds($ids);
   });
   ```
2. **Controller Controller**:
   - Trong `index()`: Lấy options qua `'bulkActions' => $bulkRegistry->getActionOptions('posts')`.
   - Trong `bulk()`: Dispatch qua `$registry->dispatch('posts', $request->input('action'), $request->input('ids'))`.

### 4.4 Hệ thống Xác nhận Xóa toàn cục (`form-confirm`)
Tuyệt đối **KHÔNG viết `@push('scripts')`** với script SweetAlert2 riêng ở từng trang `index.blade.php`.
Tất cả đã được quản lý toàn cục trong `resources/js/admin/table-utils.js`:
- **Khi dùng Component `<x-admin.row-actions>`**: Truyền các key confirm vào action array:
  ```php
  $actions = [
      ['label' => 'Sửa', 'route' => route('admin.posts.edit', $post->uuid), 'color' => 'blue'],
      [
          'label'         => 'Xóa',
          'route'         => route('admin.posts.destroy', $post->uuid),
          'method'        => 'DELETE',
          'color'         => 'red',
          'confirm_title' => 'Xóa bài viết?',
          'confirm_text'  => 'Bạn có chắc chắn muốn xóa bài viết này?',
          'confirm_btn'   => 'Xóa ngay'
      ],
  ];
  ```
- **Khi viết form xóa thủ công:** Thêm class `form-confirm` và các data attribute:
  ```html
  <form action="..." method="POST" class="form-confirm"
        data-confirm-title="Xóa bản ghi?"
        data-confirm-text="Thao tác này không thể hoàn tác."
        data-confirm-btn="Xóa ngay">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
  </form>
  ```

---

## 5. QUY TRÌNH XÂY DỰNG 1 MODULE MỚI

*Ví dụ: Xây dựng module `Article` (Bài viết / Tin tức) chuẩn từ A-Z.*

### BƯỚC 1 — Migrations & Models

#### 1.1 Migration (`database/migrations/`)
Tách làm 2 bảng: bảng chính và bảng translations:
```php
// Bảng chính: articles
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('status', 30)->default('draft')->index();
    $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('image')->nullable();
    $table->timestamp('published_at')->nullable();
    $table->softDeletes();
    $table->timestamps();
});

// Bảng bản dịch: article_translations
Schema::create('article_translations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
    $table->string('locale', 10);
    $table->string('title');
    $table->string('slug')->index();
    $table->text('excerpt')->nullable();
    $table->longText('content')->nullable();
    $table->unique(['article_id', 'locale']);
});
```

#### 1.2 Model Chính (`app/Models/Article.php`)
```php
namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasUuid, SoftDeletes, HasSeo;

    protected $fillable = [
        'uuid', 'status', 'author_id', 'image', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translate(string $locale): ?ArticleTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
```

---

### BƯỚC 2 — Enum Trạng thái (`app/Core/Enums/ArticleStatus.php`)

```php
namespace App\Core\Enums;

enum ArticleStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT     = 'draft';
    case ARCHIVED  = 'archived';

    public function label(): string
    {
        return match($this) {
            self::PUBLISHED => __('Đã xuất bản'),
            self::DRAFT     => __('Bản nháp'),
            self::ARCHIVED  => __('Lưu trữ'),
        };
    }
}
```

---

### BƯỚC 3 — Repository Interface & Implementation

#### 3.1 Interface (`app/Repositories/Interfaces/ArticleRepositoryInterface.php`)
```php
namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface extends BaseRepositoryInterface
{
    public function getFiltered(array $filters): LengthAwarePaginator;
    public function countByStatus(): array;
}
```

#### 3.2 Implementation (`app/Repositories/ArticleRepository.php`)
```php
namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleRepository extends BaseRepository implements ArticleRepositoryInterface
{
    public function __construct(Article $model)
    {
        parent::__construct($model);
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['translations', 'author'])->latest();

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('translations', function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        return $query->paginate(15);
    }

    public function countByStatus(): array
    {
        return $this->model->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}
```

#### 3.3 Đăng ký Binding (`app/Providers/RepositoryServiceProvider.php`)
```php
$this->app->bind(
    \App\Repositories\Interfaces\ArticleRepositoryInterface::class,
    \App\Repositories\ArticleRepository::class
);
```

---

### BƯỚC 4 — DTO (`app/DTOs/Article/ArticleDTO.php`)

Gom chung vào một DTO duy nhất, đóng gói và bóc tách dữ liệu sạch:
```php
namespace App\DTOs\Article;

use Illuminate\Http\Request;

class ArticleDTO
{
    public function __construct(
        public readonly string $status,
        public readonly ?int $authorId,
        public readonly ?string $image,
        public readonly ?string $publishedAt,
        public readonly array $translations,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status:       $request->input('status', 'draft'),
            authorId:     $request->input('author_id'),
            image:        $request->input('image'),
            publishedAt:  $request->input('published_at'),
            translations: $request->input('translations', []),
        );
    }

    public function toArray(): array
    {
        return [
            'status'       => $this->status,
            'author_id'    => $this->authorId,
            'image'        => $this->image,
            'published_at' => $this->publishedAt,
        ];
    }
}
```

---

### BƯỚC 5 — Service (`app/Services/Admin/Article/ArticleService.php`)

Kế thừa `BaseService`, quản lý lưu bản dịch, sinh slug an toàn và lưu SEO:
```php
namespace App\Services\Admin\Article;

use App\Core\Base\BaseService;
use App\Core\Enums\ArticleStatus;
use App\DTOs\Article\ArticleDTO;
use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService extends BaseService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository
    ) {}

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFiltered($filters);
    }

    public function create(ArticleDTO $dto): Article
    {
        return $this->handleTransaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['author_id'] = $data['author_id'] ?? auth()->id();

            $article = $this->repository->create($data);
            $this->saveTranslations($article, $dto->translations);

            return $article;
        });
    }

    public function update(string $uuid, ArticleDTO $dto): Article
    {
        return $this->handleTransaction(function () use ($uuid, $dto) {
            $article = $this->repository->findByUuid($uuid);
            $this->repository->update($article->id, $dto->toArray());
            $this->saveTranslations($article, $dto->translations);

            return $article->fresh();
        });
    }

    public function delete(string $uuid): bool
    {
        $article = $this->repository->findByUuid($uuid);
        return $this->repository->delete($article->id);
    }

    public function getTabs(): array
    {
        $counts = $this->repository->countByStatus();
        $tabs = [['key' => 'all', 'label' => 'Tất cả', 'count' => array_sum($counts)]];

        foreach (ArticleStatus::cases() as $status) {
            $tabs[] = ['key' => $status->value, 'label' => $status->label(), 'count' => $counts[$status->value] ?? 0];
        }

        return $tabs;
    }

    private function saveTranslations(Article $article, array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['title'])) continue;

            $data['slug'] = $this->generateUniqueSlug(
                translationTable: 'article_translations',
                locale: $locale,
                slug: $data['slug'] ?? null,
                title: $data['title'],
                ignoreForeignId: $article->id,
                foreignKey: 'article_id'
            );

            unset($data['meta_title'], $data['meta_description'], $data['meta_keywords']);
            $article->translations()->updateOrCreate(['locale' => $locale], $data);
        }

        $article->saveSeoTranslations($translations);
    }
}
```

---

### BƯỚC 6 — Form Requests (`app/Http/Requests/Admin/Article/`)

> **Lưu ý:** Kế thừa `Illuminate\Foundation\Http\FormRequest`.
> Bắt buộc dùng `prepareForValidation()` để bảo vệ trường `author_id` (chỉ admin/super-admin mới được chỉ định tác giả khác).

```php
namespace App\Http\Requests\Admin\Article;

use App\Core\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if ($user && !$user->hasRole(['admin', 'super-admin'])) {
            $this->merge(['author_id' => $user->id]);
        }
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'status'         => ['required', new Enum(ArticleStatus::class)],
            'author_id'      => ['nullable', 'exists:users,id'],
            'image'          => ['nullable', 'string', 'max:255'],
            'published_at'   => ['nullable', 'date'],
            'translations'   => ['required', 'array'],
            "translations.{$defaultLocale}.title" => ['required', 'string', 'max:255'],
            'translations.*.title'   => ['nullable', 'string', 'max:255'],
            'translations.*.slug'    => ['nullable', 'string', 'max:255'],
            'translations.*.excerpt' => ['nullable', 'string', 'max:1000'],
            'translations.*.content' => ['nullable', 'string'],
        ];
    }
}
```
*Tạo `UpdateArticleRequest` kế thừa `StoreArticleRequest`.*

---

### BƯỚC 7 — Controller (`app/Http/Controllers/Admin/ArticleController.php`)

```php
namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\Core\Enums\ArticleStatus;
use App\DTOs\Article\ArticleDTO;
use App\Http\Requests\Admin\Article\StoreArticleRequest;
use App\Http\Requests\Admin\Article\UpdateArticleRequest;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Services\Admin\Article\ArticleService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ArticleController extends BaseController
{
    public function __construct(
        private readonly ArticleService $service,
        private readonly ArticleRepositoryInterface $repository,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {}

    public function index(BulkActionRegistry $bulkRegistry): View
    {
        return view('admin.articles.index', [
            'articles'    => $this->service->getList(request()->all()),
            'tabs'        => $this->service->getTabs(),
            'tab'         => request('tab', 'all'),
            'statuses'    => collect(ArticleStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
            'bulkActions' => $bulkRegistry->getActionOptions('articles'),
        ]);
    }

    public function create(): View
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLocale = $activeLanguages->firstWhere('is_default', true)?->code ?? app()->getLocale();

        return view('admin.articles.form', [
            'statuses'        => collect(ArticleStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
            'authors'         => \App\Models\User::active()->get(),
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $article = $this->service->create(ArticleDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.articles.edit', $article->uuid)->with('success', 'Thêm mới thành công.');
        }

        return redirect()->route('admin.articles.index')->with('success', 'Thêm mới thành công.');
    }

    public function edit(string $uuid): View
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLocale = $activeLanguages->firstWhere('is_default', true)?->code ?? app()->getLocale();

        return view('admin.articles.form', [
            'article'         => $this->repository->findByUuid($uuid, ['*'], ['translations']),
            'statuses'        => collect(ArticleStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
            'authors'         => \App\Models\User::active()->get(),
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ]);
    }

    public function update(UpdateArticleRequest $request, string $uuid): RedirectResponse
    {
        $article = $this->service->update($uuid, ArticleDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.articles.edit', $article->uuid)->with('success', 'Cập nhật thành công.');
        }

        return redirect()->route('admin.articles.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->service->delete($uuid);
        return redirect()->route('admin.articles.index')->with('success', 'Xóa thành công.');
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch('articles', $request->input('action'), $request->input('ids'));
        return back()->with('success', 'Thao tác hàng loạt thành công.');
    }
}
```

---

### BƯỚC 8 — Routes & Bulk Action Registration

#### 8.1 Routes (`routes/web.php`)
```php
Route::prefix('admin')->name('admin.')->middleware(['auth:web'])->group(function () {
    Route::post('articles/bulk', [ArticleController::class, 'bulk'])->name('articles.bulk');
    Route::resource('articles', ArticleController::class)->except(['show']);
});
```

#### 8.2 Bulk Actions (`app/Providers/BulkActionServiceProvider.php`)
```php
$registry->register('articles', 'delete', 'Xóa đã chọn', function (array $ids) {
    app(ArticleRepositoryInterface::class)->deleteManyByIds($ids);
});
```

---

### BƯỚC 9 — Views (Chuẩn Bootstrap 5.3)

#### 9.1 Trang danh sách (`resources/views/admin/articles/index.blade.php`)
```blade
@extends('layouts.admin')
@section('title', 'Quản lý Bài viết')

@section('content')
    <x-admin.page-header title="Danh sách Bài viết" subtitle="Quản lý toàn bộ bài viết trên hệ thống">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.articles.create') }}" variant="primary">
                + Thêm mới
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Tabs lọc trạng thái --}}
    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.articles.index" />

    {{-- Bulk Actions + Search Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto" size="sm">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                @foreach($bulkActions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" size="sm">
                {{ __('Áp dụng') }}
            </x-admin.button>
        </div>

        <form action="{{ route('admin.articles.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm kiếm tiêu đề, slug..." size="sm" />
            <x-admin.button type="submit" variant="primary" size="sm">Lọc</x-admin.button>
        </form>
    </div>

    {{-- Form bulk ẩn phục vụ submit JS --}}
    <form id="form-bulk-action" action="{{ route('admin.articles.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="articles">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    {{-- Bảng dữ liệu --}}
    <x-admin.table :paginator="$articles">
        <x-slot:head>
            <x-admin.table-th align="center" padding="text-center" width="40px">
                <input type="checkbox" id="check-all" class="form-check-input">
            </x-admin.table-th>
            <x-admin.table-th>Tiêu đề</x-admin.table-th>
            <x-admin.table-th>Trạng thái</x-admin.table-th>
            <x-admin.table-th>Tác giả</x-admin.table-th>
            <x-admin.table-th>Ngày tạo</x-admin.table-th>
        </x-slot:head>

        @forelse($articles as $article)
            @php
                $title = $article->translate(app()->getLocale())?->title ?? '---';
                $slug = $article->translate(app()->getLocale())?->slug ?? '';
                $actions = [
                    ['label' => 'Sửa', 'route' => route('admin.articles.edit', $article->uuid), 'color' => 'blue'],
                    [
                        'label'         => 'Xóa',
                        'route'         => route('admin.articles.destroy', $article->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => 'Xóa bài viết?',
                        'confirm_text'  => 'Bạn có chắc muốn xóa bài viết này không?',
                        'confirm_btn'   => 'Xóa ngay'
                    ],
                ];
            @endphp
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="ids[]" value="{{ $article->id }}" class="row-checkbox form-check-input">
                </td>
                <td>
                    <x-admin.table-cell-primary :title="$title" :subtitle="'/' . $slug" :actions="$actions" />
                </td>
                <td>
                    <x-admin.badge :label="$article->status" variant="info" />
                </td>
                <td class="small text-muted">{{ $article->author?->name ?? '---' }}</td>
                <td class="small text-muted">{{ $article->created_at?->format('d/m/Y') ?? '---' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">Không tìm thấy bản ghi nào.</td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
```

#### 9.2 Trang Form Tạo/Sửa (`resources/views/admin/articles/form.blade.php`)
```blade
@extends('layouts.admin')
@section('title', isset($article) ? 'Sửa bài viết' : 'Thêm bài viết mới')

@section('content')
<form action="{{ isset($article) ? route('admin.articles.update', $article->uuid) : route('admin.articles.store') }}"
      method="POST" class="needs-validation">
    @csrf
    @if(isset($article)) @method('PUT') @endif

    <div class="row g-4">
        {{-- Cột trái (col-lg-9 col-md-8): Dữ liệu đa ngôn ngữ & nội dung chính --}}
        <div class="col-lg-9 col-md-8">
            <x-admin.card title="Nội dung bài viết" class="mb-4">
                <x-admin.lang-tabs :locales="$activeLanguages" :defaultLocale="$defaultLocale">
                    @foreach($activeLanguages as $lang)
                        @php
                            $trans = isset($article) ? $article->translate($lang->code) : null;
                        @endphp
                        <div class="tab-pane fade {{ $lang->code === $defaultLocale ? 'show active' : '' }}"
                             id="lang-{{ $lang->code }}" role="tabpanel">

                            <x-admin.form-group label="Tiêu đề" name="translations[{{ $lang->code }}][title]" :required="$lang->code === $defaultLocale">
                                <x-admin.input type="text" name="translations[{{ $lang->code }}][title]"
                                    value="{{ old('translations.'.$lang->code.'.title', $trans?->title ?? '') }}" />
                            </x-admin.form-group>

                            <x-admin.form-group label="Đường dẫn (Slug)" name="translations[{{ $lang->code }}][slug]" description="Để trống hệ thống sẽ tự sinh slug chuẩn URL">
                                <x-admin.input type="text" name="translations[{{ $lang->code }}][slug]"
                                    value="{{ old('translations.'.$lang->code.'.slug', $trans?->slug ?? '') }}" />
                            </x-admin.form-group>

                            <x-admin.form-group label="Mô tả ngắn" name="translations[{{ $lang->code }}][excerpt]">
                                <x-admin.textarea name="translations[{{ $lang->code }}][excerpt]" rows="3">
                                    {{ old('translations.'.$lang->code.'.excerpt', $trans?->excerpt ?? '') }}
                                </x-admin.textarea>
                            </x-admin.form-group>

                            <x-admin.form-group label="Nội dung chi tiết" name="translations[{{ $lang->code }}][content]">
                                <x-admin.textarea name="translations[{{ $lang->code }}][content]" rows="10" class="rich-editor">
                                    {{ old('translations.'.$lang->code.'.content', $trans?->content ?? '') }}
                                </x-admin.textarea>
                            </x-admin.form-group>
                        </div>
                    @endforeach
                </x-admin.lang-tabs>
            </x-admin.card>

            {{-- Component SEO tập trung --}}
            <x-admin.seo-meta :model="$article ?? null" :locales="$activeLanguages" :defaultLocale="$defaultLocale" />
        </div>

        {{-- Cột phải (col-lg-3 col-md-4): Trạng thái, ảnh đại diện, nút lưu --}}
        <div class="col-lg-3 col-md-4">
            <x-admin.card title="Xuất bản" class="mb-4">
                <x-admin.form-group label="Trạng thái" name="status" class="mb-3">
                    <x-admin.select name="status">
                        @foreach($statuses as $val => $lbl)
                            <option value="{{ $val }}" {{ old('status', $article->status ?? 'draft') === $val ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </x-admin.select>
                </x-admin.form-group>

                @hasrole('admin|super-admin')
                <x-admin.form-group label="Tác giả" name="author_id" class="mb-3">
                    <x-admin.select name="author_id">
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}" {{ old('author_id', $article->author_id ?? auth()->id()) == $author->id ? 'selected' : '' }}>
                                {{ $author->name }}
                            </option>
                        @endforeach
                    </x-admin.select>
                </x-admin.form-group>
                @endhasrole

                <hr class="my-3">

                <div class="d-flex flex-column gap-2">
                    <x-admin.button type="submit" name="submit_action" value="save" variant="primary" class="w-100">
                        Lưu thay đổi
                    </x-admin.button>
                    <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="outline" class="w-100">
                        Lưu & Tiếp tục sửa
                    </x-admin.button>
                    <x-admin.button href="{{ route('admin.articles.index') }}" variant="secondary" class="w-100">
                        Quay lại
                    </x-admin.button>
                </div>
            </x-admin.card>

            <x-admin.card title="Ảnh đại diện">
                <x-admin.media-picker name="image" :value="old('image', $article->image ?? '')" />
            </x-admin.card>
        </div>
    </div>
</form>
@endsection
```

---

## 6. QUY TRÌNH THÊM 1 TRƯỜNG DỮ LIỆU MỚI

Khi bổ sung trường mới, phải xác định trường đó thuộc **Bảng Chính (dữ liệu chung)** hay **Bảng Bản Dịch (`_translations`)**:

1. **Migration & Model:**
   - Tạo migration thêm cột vào bảng tương ứng (`articles` hoặc `article_translations`).
   - Khai báo cột vào mảng `$fillable` của Model tương ứng.
2. **DTO:**
   - Nếu là trường chung: Khai báo property trong Constructor, lấy qua `$request->input('...')` trong `fromRequest()`, đưa vào `toArray()`.
   - Nếu là trường đa ngữ: Nằm tự động trong mảng `translations` của DTO.
3. **FormRequest:**
   - Thêm rule validate vào `rules()` (VD: `author_id` ở bảng chính hoặc `translations.*.new_field`).
4. **Service:**
   - Nếu là trường chung: Tự động ghi vào DB qua `$dto->toArray()`.
   - Nếu là trường đa ngữ: Cập nhật trong hàm `saveTranslations()`.
5. **View:**
   - Thêm `<x-admin.form-group>` vào `form.blade.php` (nếu là trường đa ngữ thì đặt bên trong vòng lặp `$activeLanguages`).

---

## 7. QUY TẮC VIẾT TỪNG LỚP (LAYER RULES)

- **Controller:**
  - Inject Service & Repository qua `__construct(private readonly ...)`.
  - Tham số route nhận `string $uuid`.
  - Xử lý redirect dựa theo `submit_action` (`save` hoặc `save_and_edit`).
- **FormRequest:**
  - Form Admin luôn kế thừa `Illuminate\Foundation\Http\FormRequest`.
  - Validate trạng thái bằng Enum: `['required', new Enum(PostStatus::class)]`.
  - Sử dụng `prepareForValidation()` để chuẩn hóa/bảo vệ dữ liệu trước khi validate.
- **DTO:**
  - Luôn khai báo `class` với các thuộc tính `public readonly`.
  - Hàm tạo tĩnh `fromRequest(Request $request): self`.
- **Service:**
  - Luôn kế thừa `BaseService`.
  - Bọc các lệnh ghi DB liên quan trong `$this->handleTransaction(...)`.
  - Luôn gọi `$this->generateUniqueSlug(...)` để làm sạch và chống trùng slug.
- **Repository:**
  - Kế thừa `BaseRepository`, implement Interface tương ứng.
  - Sử dụng Eager Loading `with([...])` để ngăn chặn triệt để lỗi N+1 Query.

---

## 8. QUY CHUẨN GIAO DIỆN (BOOTSTRAP 5.3) & DANH MỤC BLADE COMPONENT

Dự án sử dụng **Bootstrap 5.3 + SCSS**. Bố cục Form chuẩn là lưới 12 cột: `col-lg-9 col-md-8` (cột chính) và `col-lg-3 col-md-4` (cột phụ).

### Danh mục Component chuẩn trong `resources/views/components/admin/`:

| Tên Component | Cú pháp gọi | Mục đích sử dụng | Props chính |
|---|---|---|---|
| **Page Header** | `<x-admin.page-header>` | Tiêu đề đầu trang + nút hành động | `title`, `subtitle`, `breadcrumbs`, slot `actions` |
| **Card** | `<x-admin.card>` | Khung bọc khối giao diện | `title`, `class` |
| **Form Group** | `<x-admin.form-group>` | Bọc label + input + mô tả + lỗi | `label`, `name`, `description`, `required` |
| **Input** | `<x-admin.input>` | Ô nhập liệu text, email, password... | `type`, `name`, `value`, `placeholder`, `size` |
| **Textarea** | `<x-admin.textarea>` | Ô nhập văn bản nhiều dòng | `name`, `rows`, `class` |
| **Select** | `<x-admin.select>` | Dropdown chọn lựa chọn | `name`, `size`, slot `<option>` |
| **Toggle Switch** | `<x-admin.toggle>` | Công tắc bật/tắt on-off dạng switch | `name`, `checked`, `label` |
| **Button** | `<x-admin.button>` | Nút bấm hoặc liên kết dạng nút | `variant` (primary, secondary, outline...), `href`, `type`, `size` |
| **Badge** | `<x-admin.badge>` | Nhãn hiển thị trạng thái | `label`, `color` (green, amber, gray, red...) hoặc `variant` |
| **Table Container**| `<x-admin.table>` | Khung bảng dữ liệu + phân trang | `paginator`, slot `head`, slot mặc định `<tr>` |
| **Table Header** | `<x-admin.table-th>` | Thẻ `<th>` tiêu đề cột trong bảng | `align`, `padding`, `width` |
| **Table Cell Primary** | `<x-admin.table-cell-primary>` | Cột hiển thị chính (Ảnh + Tiêu đề + Actions hover) | `title`, `subtitle`, `image`, `actions` |
| **Row Actions** | `<x-admin.row-actions>` | Bộ nút Sửa / Xóa (tích hợp confirm) | `actions` (mảng cấu hình action) |
| **Filter Tabs** | `<x-admin.filter-tabs>` | Hàng tab đếm số lượng theo status | `items`, `current`, `route` |
| **Language Tabs**| `<x-admin.lang-tabs>` | Tabs chuyển đổi ngôn ngữ trong Form | `locales`, `defaultLocale`, slot mặc định |
| **SEO Meta** | `<x-admin.seo-meta>` | Cụm form SEO on-page + Schema + Preview | `model`, `locales`, `defaultLocale` |
| **Media Picker** | `<x-admin.media-picker>` | Chọn ảnh từ Media Library tập trung | `name`, `value`, `placeholder` |
| **Tree Checkbox** | `<x-admin.tree-checkbox>`| Cây chọn danh mục phân cấp | `name`, `options`, `selected`, `type` |
| **Modal** | `<x-admin.modal>` | Hộp thoại Bootstrap Modal | `id`, `title`, slot `footer` |

---

## 9. CHECKLIST KIỂM TRA TRƯỚC KHI HOÀN THÀNH

Trước khi hoàn thành bất kỳ tính năng hoặc module nào, hãy tự kiểm tra:

### 1. Kiến trúc & Logic
- [ ] Controller không chứa query DB hoặc logic nghiệp vụ rẽ nhánh.
- [ ] Service kế thừa `BaseService`, inject `RepositoryInterface` (không inject class).
- [ ] Bọc các thao tác ghi dữ liệu nhiều bảng trong `$this->handleTransaction(...)`.
- [ ] Gọi `$this->generateUniqueSlug(...)` khi lưu bản dịch bài viết/danh mục/trang.
- [ ] Sử dụng `Enum` cho các trạng thái, không hardcode string.
- [ ] Dùng `uuid` làm định danh route ngoài view/URL, không dùng `id`.

### 2. FormRequest & Bảo mật
- [ ] FormRequest cho Admin kế thừa `Illuminate\Foundation\Http\FormRequest`.
- [ ] Áp dụng `prepareForValidation()` để bảo vệ các trường nhạy cảm (`author_id`).
- [ ] Kiểm tra phân quyền trong hàm `authorize()`.

### 3. Giao diện & Blade
- [ ] Sử dụng 100% class **Bootstrap 5.3** (không có class Tailwind).
- [ ] Form dùng chung 1 file `form.blade.php` cho cả create và edit.
- [ ] Bọc toàn bộ input/select trong `<x-admin.form-group>`.
- [ ] Xóa bản ghi dùng cơ chế toàn cục `form-confirm` hoặc mảng `actions` của `<x-admin.row-actions>`, không viết script SweetAlert2 thủ công.
- [ ] Khai báo thao tác hàng loạt qua `BulkActionRegistry`.
