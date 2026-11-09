<?php

namespace App\Repositories\Eloquent;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function findActiveByUserId(int $userId): ?Cart
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', CartStatus::ACTIVE->value)
            ->latest('id')
            ->first();
    }

    public function findActiveByUuid(string $uuid): ?Cart
    {
        return $this->model
            ->where('uuid', $uuid)
            ->where('status', CartStatus::ACTIVE->value)
            ->first();
    }

    public function findByUuidWithItems(string $uuid): ?Cart
    {
        return $this->model
            ->with([
                'coupon',
                'items.product.translations',
                'items.product.primaryImageMedia',
                'items.variant.attributeValues.attribute',
            ])
            ->where('uuid', $uuid)
            ->first();
    }

    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'coupon', 'items'])->latest('id');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage);
    }

    public function markAbandoned(Carbon $threshold): int
    {
        return $this->model
            ->where('status', CartStatus::ACTIVE->value)
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<', $threshold)
            ->update(['status' => CartStatus::ABANDONED->value]);
    }

    public function purgeStale(Carbon $guestThreshold, Carbon $userThreshold): int
    {
        $deleted = 0;

        // Guest stale carts
        $this->model->whereNull('user_id')
            ->whereIn('status', [CartStatus::ABANDONED->value, CartStatus::MERGED->value, CartStatus::CONVERTED->value])
            ->where('updated_at', '<', $guestThreshold)
            ->chunkById(100, function ($carts) use (&$deleted) {
                foreach ($carts as $cart) {
                    $cart->delete();
                    $deleted++;
                }
            });

        // User stale carts
        $this->model->whereNotNull('user_id')
            ->whereIn('status', [CartStatus::ABANDONED->value, CartStatus::MERGED->value, CartStatus::CONVERTED->value])
            ->where('updated_at', '<', $userThreshold)
            ->chunkById(100, function ($carts) use (&$deleted) {
                foreach ($carts as $cart) {
                    $cart->delete();
                    $deleted++;
                }
            });

        return $deleted;
    }

    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }
}
