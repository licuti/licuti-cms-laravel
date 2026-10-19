<?php

namespace App\Repositories\Eloquent;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getFilteredPaginated([], $perPage);
    }

    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['user', 'paymentMethod', 'shippingAddress', 'items'])
            ->latest('id');

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['payment_method_id'])) {
            $query->where('payment_method_id', $filters['payment_method_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model->newQuery()
            ->with(['user', 'paymentMethod', 'shippingAddress', 'items', 'statusHistories.changedBy', 'payments'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function findByUuidWithRelations(string $uuid): Order
    {
        return $this->model->newQuery()
            ->with(['user', 'paymentMethod', 'shippingAddress', 'items.product', 'statusHistories.changedBy', 'payments'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function getStatusCounts(): array
    {
        $rawCounts = $this->model->newQuery()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $allCount = $this->model->newQuery()->count();

        $result = ['all' => $allCount];
        foreach (OrderStatus::cases() as $case) {
            $result[$case->value] = $rawCounts[$case->value] ?? 0;
        }

        return $result;
    }
}
