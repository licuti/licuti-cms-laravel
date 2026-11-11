<?php

namespace App\Http\Requests\Admin\Notification;

use App\Services\Shared\Notification\NotificationInboxService;
use Illuminate\Foundation\Http\FormRequest;

abstract class NotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(NotificationInboxService::class)->canAccessInbox($this->user());
    }
}
