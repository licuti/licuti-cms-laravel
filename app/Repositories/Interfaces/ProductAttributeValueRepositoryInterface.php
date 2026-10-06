<?php

namespace App\Repositories\Interfaces;

use App\Models\ProductAttributeValue;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductAttributeValueRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginatedByAttribute(int $attributeId, array $filters = []): LengthAwarePaginator;

    public function findByUuidWithRelations(string $uuid);

    public function findByUuidAndAttribute(string $uuid, int $attributeId): ProductAttributeValue;

    public function findByText(int $attributeId, string $locale, string $value): ?ProductAttributeValue;
}
