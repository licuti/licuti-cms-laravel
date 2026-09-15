<?php

namespace App\Core\Base;

use App\Core\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Ghi đè hành vi mặc định khi validation thất bại.
     * Thay vì redirect, trả về JSON theo chuẩn API dự án.
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }

    /**
     * Ghi đè hành vi khi authorization thất bại.
     * Trả về JSON 403 thay vì throw AuthorizationException.
     */
    protected function failedAuthorization(): never
    {
        throw new HttpResponseException(
            $this->forbiddenResponse()
        );
    }
}
