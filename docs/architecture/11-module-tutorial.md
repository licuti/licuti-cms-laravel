# 11 — Quy trình xây dựng 1 MODULE MỚI từ A→Z

> **Ví dụ:** Xây dựng module `Article` (Bài viết / Tin tức). Trong thực tế dự án đang dùng module `Post` với cấu trúc tương đương — bạn có thể thay thế `Article` → `Post` để so sánh.

---

## BƯỚC 1 — Migrations & Models

### 1.1 Migration (`database/migrations/`)

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

### 1.2 Model Chính (`app/Models/Article.php`)

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

## BƯỚC 2 — Enum Trạng thái (`app/Core/Enums/ArticleStatus.php`)

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

## BƯỚC 3 — Repository Interface & Implementation

### 3.1 Interface (`app/Repositories/Interfaces/ArticleRepositoryInterface.php`)

```php
namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface extends BaseRepositoryInterface
{
    public function getFiltered(array $filters): LengthAwarePaginator;
    public function countByStatus(): array;
}
```

### 3.2 Implementation (`app/Repositories/Eloquent/ArticleRepository.php`)

> ⚠️ Đặt tại `app/Repositories/Eloquent/`, **không phải** `app/Repositories/`.

```php
namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Repositories\BaseRepository;
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

    public function deleteByIds(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
```

### 3.3 Đăng ký Binding (`app/Providers/RepositoryServiceProvider.php`)

```php
$this->app->bind(
    \App\Repositories\Interfaces\ArticleRepositoryInterface::class,
    \App\Repositories\Eloquent\ArticleRepository::class
);
```

---

## BƯỚC 4 — DTO (`app/DTOs/Article/ArticleDTO.php`)

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

## BƯỚC 5 — Service (`app/Services/Admin/Article/ArticleService.php`)

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

## BƯỚC 6 — Form Requests (`app/Http/Requests/Admin/Article/`)

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

## BƯỚC 7 — Controller (`app/Http/Controllers/Admin/ArticleController.php`)

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

## BƯỚC 8 — Routes & Bulk Action Registration

### 8.1 Routes (`routes/web.php`)

```php
Route::prefix('admin')->name('admin.')->middleware(['auth:web'])->group(function () {
    Route::post('articles/bulk', [ArticleController::class, 'bulk'])->name('articles.bulk');
    Route::resource('articles', ArticleController::class)->except(['show']);
});
```

### 8.2 Bulk Actions (`app/Providers/BulkActionServiceProvider.php`)

```php
$registry->register('articles', 'delete', 'Xóa đã chọn', function (array $ids) {
    app(ArticleRepositoryInterface::class)->deleteByIds($ids);
});
```

---

## BƯỚC 9 — Views (Chuẩn Bootstrap 5.3)

### 9.1 Trang danh sách (`resources/views/admin/articles/index.blade.php`)

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

### 9.2 Trang Form Tạo/Sửa (`resources/views/admin/articles/form.blade.php`)

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
                                <x-admin.textarea name="translations[{{ $lang->code }}][content]" rows="10" class="tinymce-editor">
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

        {{-- Cột phải (col-lg-3 col-md-4): Xuất bản + ảnh đại diện --}}
        <div class="col-lg-3 col-md-4">
            {{-- publish-box chuẩn: status select + nút Lưu / Lưu & Sửa / Quay lại
                 (thêm :show-published-at="true" :published-at="$article->published_at" nếu module có lịch đăng,
                  :show-featured="true" nếu có is_featured) --}}
            <x-admin.publish-box
                :statuses="$statuses"
                :status="$article?->status?->value"
                :index-route="route('admin.articles.index')"
            />

            <x-admin.card title="Ảnh đại diện" class="mt-4">
                <x-admin.media-picker name="image" :value="old('image', $article->image ?? '')" />
            </x-admin.card>
        </div>
    </div>
</form>
@endsection
```

> Nếu form có trường nội dung dạng rich-text: đã thêm `class="tinymce-editor"` ở textarea,
> đặt `<x-admin.scripts.tinymce />` **một lần duy nhất** cuối form để nạp editor
> (component tự lo khởi tạo, đổi theme theo dark/light mode và `triggerSave` khi submit).
> Chi tiết xem [13-ui-conventions.md](13-ui-conventions.md).

---

## So sánh với module `Post` thực tế

Module `Article` ở trên là ví dụ minh họa. Dự án hiện tại đã có sẵn module **`Post`** với cấu trúc tương đương. Khi làm việc với `Post`, thay thế:

| Article (demo) | Post (thực tế) |
|---|---|
| `App\Models\Article` | `App\Models\Post` |
| `articles` table | `posts` table |
| `article_translations` | `post_translations` |
| `ArticleController` | `PostController` (xem `app/Http/Controllers/Admin/PostController.php`) |
| `ArticleStatus` enum | `ContentStatus` enum |
| `ArticleRepository` | `PostRepository` (`app/Repositories/Eloquent/PostRepository.php`) |

So sánh trực tiếp 2 file để thấy cách áp dụng chuẩn vào module thật.