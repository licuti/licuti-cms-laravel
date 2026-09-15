<?php

namespace App\DTOs\Role;

use Illuminate\Http\Request;

readonly class UpdateRoleDTO
{
    public function __construct(
        public ?string $name = null,
        public ?array $permissions = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name:        $request->input('name'),
            permissions: $request->input('permissions'),
        );
    }
}
