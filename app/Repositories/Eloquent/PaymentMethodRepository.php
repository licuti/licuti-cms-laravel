<?php

namespace App\Repositories\Eloquent;

use App\Core\Constants\CacheKey;
use App\Models\PaymentMethod;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodRepositoryInterface
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getActiveCodes(): array
    {
        return $this->getActiveMethods()->pluck('code')->toArray();
    }

    public function getActiveMethods(): Collection
    {
        try {
            if (Cache::supportsTags()) {
                return Cache::tags(['payment_methods'])->remember(
                    CacheKey::PAYMENT_METHODS_ACTIVE,
                    now()->addDay(),
                    fn () => $this->model->active()->ordered()->get()
                );
            }
        } catch (\BadMethodCallException|\Exception) {
            // Fallback khi cache store không hỗ trợ tags
        }

        return Cache::remember(
            CacheKey::PAYMENT_METHODS_ACTIVE,
            now()->addDay(),
            fn () => $this->model->active()->ordered()->get()
        );
    }

    public function clearCache(): void
    {
        try {
            if (Cache::supportsTags()) {
                Cache::tags(['payment_methods'])->flush();
            }
        } catch (\BadMethodCallException|\Exception) {
            // Fallback
        }

        Cache::forget(CacheKey::PAYMENT_METHODS_ACTIVE);
    }

    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        $search = $filters['search'] ?? $filters['keyword'] ?? null;
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $status = $filters['status'] ?? $filters['is_active'] ?? null;
        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        return $query->orderBy('display_order', 'asc')->latest('id')->paginate($perPage);
    }

    public function updateStatusByIds(array $ids, bool $isActive): int
    {
        $updated = $this->model->whereIn('id', $ids)->update(['is_active' => $isActive]);
        $this->clearCache();
        return $updated;
    }

    public function deleteByIds(array $ids): int
    {
        $deleted = $this->model->whereIn('id', $ids)->delete();
        $this->clearCache();
        return $deleted;
    }

    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                $this->model->where('id', $id)->update(['display_order' => $index + 1]);
            }
        });

        $this->clearCache();
    }
}
