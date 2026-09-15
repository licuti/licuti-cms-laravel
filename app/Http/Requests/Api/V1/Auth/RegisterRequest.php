<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Core\Base\BaseRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true; // Ai cũng có thể đăng ký
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'phone'    => ['nullable', 'string', 'regex:/^(0|\+84)[0-9]{9}$/', 'unique:users,phone'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Vui lòng nhập họ tên.',
            'email.required'     => 'Vui lòng nhập địa chỉ email.',
            'email.email'        => 'Địa chỉ email không hợp lệ.',
            'email.unique'       => 'Email này đã được sử dụng.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'phone.regex'        => 'Số điện thoại không đúng định dạng Việt Nam.',
            'phone.unique'       => 'Số điện thoại này đã được đăng ký.',
        ];
    }
}
