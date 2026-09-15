<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transform User model thành JSON response chuẩn API.
 * Luôn dùng Resource này thay vì $user->toArray() để kiểm soát
 * chính xác những field nào được expose ra ngoài.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'               => $this->uuid,
            'name'               => $this->name,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'avatar'             => $this->avatar,
            'gender'             => $this->gender,
            'birthday'           => $this->birthday?->format('Y-m-d'),
            'status'             => $this->status?->value,
            'status_label'       => $this->status?->label(),
            'email_verified_at'  => $this->email_verified_at?->toIso8601String(),
            'last_login_at'      => $this->last_login_at?->toIso8601String(),
            'roles'              => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'permissions'        => $this->whenLoaded('permissions', fn() => $this->getAllPermissions()->pluck('name')),
            'created_at'         => $this->created_at->toIso8601String(),
        ];
    }
}
