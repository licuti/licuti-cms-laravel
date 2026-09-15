<?php

namespace App\Services\Admin\Role;

use App\Core\Base\BaseService;
use App\DTOs\Role\CreateRoleDTO;
use App\DTOs\Role\UpdateRoleDTO;
use App\Exceptions\BusinessException;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService extends BaseService
{
    /**
     * Danh sách các vai trò hệ thống cốt lõi không được phép xóa
     */
    private const PROTECTED_ROLES = ['super-admin', 'admin', 'customer'];

    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
    ) {}

    public function getAllWithCount(): Collection
    {
        return $this->roleRepository->getRolesWithCount();
    }

    public function getByIdOrName(string|int $identifier): Role
    {
        try {
            if (is_numeric($identifier)) {
                return $this->roleRepository->find((int) $identifier, ['*'], ['permissions']);
            }
            return $this->roleRepository->findByNameOrFail((string) $identifier);
        } catch (\Throwable) {
            throw new BusinessException("Không tìm thấy vai trò: {$identifier}", 404);
        }
    }

    public function create(CreateRoleDTO $dto): Role
    {
        return $this->handleTransaction(function () use ($dto) {
            $role = $this->roleRepository->create([
                'name'       => $dto->name,
                'guard_name' => $dto->guardName,
            ]);

            if (!empty($dto->permissions)) {
                $role->syncPermissions($dto->permissions);
            }

            return $role->load('permissions');
        });
    }

    public function update(string|int $identifier, UpdateRoleDTO $dto): Role
    {
        $role = $this->getByIdOrName($identifier);

        // Bảo vệ: Nếu đổi tên của các role hệ thống thì báo lỗi
        if ($dto->name !== null && $dto->name !== $role->name && in_array($role->name, self::PROTECTED_ROLES)) {
            throw new BusinessException("Không thể đổi tên vai trò hệ thống \"{$role->name}\".");
        }

        return $this->handleTransaction(function () use ($role, $dto) {
            if ($dto->name !== null && $dto->name !== $role->name) {
                $role->update(['name' => $dto->name]);
            }

            if ($dto->permissions !== null) {
                // Nếu là super-admin, luôn phải giữ toàn bộ quyền
                if ($role->name === 'super-admin') {
                    $role->syncPermissions(Permission::all());
                } else {
                    $role->syncPermissions($dto->permissions);
                }
            }

            return $role->fresh('permissions');
        });
    }

    public function delete(string|int $identifier): bool
    {
        $role = $this->getByIdOrName($identifier);

        if (in_array($role->name, self::PROTECTED_ROLES)) {
            throw new BusinessException("Không được phép xóa vai trò hệ thống \"{$role->name}\".");
        }

        // Kiểm tra nếu có user đang giữ role này thì không cho xóa
        if ($role->users()->count() > 0) {
            throw new BusinessException("Vai trò \"{$role->name}\" đang được gán cho người dùng, không thể xóa.");
        }

        return $this->roleRepository->delete($role->id);
    }

    public function getAllPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }
}
