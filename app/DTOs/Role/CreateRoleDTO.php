<?php

namespace App\DTOs\Role;

use Illuminate\Http\Request;

readonly class CreateRoleDTO
{
    public function __construct(
        public string $name,
        public array $permissions = [],
        public string $guardName = 'web',
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name:        $request->validated('name'),
            permissions: $request->input('permissions', []),
            guardName:   $request->input('guard_name', 'web'),
        );
    }
}
