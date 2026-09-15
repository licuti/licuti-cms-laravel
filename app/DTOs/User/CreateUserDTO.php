<?php

namespace App\DTOs\User;

use App\Core\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
        public ?string $gender = null,
        public ?string $birthday = null,
        public UserStatus $status = UserStatus::ACTIVE,
        public bool $isAdmin = false,
        public array $roles = [],
        public ?string $avatarMediaUuid = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name:     $request->validated('name'),
            email:    $request->validated('email'),
            password: $request->validated('password'),
            phone:    $request->validated('phone'),
            gender:   $request->validated('gender'),
            birthday: $request->validated('birthday'),
            status:   UserStatus::tryFrom($request->input('status', 'active')) ?? UserStatus::ACTIVE,
            isAdmin:  (bool) $request->input('is_admin', false),
            roles:             $request->input('roles', ['customer']),
            avatarMediaUuid:   $request->input('avatar_media_uuid') ?: null,
        );
    }
}
