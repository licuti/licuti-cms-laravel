<?php

namespace App\Exceptions\User;

use App\Exceptions\BusinessException;

class UserNotFoundException extends BusinessException
{
    public function __construct(string $identifier = '')
    {
        $message = $identifier
            ? "Không tìm thấy người dùng: {$identifier}"
            : 'Không tìm thấy người dùng';

        parent::__construct($message, 404);
    }
}
