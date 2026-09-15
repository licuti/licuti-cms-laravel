<?php

namespace App\Http\Requests\Api\V1\Admin\User;

use App\Core\Base\BaseRequest;
use App\Core\Enums\UserStatus;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()],
            'phone'    => ['nullable', 'string', 'regex:/^(0|\+84)[0-9]{9}$/', 'unique:users,phone'],
            'gender'   => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'birthday' => ['nullable', 'date', 'before:today'],
            'status'   => ['nullable', 'string', Rule::in(UserStatus::values())],
            'is_admin' => ['nullable', 'boolean'],
            'roles'    => ['nullable', 'array'],
            'roles.*'  => ['string', 'exists:roles,name'],
        ];
    }
}
