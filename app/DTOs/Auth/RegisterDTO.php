<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

readonly class RegisterDTO
{
    public function __construct(
        public string  $name,
        public string  $email,
        public string  $password,
        public ?string $phone      = null,
        public string  $deviceName = 'web',
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name:       $request->validated('name'),
            email:      $request->validated('email'),
            password:   $request->validated('password'),
            phone:      $request->validated('phone'),
            deviceName: $request->input('device_name', 'web'),
        );
    }
}
