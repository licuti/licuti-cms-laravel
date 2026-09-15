<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function findByName(string $name, string $guardName = 'web'): ?Role;

    public function findByNameOrFail(string $name, string $guardName = 'web'): Role;

    public function getRolesWithCount(): Collection;
}
