<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Core\Base\BaseRequest;

class LoginRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true; // Ai cũng có thể gửi request đăng nhập
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => __('validation.required', ['attribute' => 'email']),
            'email.email'       => __('validation.email', ['attribute' => 'email']),
            'password.required' => __('validation.required', ['attribute' => 'mật khẩu']),
            'password.min'      => __('validation.min.string', ['attribute' => 'mật khẩu', 'min' => 6]),
        ];
    }
}
