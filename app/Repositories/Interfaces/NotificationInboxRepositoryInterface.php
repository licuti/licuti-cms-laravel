<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

interface NotificationInboxRepositoryInterface
{
    public function freshOwner(User $owner): ?User;

    public function paginate(User $owner, array $allowedTypes, array $filters): LengthAwarePaginator;

    public function recent(User $owner, array $allowedTypes): Collection;

    public function counts(User $owner, array $allowedTypes): array;

    public function find(User $owner, array $allowedTypes, string $uuid): DatabaseNotification;

    public function markRead(User $owner, array $allowedTypes, string $uuid): void;

    public function markAllRead(User $owner, array $allowedTypes): int;

    public function existingTargetUuid(string $targetType, string $uuid): ?string;
}
