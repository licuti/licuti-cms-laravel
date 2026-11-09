<?php

namespace App\Enums;

enum FlashSaleStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case RUNNING = 'running';
    case ENDED = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => __('Bản nháp'),
            self::SCHEDULED => __('Sắp diễn ra'),
            self::RUNNING   => __('Đang diễn ra'),
            self::ENDED     => __('Đã kết thúc'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT     => 'secondary',
            self::SCHEDULED => 'info',
            self::RUNNING   => 'success',
            self::ENDED     => 'dark',
        };
    }

    public static function options(): array
    {
        return array_reduce(self::cases(), function (array $carry, self $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }
}
