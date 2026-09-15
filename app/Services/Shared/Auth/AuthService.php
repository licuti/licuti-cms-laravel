<?php

namespace App\Services\Shared\Auth;

use App\Core\Base\BaseService;
use App\Core\Enums\UserStatus;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Events\User\UserRegistered;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService extends BaseService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Xác thực thông tin đăng nhập và phát hành Sanctum token.
     *
     * @throws AuthenticationException
     */
    public function login(LoginDTO $dto, string $ipAddress): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new AuthenticationException('Email hoặc mật khẩu không chính xác.');
        }

        if ($user->status === UserStatus::BANNED) {
            throw new AuthenticationException('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
        }

        if ($user->status === UserStatus::INACTIVE) {
            throw new AuthenticationException('Tài khoản chưa được kích hoạt.');
        }

        // Xóa token cũ theo device để tránh token rác
        $user->tokens()->where('name', $dto->deviceName)->delete();

        $tokenExpiration = $dto->remember ? now()->addDays(30) : now()->addDay();
        $token = $user->createToken($dto->deviceName, ['*'], $tokenExpiration)->plainTextToken;

        // Cập nhật thông tin đăng nhập cuối
        $this->userRepository->updateLastLogin($user->id, $ipAddress);

        return [
            'user'       => $user->fresh(),
            'token'      => $token,
            'expires_at' => $tokenExpiration->toIso8601String(),
        ];
    }

    /**
     * Đăng ký tài khoản mới và phát hành Sanctum token.
     */
    public function register(RegisterDTO $dto, string $ipAddress): array
    {
        $user = $this->handleTransaction(function () use ($dto) {
            $user = $this->userRepository->create([
                'name'     => $dto->name,
                'email'    => $dto->email,
                'password' => $dto->password,   // sẽ được tự hash bởi cast 'hashed' trong Model
                'phone'    => $dto->phone,
                'status'   => UserStatus::ACTIVE,
                'is_admin' => false,
            ]);

            // Gán role mặc định cho người dùng mới
            $user->assignRole('customer');

            return $user;
        });

        // Fire event — Listener sẽ tự gửi email chào mừng qua Queue
        event(new UserRegistered($user));

        $token = $user->createToken($dto->deviceName, ['*'], now()->addDay())->plainTextToken;

        $this->userRepository->updateLastLogin($user->id, $ipAddress);

        return [
            'user'       => $user->fresh(),
            'token'      => $token,
            'expires_at' => now()->addDay()->toIso8601String(),
        ];
    }

    /**
     * Thu hồi token hiện tại (đăng xuất).
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Thu hồi tất cả token của user (đăng xuất mọi thiết bị).
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }
}
