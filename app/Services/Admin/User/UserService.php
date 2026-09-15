<?php

namespace App\Services\Admin\User;

use App\Core\Base\BaseService;
use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Exceptions\BusinessException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Shared\Media\MediaService;
use Illuminate\Pagination\LengthAwarePaginator;


class UserService extends BaseService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MediaService $mediaService,
    ) {}

    public function getPaginatedUsers(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->getPaginatedUsers($filters, $perPage);
    }

    public function getByUuid(string $uuid): User
    {
        try {
            return $this->userRepository->findByUuid($uuid, ['*'], ['roles', 'permissions']);
        } catch (\Throwable) {
            throw new UserNotFoundException($uuid);
        }
    }

    public function create(CreateUserDTO $dto): User
    {
        return $this->handleTransaction(function () use ($dto) {
            $user = $this->userRepository->create([
                'name'     => $dto->name,
                'email'    => $dto->email,
                'password' => $dto->password,
                'phone'    => $dto->phone,
                'gender'   => $dto->gender,
                'birthday' => $dto->birthday,
                'status'   => $dto->status,
                'is_admin' => $dto->isAdmin,
            ]);

            if (!empty($dto->roles)) {
                $user->syncRoles($dto->roles);
            } else {
                $user->assignRole('customer');
            }

            if ($dto->avatarMediaUuid) {
                $user->update(['avatar_media_uuid' => $dto->avatarMediaUuid]);
            }

            return $user->fresh(['roles', 'permissions']);
        });
    }

    public function update(string $uuid, UpdateUserDTO $dto, User $actor): User
    {
        $user = $this->getByUuid($uuid);

        // Bảo vệ: Không ai được phép khóa hoặc hạ quyền của chính mình
        if ($user->id === $actor->id) {
            if ($dto->status !== null && $dto->status !== $user->status) {
                throw new BusinessException('Bạn không thể tự khóa hoặc đổi trạng thái tài khoản của chính mình.');
            }
            if ($dto->isAdmin !== null && $dto->isAdmin === false && $user->is_admin) {
                throw new BusinessException('Bạn không thể tự hủy quyền Admin của chính mình.');
            }
        }

        // Bảo vệ: Nếu tài khoản là super-admin, chỉ super-admin khác mới được chỉnh sửa
        if ($user->hasRole('super-admin') && !$actor->hasRole('super-admin')) {
            throw new BusinessException('Bạn không có quyền chỉnh sửa tài khoản Super Admin.');
        }

        return $this->handleTransaction(function () use ($user, $dto) {
            $updateData = $dto->toUpdateArray();
            if (!empty($updateData)) {
                $user->update($updateData);
            }

            if ($dto->roles !== null) {
                $user->syncRoles($dto->roles);
            }

            if ($dto->removeAvatar) {
                $user->update(['avatar_media_uuid' => null]);
            } elseif ($dto->avatarMediaUuid) {
                $user->update(['avatar_media_uuid' => $dto->avatarMediaUuid]);
            }
            return $user->fresh(['roles', 'permissions']);
        });
    }

    public function delete(string $uuid, User $actor): bool
    {
        $user = $this->getByUuid($uuid);

        if ($user->id === $actor->id) {
            throw new BusinessException('Bạn không thể tự xóa tài khoản của chính mình.');
        }

        if ($user->hasRole('super-admin')) {
            throw new BusinessException('Không thể xóa tài khoản Super Admin.');
        }

        return $this->userRepository->delete($user->id);
    }

    public function syncRoles(string $uuid, array $roles, User $actor): User
    {
        $user = $this->getByUuid($uuid);

        if ($user->id === $actor->id && !in_array('admin', $roles) && !in_array('super-admin', $roles)) {
            throw new BusinessException('Bạn không thể tự tước bỏ vai trò Quản trị viên của chính mình.');
        }

        if ($user->hasRole('super-admin') && !$actor->hasRole('super-admin')) {
            throw new BusinessException('Bạn không có quyền thay đổi vai trò của tài khoản Super Admin.');
        }

        $user->syncRoles($roles);

        return $user->fresh(['roles', 'permissions']);
    }

    public function restore(string $uuid): User
    {
        $user = User::withTrashed()->where('uuid', $uuid)->firstOrFail();
        $user->restore();

        return $user->fresh(['roles', 'permissions']);
    }

    public function bulkDelete(array $ids, User $actor): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $user = User::where('id', $id)->orWhere('uuid', $id)->first();
            if ($user && $user->id !== $actor->id && !$user->hasRole('super-admin')) {
                if ($this->userRepository->delete($user->id)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function bulkUpdateStatus(array $ids, string $status, User $actor): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $user = User::where('id', $id)->orWhere('uuid', $id)->first();
            if ($user && $user->id !== $actor->id && !$user->hasRole('super-admin')) {
                $user->update(['status' => $status]);
                $count++;
            }
        }
        return $count;
    }

    public function bulkRestore(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $user = User::withTrashed()->where('id', $id)->orWhere('uuid', $id)->first();
            if ($user && $user->trashed()) {
                $user->restore();
                $count++;
            }
        }
        return $count;
    }

    public function bulkForceDelete(array $ids, User $actor): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $user = User::withTrashed()->where('id', $id)->orWhere('uuid', $id)->first();
            if ($user && $user->trashed() && !$user->hasRole('super-admin')) {
                $user->forceDelete();
                $count++;
            }
        }
        return $count;
    }

    public function resolveActions(User $user, string $tab, User $actor): array
    {
        if ($tab === 'trash') {
            $actions = [
                ['label' => 'Khôi phục', 'route' => route('admin.users.restore', $user->uuid), 'method' => 'POST', 'color' => 'emerald'],
            ];
            
            if (!$user->hasRole('super-admin')) {
                $actions[] = [
                    'label'         => 'Xóa vĩnh viễn', 
                    'route'         => route('admin.users.force-delete', $user->uuid), 
                    'method'        => 'DELETE', 
                    'color'         => 'red',
                    'confirm_title' => 'Xóa vĩnh viễn?',
                    'confirm_text'  => 'Tài khoản này sẽ bị xóa hoàn toàn khỏi hệ thống.',
                    'confirm_btn'   => 'Xóa vĩnh viễn'
                ];
            }
            return $actions;
        }

        $actions = [
            ['label' => 'Sửa', 'route' => route('admin.users.edit', $user->uuid), 'method' => 'GET', 'color' => 'amber'],
        ];

        if ($user->id !== $actor->id && !$user->hasRole('super-admin')) {
            $actions[] = [
                'label'         => 'Xóa', 
                'route'         => route('admin.users.destroy', $user->uuid), 
                'method'        => 'DELETE', 
                'color'         => 'red',
                'confirm_title' => 'Xóa người dùng?',
                'confirm_text'  => 'Thao tác này sẽ chuyển tài khoản vào Thùng rác.',
                'confirm_btn'   => 'Xóa ngay'
            ];
        }

        return $actions;
    }
}
