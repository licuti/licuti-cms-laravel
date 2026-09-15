<?php

namespace App\Repositories\Eloquent;

use App\Models\Page;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with('translations');

        // Lọc theo tab (all | published | draft | archived)
        if (!empty($filters['tab']) && $filters['tab'] !== 'all') {
            $query->where('status', $filters['tab']);
        }

        // Lọc theo trạng thái (khi người dùng chọn qua GET ?status=)
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        // Lọc theo chuyên mục/parent nếu có
        if (!empty($filters['parent'])) {
            $query->where('parent_id', $filters['parent']);
        }

        // Tìm theo tiêu đề trong translations
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('translations', function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(5, min(100, $perPage));

        return $query->orderBy('display_order')->latest()->paginate($perPage)->withQueryString();
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

    /**
     * Trả về cây phân cấp Collection.
     * excludeId (khi chỉnh sửa): loại trừ node đó và toàn bộ subtree của nó khỏi danh sách
     * trang cha (để tránh chọn chính mình / cycle).
     */
    public function getTree(?int $excludeId = null): Collection
    {
        $all = $this->model
            ->with('translations')
            ->when($excludeId ? $this->collectSubtreeIds($excludeId) : [], fn($q, $ids) => $q->whereNotIn('id', $ids))
            ->orderBy('display_order')
            ->get();

        $buildBranch = function (?int $parentId) use ($all, &$buildBranch) {
            return $all
                ->filter(fn ($item) => (int) $item->parent_id === (int) $parentId)
                ->map(function ($item) use (&$buildBranch) {
                    $item->_children = $buildBranch($item->id);
                    return $item;
                })
                ->values();
        };

        return $buildBranch(null);
    }

    /** Thu thập toàn bộ ID subtree (chính nó + descendants) */
    private function collectSubtreeIds(int $rootId): array
    {
        $all = $this->model->get(['id', 'parent_id']);
        $result = [$rootId];
        $queue = [$rootId];

        while ($queue) {
            $current = array_shift($queue);
            $children = $all->where('parent_id', $current);
            foreach ($children as $child) {
                $result[] = (int) $child->id;
                $queue[] = (int) $child->id;
            }
        }

        return $result;
    }
}
