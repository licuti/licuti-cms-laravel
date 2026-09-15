<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;

interface PostCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllForList(array $filters = []);
    public function getAllActive();
}
