<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function findByName(string $name, string $guardName = 'web'): ?Role
    {
        return $this->model->where('name', $name)->where('guard_name', $guardName)->first();
    }

    public function findByNameOrFail(string $name, string $guardName = 'web'): Role
    {
        return $this->model->where('name', $name)->where('guard_name', $guardName)->firstOrFail();
    }

    public function getRolesWithCount(): Collection
    {
        return $this->model->withCount(['users', 'permissions'])->with('permissions')->get();
    }
}
