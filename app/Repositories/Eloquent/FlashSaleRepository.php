<?php

namespace App\Repositories\Eloquent;

use App\Enums\FlashSaleStatus;
use App\Models\FlashSale;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\FlashSaleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FlashSaleRepository extends BaseRepository implements FlashSaleRepositoryInterface
{
    public function __construct(FlashSale $model)
    {
        parent::__construct($model);
    }

    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['translations', 'creator'])
            ->withCount('products');

        $search = $filters['search'] ?? $filters['keyword'] ?? null;
        if (! empty($search)) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $status = $filters['status'] ?? null;
        if (! empty($status) && $status !== 'all') {
            $query->status($status);
        }

        return $query->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findByUuidOrFail(string $uuid): FlashSale
    {
        return $this->model->query()
            ->with([
                'translations',
                'products.product.translations',
                'products.variant',
                'creator',
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function getActiveRunning(): Collection
    {
        return $this->model->query()
            ->activeWindow()
            ->with([
                'translations',
                'products' => function ($q) {
                    $q->orderBy('sort_order');
                },
                'products.product.translations',
                'products.variant',
            ])
            ->get();
    }

    public function getStatusCounts(): array
    {
        $now = Carbon::now();

        return [
            'all'       => $this->model->count(),
            'running'   => $this->model->where('is_active', true)->where('starts_at', '<=', $now)->where('ends_at', '>=', $now)->count(),
            'scheduled' => $this->model->where('is_active', true)->where('starts_at', '>', $now)->count(),
            'ended'     => $this->model->where('is_active', true)->where('ends_at', '<', $now)->count(),
            'draft'     => $this->model->where('is_active', false)->count(),
        ];
    }

    public function deleteByIds(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
