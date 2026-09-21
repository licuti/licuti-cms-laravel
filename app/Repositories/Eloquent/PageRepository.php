<?php

namespace App\Repositories\Eloquent;

use App\Models\Page;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    /**
     * Lấy danh sách page dạng cây phân cấp (có depth + children) với lọc theo tab/status.
     * Returns: Collection các items, mỗi item có thêm:
     *   - depth: số bậc cha con (0 = root)
     *   - has_children: boolean
     */
    public function getTreeList(array $filters): Collection
    {
        // Lấy full tree
        $tree = $this->getTree();

        // Lọc theo status/tab nếu có
        $statusFilter = $filters['tab'] ?? $filters['status'] ?? null;
        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $tree = $tree->filter(fn($item) => $item->status === $statusFilter);
        }

        // Lọc theo keyword
        if (!empty($filters['keyword'])) {
            $keyword = strtolower($filters['keyword']);
            $tree = $tree->filter(function($item) use ($keyword) {
                $title = strtolower($item->title ?? '');
                return str_contains($title, $keyword);
            });
        }

        // Flatten tree với depth
        $flatten = function($nodes, &$result, $depth = 0) use (&$flatten) {
            foreach ($nodes as $node) {
                $node->depth = $depth;
                $node->has_children = $node->_children->count() > 0;
                $result[] = $node;
                
                if ($node->_children->count() > 0) {
                    $flatten($node->_children, $result, $depth + 1);
                }
            }
        };

        $result = collect();
        $flatten($tree, $result);

        return $result;
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
     * Trả về cây phân cấp EloquentCollection.
     * excludeId (khi chỉnh sửa): loại trừ node đó và toàn bộ subtree của nó khỏi danh sách
     * trang cha (để tránh chọn chính mình / cycle).
     */
    public function getTree(?int $excludeId = null): EloquentCollection
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
