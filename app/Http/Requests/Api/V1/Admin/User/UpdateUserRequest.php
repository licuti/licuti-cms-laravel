<?php

namespace App\Http\Requests\Api\V1\Admin\User;

use App\Core\Base\BaseRequest;
use App\Core\Enums\UserStatus;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        // Lấy uuid từ route parameters (chúng ta dùng uuid trong getRouteKeyName)
        $uuid = $this->route('user');
        $targetUser = User::where('uuid', $uuid)->first();
        $userId = $targetUser ? $targetUser->id : null;

        return [
            'name'     => ['sometimes', 'string', 'max:100'],
            'email'    => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()],
            'phone'    => ['nullable', 'string', 'regex:/^(0|\+84)[0-9]{9}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'gender'   => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'birthday' => ['nullable', 'date', 'before:today'],
            'status'   => ['nullable', 'string', Rule::in(UserStatus::values())],
            'is_admin' => ['nullable', 'boolean'],
            'roles'    => ['nullable', 'array'],
            'roles.*'  => ['string', 'exists:roles,name'],
        ];
    }
}
