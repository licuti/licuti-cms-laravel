<?php

namespace App\Repositories\Interfaces;

use App\Models\ProductVariant;

interface ProductVariantRepositoryInterface extends BaseRepositoryInterface
{
    public function lockForUpdateFind(int $id): ?ProductVariant;
}
