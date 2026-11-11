<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Interfaces\NotificationInboxRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationInboxRepository implements NotificationInboxRepositoryInterface
{
    private const PAGE_SIZE = 20;

    private const RECENT_LIMIT = 5;

    private const UPDATE_CHUNK_SIZE = 500;

    public function freshOwner(User $owner): ?User
    {
        return User::with(['roles.permissions', 'permissions'])->find($owner->getKey());
    }

    public function paginate(User $owner, array $allowedTypes, array $filters): LengthAwarePaginator
    {
        $query = $this->ownedQuery($owner, $allowedTypes);
        match ($filters['tab'] ?? 'all') {
            'unread' => $query->whereNull('read_at'),
            'read' => $query->whereNotNull('read_at'),
            default => null,
        };

        return $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(self::PAGE_SIZE, ['*'], 'page', $filters['page'] ?? 1);
    }

    public function recent(User $owner, array $allowedTypes): Collection
    {
        return $this->ownedQuery($owner, $allowedTypes)
            ->orderByDesc('created_at')->orderByDesc('id')->limit(self::RECENT_LIMIT)->get();
    }

    public function counts(User $owner, array $allowedTypes): array
    {
        $query = $this->ownedQuery($owner, $allowedTypes);
        $unread = (clone $query)->whereNull('read_at')->count();
        $read = (clone $query)->whereNotNull('read_at')->count();

        return ['all' => $unread + $read, 'unread' => $unread, 'read' => $read];
    }

    public function find(User $owner, array $allowedTypes, string $uuid): DatabaseNotification
    {
        return $this->ownedQuery($owner, $allowedTypes)->whereKey($uuid)->firstOrFail();
    }

    public function markRead(User $owner, array $allowedTypes, string $uuid): void
    {
        $this->find($owner, $allowedTypes, $uuid);
        $this->ownedQuery($owner, $allowedTypes)->whereKey($uuid)
            ->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function markAllRead(User $owner, array $allowedTypes): int
    {
        return DB::transaction(function () use ($owner, $allowedTypes): int {
            // One SELECT snapshots IDs, not timestamps: inserts after this SELECT are never updated.
            $snapshotIds = $this->ownedQuery($owner, $allowedTypes)->whereNull('read_at')->pluck('id');
            $updated = 0;
            $readAt = now();
            foreach ($snapshotIds->chunk(self::UPDATE_CHUNK_SIZE) as $ids) {
                $updated += $this->ownedQuery($owner, $allowedTypes)->whereIn('id', $ids)
                    ->whereNull('read_at')->update(['read_at' => $readAt]);
            }

            return $updated;
        });
    }

    public function existingTargetUuid(string $targetType, string $uuid): ?string
    {
        $model = match ($targetType) {
            'order' => Order::class,
            'payment' => Payment::class,
            'product' => Product::class,
            default => null,
        };

        return $model ? $model::query()->where('uuid', $uuid)->value('uuid') : null;
    }

    private function ownedQuery(User $owner, array $allowedTypes): Builder
    {
        // Ownership is unconditional, including for users with Gate::before super-admin access.
        return DatabaseNotification::query()
            ->where('notifiable_type', $owner->getMorphClass())
            ->where('notifiable_id', $owner->getKey())
            ->whereIn('type', $allowedTypes);
    }
}
