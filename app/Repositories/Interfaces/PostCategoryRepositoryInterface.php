<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;

interface PostCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllForList(array $filters = []);
    public function getAllActive();

    /**
     * Trả về toàn bộ danh mục dưới dạng cây phân cấp (có thuộc tính _children).
     * Single query, build tree in-memory.
     */
    public function getTree(): \Illuminate\Support\Collection;

    /**
     * Đếm nhanh tổng số / số đang kích hoạt / số đã ẩn để hiển thị chip thống kê.
     *
     * @return array{total:int,active:int,inactive:int}
     */
    public function getStats(): array;
}
