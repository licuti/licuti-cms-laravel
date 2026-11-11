<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\NotificationRecipientRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class NotificationRecipientRepository implements NotificationRecipientRepositoryInterface
{
    public function findFresh(int $userId): ?User
    {
        return User::query()->with(['roles.permissions', 'permissions'])->find($userId);
    }

    public function activeAdminCandidates(): iterable
    {
        return User::query()->active()->with(['roles.permissions', 'permissions'])
            ->where(function (Builder $query) {
                $query->where('is_admin', true)
                    ->orWhereHas('permissions', fn (Builder $permission) => $permission->where('name', 'admin.access'))
                    ->orWhereHas('roles.permissions', fn (Builder $permission) => $permission->where('name', 'admin.access'));
            })
            ->lazyById(max(1, (int) config('notification.recipient_batch', 200)));
    }
}
