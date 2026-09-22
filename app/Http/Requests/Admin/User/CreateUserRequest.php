<?php

namespace App\Http\Requests\Admin\User;

use App\Core\Enums\UserStatus;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'users.create';
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()],
            'phone'    => ['nullable', 'string', 'regex:/^(0|\+84)[0-9]{9}$/', 'unique:users,phone'],
            'status'   => ['nullable', 'string', Rule::in(UserStatus::values())],
            'is_admin' => ['nullable', 'boolean'],
            'roles'    => ['nullable', 'array'],
            'roles.*'             => ['string', 'exists:roles,name'],
            'avatar_media_uuid'   => ['nullable', 'string', 'exists:media,uuid'],
        ];
    }
}
