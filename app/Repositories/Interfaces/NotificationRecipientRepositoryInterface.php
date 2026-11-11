<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface NotificationRecipientRepositoryInterface
{
    public function findFresh(int $userId): ?User;

    /** @return iterable<User> Fresh active candidates with roles.permissions and direct permissions loaded. */
    public function activeAdminCandidates(): iterable;
}
