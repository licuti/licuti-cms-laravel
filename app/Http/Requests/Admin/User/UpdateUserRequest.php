<?php

namespace App\Http\Requests\Admin\User;

use App\Core\Enums\UserStatus;
use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'users.update';
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?: $this->route('uuid');

        return [
            'name'     => ['sometimes', 'required', 'string', 'max:100'],
            'email'    => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId, 'uuid')],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()],
            'phone'    => ['nullable', 'string', 'regex:/^(0|\+84)[0-9]{9}$/', Rule::unique('users', 'phone')->ignore($userId, 'uuid')],
            'status'   => ['nullable', 'string', Rule::in(UserStatus::values())],
            'is_admin' => ['nullable', 'boolean'],
            'roles'    => ['nullable', 'array'],
            'roles.*'            => ['string', 'exists:roles,name'],
            'avatar_media_uuid'  => ['nullable', 'string', 'exists:media,uuid'],
            'remove_avatar'      => ['nullable', 'boolean'],
        ];
    }
}
