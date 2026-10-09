<?php

namespace App\Core\Enums;

enum ReviewStatus: string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case FLAGGED  = 'flagged';

    public function label(): string
    {
        return match($this) {
            self::PENDING  => __('Chờ duyệt'),
            self::APPROVED => __('Đã duyệt'),
            self::REJECTED => __('Từ chối'),
            self::FLAGGED  => __('Bị báo cáo'),
        };
    }
}
