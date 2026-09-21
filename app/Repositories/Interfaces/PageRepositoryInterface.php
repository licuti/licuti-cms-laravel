<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

interface PageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Danh sách trang dạng cây phân cấp (có depth + has_children), kèm lọc tab/status/keyword.
     */
    public function getTreeList(array $filters): Collection;

    /**
     * Cập nhật trạng thái nhiều trang (bulk action).
     */
    public function updateStatusByIds(array $ids, string $status): int;

    /**
     * Xóa mềm nhiều trang (bulk action).
     */
    public function deleteByIds(array $ids): int;

    /**
     * Đếm số trang theo từng trạng thái (cho tabs).
     */
    public function countByStatus(): array;

    /**
     * Build cây trang phân cấp (dưới dạng Collection có thuộc tính _children).
     * excludeId: loại trừ trang đang chỉnh sửa và subtree của nó khỏi danh sách
     * chọn trang cha (tránh cycle + chọn chính nó).
     */
    public function getTree(?int $excludeId = null): Collection;
}
