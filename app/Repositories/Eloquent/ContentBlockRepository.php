<?php

namespace App\Repositories\Eloquent;

use App\Models\ContentBlock;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ContentBlockRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ContentBlockRepository extends BaseRepository implements ContentBlockRepositoryInterface
{
    public function __construct(ContentBlock $model)
    {
        parent::__construct($model);
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['translations', 'imageMedia']);

        if (! empty($filters['tab']) && $filters['tab'] !== 'all') {
            $query->where('status', $filters['tab']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($query) use ($keyword) {
                $query->where('uuid', 'like', "%{$keyword}%")
                    ->orWhereHas('translations', function ($translationQuery) use ($keyword) {
                        $translationQuery
                            ->where('title', 'like', "%{$keyword}%")
                            ->orWhere('slug', 'like', "%{$keyword}%");
                    });
            });
        }

        return $query->latest()->paginate(15)->withQueryString();
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
