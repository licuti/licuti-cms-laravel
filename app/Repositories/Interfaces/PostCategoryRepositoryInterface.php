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
}
