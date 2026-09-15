<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findByEmailOrFail(string $email): User;

    public function findByPhone(string $phone): ?User;

    public function existsByEmail(string $email): bool;

    public function updateLastLogin(int $userId, string $ipAddress): void;

    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
