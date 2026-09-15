<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Page;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('translations');

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', (bool)$filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('translations', function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%{$keyword}%")
                  ->orWhere('slug', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->orderBy('display_order')->latest()->paginate($perPage);
    }
}
