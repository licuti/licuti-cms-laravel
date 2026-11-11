<?php

namespace App\Services\Shared\Notification;

use App\Enums\NotificationType;
use App\Models\User;
use App\Repositories\Interfaces\NotificationRecipientRepositoryInterface;

class NotificationAccessService
{
    public function __construct(private readonly NotificationRecipientRepositoryInterface $recipients) {}

    /** @return list<string> */
    public function allowedTypes(User $user): array
    {
        $freshUser = $this->recipients->findFresh((int) $user->getKey());

        return $freshUser ? $this->typesForFreshUser($freshUser) : [];
    }

    public function canReceive(User $user, NotificationType $type): bool
    {
        return in_array($type->value, $this->allowedTypes($user), true);
    }

    /** @return iterable<User> No per-recipient permission queries during fan-out. */
    public function eligibleRecipients(NotificationType $type): iterable
    {
        if ($type->permission() === null) {
            return;
        }

        foreach ($this->recipients->activeAdminCandidates() as $user) {
            if (in_array($type->value, $this->typesForFreshUser($user), true)) {
                yield $user;
            }
        }
    }

    private function typesForFreshUser(User $user): array
    {
        if (! $user->isActive()) {
            return [];
        }

        // Read the eager-loaded DB relations, never Spatie's cached permission registry or Gate.
        $permissions = $user->permissions->concat($user->roles->flatMap->permissions)
            ->where('guard_name', 'web')->pluck('name')->unique()->all();
        if (! $user->is_admin && ! in_array('admin.access', $permissions, true)) {
            return [];
        }

        return array_values(array_map(fn (NotificationType $type) => $type->value,
            array_filter(NotificationType::cases(), fn (NotificationType $type) => $type->permission() !== null && ($user->is_admin || in_array($type->permission(), $permissions, true))
            )
        ));
    }
}
