<?php

namespace App\Repositories\Interfaces;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    /** Lấy danh sách có lọc theo tab, status, category, keyword */
    public function getFiltered(array $filters): LengthAwarePaginator;

    public function getPublished(int $perPage, string $locale, string $fallbackLocale): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug, string $locale, string $fallbackLocale): ?Post;

    /** Lấy danh sách bài viết liên quan đã xuất bản (ưu tiên cùng chuyên mục) */
    public function getRelatedPublished(Post $post, int $limit, string $locale, string $fallbackLocale): Collection;

    /** Cập nhật trạng thái nhiều bài viết */
    public function updateStatusByIds(array $ids, string $status): int;

    /** Xóa mềm nhiều bài viết */
    public function deleteByIds(array $ids): int;

    /** Đếm số bài viết theo từng trạng thái */
    public function countByStatus(): array;
}
