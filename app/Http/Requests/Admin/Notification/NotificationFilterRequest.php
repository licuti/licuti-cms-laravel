<?php

namespace App\Http\Requests\Admin\Notification;

use Illuminate\Validation\Rule;

class NotificationFilterRequest extends NotificationRequest
{
    public function rules(): array
    {
        return [
            'tab' => ['nullable', Rule::in(['all', 'unread', 'read'])],
            'group' => ['nullable', Rule::in(['orders', 'payments', 'products'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'user_id' => ['prohibited'],
        ];
    }
}
