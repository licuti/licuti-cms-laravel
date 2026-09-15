<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    /** Lấy danh sách có lọc theo tab, status, category, keyword */
    public function getFiltered(array $filters): LengthAwarePaginator;

    /** Cập nhật trạng thái nhiều bài viết */
    public function updateStatusByIds(array $ids, string $status): int;

    /** Xóa mềm nhiều bài viết */
    public function deleteByIds(array $ids): int;

    /** Đếm số bài viết theo từng trạng thái */
    public function countByStatus(): array;
}
