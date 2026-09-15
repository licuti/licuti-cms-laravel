<?php

namespace App\Core\Enums;

/**
 * Mẫu hiển thị trang tĩnh.
 * Mỗi value là tên file view trong resources/views/frontend/pages/.
 */
enum PageTemplate: string
{
    case DEFAULT      = 'default';
    case FULL_WIDTH   = 'full_width';
    case LANDING      = 'landing';
    case CONTACT_US   = 'contact_us';

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT    => __('Mặc định'),
            self::FULL_WIDTH => __('Full width (không sidebar)'),
            self::LANDING    => __('Landing page'),
            self::CONTACT_US => __('Trang liên hệ'),
        };
    }
}