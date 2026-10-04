<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductAttributeValue;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ProductAttributeValueRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductAttributeValueRepository extends BaseRepository implements ProductAttributeValueRepositoryInterface
{
    public function __construct(ProductAttributeValue $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginatedByAttribute(int $attributeId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->where('attribute_id', $attributeId)->with('translations');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('value', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('display_order')->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function findByUuidWithRelations(string $uuid)
    {
        return $this->model->with('translations')->where('uuid', $uuid)->firstOrFail();
    }

    public function findByText(int $attributeId, string $locale, string $value): ?ProductAttributeValue
    {
        return $this->model->where('attribute_id', $attributeId)
            ->whereHas('translations', function ($q) use ($locale, $value) {
                $q->where('locale', $locale)->where('value', $value);
            })->first();
    }
}
