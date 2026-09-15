<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByEmailOrFail(string $email): User
    {
        return $this->model->where('email', $email)->firstOrFail();
    }

    public function findByPhone(string $phone): ?User
    {
        return $this->model->where('phone', $phone)->first();
    }

    public function existsByEmail(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }

    public function updateLastLogin(int $userId, string $ipAddress): void
    {
        $this->model->where('id', $userId)->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }

    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['roles', 'permissions'])->latest();

        // Lọc theo trạng thái thùng rác
        if (!empty($filters['only_trashed'])) {
            $query->onlyTrashed();
        } elseif (!empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        // Lọc theo keyword (tìm trong name, email, phone)
        if (!empty($filters['keyword'])) {
            $keyword = '%' . trim($filters['keyword']) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                  ->orWhere('email', 'like', $keyword)
                  ->orWhere('phone', 'like', $keyword);
            });
        }

        // Lọc theo trạng thái
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Lọc theo vai trò (role name)
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Lọc theo is_admin
        if (isset($filters['is_admin']) && $filters['is_admin'] !== '') {
            $query->where('is_admin', (bool) $filters['is_admin']);
        }

        return $query->paginate($perPage);
    }
}
