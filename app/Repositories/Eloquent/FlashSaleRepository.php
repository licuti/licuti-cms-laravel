<?php

namespace App\Repositories\Eloquent;

use App\Enums\FlashSaleStatus;
use App\Models\FlashSale;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\FlashSaleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

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
        return Cache::remember('flash_sales:status_counts', 60, function () {
            $now = Carbon::now()->toDateTimeString();

            $raw = $this->model->newQuery()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN is_active = 1 AND starts_at <= ? AND ends_at >= ? THEN 1 ELSE 0 END) as running', [$now, $now])
                ->selectRaw('SUM(CASE WHEN is_active = 1 AND starts_at > ? THEN 1 ELSE 0 END) as scheduled', [$now])
                ->selectRaw('SUM(CASE WHEN is_active = 1 AND ends_at < ? THEN 1 ELSE 0 END) as ended', [$now])
                ->selectRaw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as draft')
                ->first();

            return [
                'all'       => (int) ($raw?->total ?? 0),
                'running'   => (int) ($raw?->running ?? 0),
                'scheduled' => (int) ($raw?->scheduled ?? 0),
                'ended'     => (int) ($raw?->ended ?? 0),
                'draft'     => (int) ($raw?->draft ?? 0),
            ];
        });
    }

    public function deleteByIds(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
