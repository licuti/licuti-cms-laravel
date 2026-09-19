<?php

namespace App\Repositories\Eloquent;

use App\Models\Menu;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\MenuRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuRepository extends BaseRepository implements MenuRepositoryInterface
{
    public function __construct(Menu $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['items']);

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('slug', 'like', $search);
            });
        }

        if (!empty($filters['location'])) {
            $query->where('location', $filters['location']);
        }

        return $query->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByUuidWithRelations(string $uuid): ?Menu
    {
        return $this->model->with(['items'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Lấy menu theo uuid kèm toàn bộ items (dùng cho form quản trị).
     */
    public function getWithItems(string $uuid): ?Menu
    {
        return $this->findByUuidWithRelations($uuid);
    }

    /**
     * Lấy menu kèm items theo vị trí (dùng cho frontend/theme).
     */
    public function getByLocation(string $location): ?Menu
    {
        return $this->model->with(['items'])
            ->where('location', $location)
            ->where('is_active', true)
            ->first();
    }
}
