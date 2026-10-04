<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface ProductAttributeValueRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginatedByAttribute(int $attributeId, array $filters = []): LengthAwarePaginator;
    public function findByUuidWithRelations(string $uuid);
    public function findByText(int $attributeId, string $locale, string $value): ?\App\Models\ProductAttributeValue;
}
