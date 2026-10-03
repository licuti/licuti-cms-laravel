<?php

namespace App\Repositories\Interfaces;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductAttributeRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuidWithRelations(string $uuid): ?ProductAttribute;

    public function getActiveWithValues();

    public function getAvailableForProduct(int $productId);

    public function findValueByText(int $attributeId, string $value): ?ProductAttributeValue;

    public function createValue(int $attributeId, string $value, ?string $colorCode = null): ProductAttributeValue;

    public function clearCache(): void;
}
