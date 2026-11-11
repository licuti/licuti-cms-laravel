<?php

namespace App\Http\Requests\Admin\Notification;

class ReadNotificationRequest extends NotificationRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['uuid' => $this->route('uuid')]);
    }

    public function rules(): array
    {
        return [
            'uuid' => ['required', 'uuid'],
            'user_id' => ['prohibited'],
        ];
    }
}
