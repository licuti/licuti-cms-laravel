<?php

namespace App\Http\Resources\Role;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'guard_name'        => $this->guard_name,
            'users_count'       => $this->whenCounted('users'),
            'permissions_count' => $this->whenCounted('permissions'),
            'permissions'       => PermissionResource::collection($this->whenLoaded('permissions')),
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}
