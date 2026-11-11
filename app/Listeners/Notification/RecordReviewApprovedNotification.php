<?php

namespace App\Listeners\Notification;

use App\DTOs\Notification\NotificationEventData;
use App\Enums\NotificationType;
use App\Events\ReviewApproved;
use App\Services\Shared\Notification\NotificationService;

class RecordReviewApprovedNotification
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(ReviewApproved $event): void
    {
        $review = $event->review;
        $review->loadMissing('user');
        $email = trim($review->user?->email ?? '');

        $this->notifications->record(new NotificationEventData(
            type: NotificationType::REVIEW_APPROVED,
            eventKey: NotificationType::REVIEW_APPROVED->value.':'.$review->uuid,
            occurredAt: now()->toISOString(),
            mailPayload: [
                'subject' => __('Đánh giá được duyệt'),
                'lines' => [__('Đánh giá sản phẩm của bạn đã được duyệt. Cảm ơn bạn đã chia sẻ trải nghiệm mua hàng.')],
            ],
            mailRecipient: filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : null,
            mailRecipientKey: 'review-author:'.$review->uuid,
            mailUserId: $review->user?->id,
        ));
    }
}
