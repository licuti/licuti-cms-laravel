<?php

namespace App\Repositories\Eloquent;

use App\Models\Banner;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class BannerRepository extends BaseRepository implements BannerRepositoryInterface
{
    public function __construct(Banner $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['translations.imageMedia']);

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('link', 'like', $search)
                  ->orWhereHas('translations', function ($tq) use ($search) {
                      $tq->where('title', 'like', $search);
                  });
            });
        }

        if (!empty($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->orderBy('display_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByUuidWithRelations(string $uuid): ?Banner
    {
        return $this->model->with(['translations.imageMedia'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Lấy các banner đang hoạt động theo vị trí (dùng cho frontend/theme).
     */
    public function getActiveByPosition(string $position)
    {
        return $this->model->with(['translations.imageMedia'])
            ->where('is_active', true)
            ->where('position', $position)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();
    }
}
