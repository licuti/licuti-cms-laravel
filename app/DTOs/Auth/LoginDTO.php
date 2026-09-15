<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName = 'web',
        public bool   $remember   = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email:      $request->validated('email'),
            password:   $request->validated('password'),
            deviceName: $request->input('device_name', 'web'),
            remember:   (bool) $request->input('remember', false),
        );
    }
}
