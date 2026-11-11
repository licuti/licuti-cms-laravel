<?php

namespace App\Http\Requests\Admin\Notification;

class ReadAllNotificationsRequest extends NotificationRequest
{
    public function rules(): array
    {
        return ['user_id' => ['prohibited']];
    }
}
