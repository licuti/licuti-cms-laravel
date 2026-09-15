<?php

namespace App\DTOs\User;

use App\Core\Enums\UserStatus;
use Illuminate\Http\Request;

readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $password = null,
        public ?string $gender = null,
        public ?string $birthday = null,
        public ?UserStatus $status = null,
        public ?bool $isAdmin = null,
        public ?array $roles = null,
        public ?string $avatarMediaUuid = null,
        public bool $removeAvatar = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name:     $request->input('name'),
            email:    $request->input('email'),
            phone:    $request->input('phone'),
            password: $request->input('password'),
            gender:   $request->input('gender'),
            birthday: $request->input('birthday'),
            status:   $request->has('status') ? UserStatus::tryFrom($request->input('status')) : null,
            isAdmin:      $request->has('is_admin') ? (bool) $request->input('is_admin') : null,
            roles:           $request->input('roles'),
            avatarMediaUuid: $request->input('avatar_media_uuid') ?: null,
            removeAvatar:    (bool) $request->input('remove_avatar', false),
        );
    }

    /**
     * Chuyển thành mảng và loại bỏ các trường null để update DB
     */
    public function toUpdateArray(): array
    {
        return array_filter([
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone,
            'password' => $this->password,
            'gender'   => $this->gender,
            'birthday' => $this->birthday,
            'status'   => $this->status?->value,
            'is_admin' => $this->isAdmin,
        ], fn($value) => $value !== null);
    }
}
