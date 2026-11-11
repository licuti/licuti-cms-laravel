<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Http\Requests\Admin\Notification\NotificationFilterRequest;
use App\Http\Requests\Admin\Notification\OpenNotificationRequest;
use App\Http\Requests\Admin\Notification\ReadAllNotificationsRequest;
use App\Http\Requests\Admin\Notification\ReadNotificationRequest;
use App\Services\Shared\Notification\NotificationInboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class NotificationController extends BaseController
{
    public function __construct(private readonly NotificationInboxService $inbox) {}

    public function index(NotificationFilterRequest $request): Response|JsonResponse
    {
        $inbox = $this->inbox->index($request->user(), $request->validated());
        if ($request->expectsJson()) {
            return $this->successResponse($inbox, __('Thông báo của tôi'))
                ->header('Cache-Control', 'private, no-store');
        }

        return response()->view('admin.notifications.index', $inbox)
            ->header('Cache-Control', 'private, no-store');
    }

    public function feed(NotificationFilterRequest $request): JsonResponse
    {
        return $this->successResponse($this->inbox->feed($request->user()), __('Thông báo của tôi'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function read(ReadNotificationRequest $request): JsonResponse|RedirectResponse
    {
        $this->inbox->read($request->user(), $request->validated('uuid'));
        if ($request->expectsJson()) {
            return $this->successResponse(message: __('Đã đánh dấu thông báo đã đọc.'));
        }

        return redirect()->route('admin.notifications.index')->with('success', __('Đã đánh dấu thông báo đã đọc.'));
    }

    public function readAll(ReadAllNotificationsRequest $request): JsonResponse|RedirectResponse
    {
        $count = $this->inbox->readAll($request->user());
        $message = __('Đã đánh dấu :count thông báo đã đọc.', ['count' => $count]);
        if ($request->expectsJson()) {
            return $this->successResponse(['count' => $count], $message);
        }

        return redirect()->route('admin.notifications.index')->with('success', $message);
    }

    public function open(OpenNotificationRequest $request): JsonResponse|RedirectResponse
    {
        $destination = $this->inbox->open($request->user(), $request->validated('uuid'));
        if ($destination['message']) {
            $request->session()->flash('warning', $destination['message']);
        }
        if ($request->expectsJson()) {
            return $this->successResponse($destination, $destination['message'] ?? __('Đang mở thông báo.'));
        }

        return redirect()->to($destination['url']);
    }
}
