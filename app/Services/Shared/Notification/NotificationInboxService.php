<?php

namespace App\Services\Shared\Notification;

use App\Enums\NotificationType;
use App\Models\User;
use App\Repositories\Interfaces\NotificationInboxRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificationInboxService
{
    private const TARGETS = [
        'orders' => ['type' => 'order', 'detail' => 'admin.orders.show', 'list' => 'admin.orders.index'],
        'payments' => ['type' => 'payment', 'detail' => 'admin.payments.edit', 'list' => 'admin.payments.index'],
        'products' => ['type' => 'product', 'detail' => 'admin.products.edit', 'list' => 'admin.products.index'],
    ];

    public function __construct(
        private readonly NotificationInboxRepositoryInterface $inbox,
        private readonly NotificationAccessService $access,
    ) {}

    public function canAccessInbox(User $user): bool
    {
        $freshUser = $this->inbox->freshOwner($user);
        if (! $freshUser || ! $freshUser->isActive()) {
            return false;
        }
        $permissions = $freshUser->permissions->concat($freshUser->roles->flatMap->permissions)
            ->where('guard_name', 'web')->pluck('name');

        return $freshUser->is_admin || $permissions->contains('admin.access');
    }

    public function index(User $user, array $filters): array
    {
        $allowedTypes = $this->allowedTypes($user);
        $group = $filters['group'] ?? null;
        $filteredTypes = array_values(array_filter($allowedTypes, fn (string $type) => ! $group || NotificationType::from($type)->group() === $group));
        $notifications = $this->inbox->paginate($user, $filteredTypes, $filters);
        $notifications->through(fn (DatabaseNotification $notification) => $this->present($notification));
        $counts = $this->inbox->counts($user, $filteredTypes);
        $tabs = [];
        foreach (['all' => __('Tất cả'), 'unread' => __('Chưa đọc'), 'read' => __('Đã đọc')] as $key => $label) {
            $tabs[] = ['key' => $key, 'label' => $label, 'count' => $counts[$key]];
        }
        $groups = collect($allowedTypes)
            ->map(fn (string $type) => NotificationType::from($type)->group())
            ->unique()->values()
            ->map(fn (string $group) => ['value' => $group, 'label' => NotificationType::groupLabel($group)])
            ->all();

        return compact('notifications', 'counts', 'tabs', 'filters', 'groups') + ['tab' => $filters['tab'] ?? 'all'];
    }

    public function feed(User $user): array
    {
        $allowedTypes = $this->allowedTypes($user);

        return [
            'counts' => $this->inbox->counts($user, $allowedTypes),
            'notifications' => $this->inbox->recent($user, $allowedTypes)
                ->map(fn (DatabaseNotification $notification) => $this->present($notification))->all(),
        ];
    }

    public function read(User $user, string $uuid): void
    {
        $this->inbox->markRead($user, $this->allowedTypes($user), $uuid);
    }

    public function readAll(User $user): int
    {
        return $this->inbox->markAllRead($user, $this->allowedTypes($user));
    }

    public function open(User $user, string $uuid): array
    {
        $allowedTypes = $this->allowedTypes($user);
        $notification = $this->inbox->find($user, $allowedTypes, $uuid);
        $destination = $this->destination($notification);
        $this->inbox->markRead($user, $allowedTypes, $uuid);

        return $destination;
    }

    private function allowedTypes(User $user): array
    {
        if (! $this->canAccessInbox($user)) {
            throw new AuthorizationException;
        }

        return $this->access->allowedTypes($user);
    }

    private function destination(DatabaseNotification $notification): array
    {
        $group = NotificationType::from($notification->type)->group();
        $target = self::TARGETS[$group] ?? null;
        $payload = $notification->data;
        $uuid = $payload['target_uuid'] ?? null;
        $validTarget = $target && ($payload['target_type'] ?? null) === $target['type']
            && is_string($uuid) && Str::isUuid($uuid);
        $existingUuid = $validTarget ? $this->inbox->existingTargetUuid($target['type'], $uuid) : null;
        if ($existingUuid) {
            return ['url' => route($target['detail'], ['uuid' => $existingUuid], false), 'message' => null];
        }

        return [
            'url' => route($target['list'] ?? 'admin.notifications.index', [], false),
            'message' => __('Đối tượng không còn tồn tại hoặc liên kết thông báo không hợp lệ. Đã chuyển về danh sách.'),
        ];
    }

    private function present(DatabaseNotification $notification): array
    {
        $type = NotificationType::from($notification->type);
        $payload = $notification->data;

        return [
            'id' => $notification->id,
            'title' => is_string($payload['title'] ?? null) ? $payload['title'] : $type->label(),
            'message' => is_string($payload['message'] ?? null) ? $payload['message'] : '',
            'type_label' => $type->label(),
            'group' => $type->group(),
            'occurred_at' => is_string($payload['occurred_at'] ?? null) ? $payload['occurred_at'] : $notification->created_at?->toIso8601String(),
            'read' => $notification->read_at !== null,
            'read_url' => route('admin.notifications.read', ['uuid' => $notification->id], false),
            'open_url' => route('admin.notifications.open', ['uuid' => $notification->id], false),
        ];
    }
}
