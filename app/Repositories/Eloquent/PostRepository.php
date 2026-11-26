<?php

namespace App\Repositories\Eloquent;

use App\Core\Enums\ContentStatus;
use App\Models\Post;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['translations', 'categories.translations', 'author', 'imageMedia']);

        // Lọc theo tab (all | published | draft | archived)
        // 'tab' là filter duy nhất cho status — controller chỉ truyền 1 trong 2
        if (! empty($filters['tab']) && $filters['tab'] !== 'all') {
            $query->where('status', $filters['tab']);
        }

        // Lọc theo danh mục
        if (! empty($filters['category'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('post_categories.id', $filters['category']);
            });
        }

        // Tìm theo tiêu đề hoặc slug (mọi locale)
        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('translations', function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(5, min(100, $perPage)); // giới hạn 5-100

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getPublished(int $perPage, string $locale, string $fallbackLocale): LengthAwarePaginator
    {
        $locales = array_values(array_unique([$locale, $fallbackLocale]));

        return $this->model
            ->where('status', ContentStatus::PUBLISHED->value)
            ->with([
                'translations' => fn ($query) => $query->whereIn('locale', $locales),
                'categories.translations',
                'author',
                'imageMedia',
            ])
            ->latest('published_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug, string $locale, string $fallbackLocale): ?Post
    {
        foreach (array_values(array_unique([$locale, $fallbackLocale])) as $translationLocale) {
            $post = $this->model
                ->where('status', ContentStatus::PUBLISHED->value)
                ->whereHas('translations', function ($query) use ($slug, $translationLocale) {
                    $query->where('locale', $translationLocale)->where('slug', $slug);
                })
                ->with([
                    'translations' => fn ($query) => $query->whereIn('locale', array_values(array_unique([$locale, $fallbackLocale]))),
                    'categories.translations',
                    'author',
                    'imageMedia',
                ])
                ->first();

            if ($post) {
                return $post;
            }
        }

        return null;
    }

    public function getRelatedPublished(Post $post, int $limit, string $locale, string $fallbackLocale): Collection
    {
        if ($limit <= 0) {
            return new Collection;
        }

        $locales = array_values(array_unique([$locale, $fallbackLocale]));
        $categoryIds = $post->categories->pluck('id')->all();

        $query = $this->model
            ->where('status', ContentStatus::PUBLISHED->value)
            ->where('id', '!=', $post->id)
            ->with([
                'translations' => fn ($q) => $q->whereIn('locale', $locales),
                'categories.translations',
                'imageMedia',
            ]);

        if ($categoryIds !== []) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('post_categories.id', $categoryIds));
        }

        return $query
            ->latest('published_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function updateStatusByIds(array $ids, string $status): int
    {
        return $this->model->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function deleteByIds(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }
}
