<?php

namespace App\Core\Enums;

enum UserStatus: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
    case BANNED   = 'banned';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE   => 'Hoạt động',
            self::INACTIVE => 'Không hoạt động',
            self::BANNED   => 'Bị cấm',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE   => 'success',
            self::INACTIVE => 'warning',
            self::BANNED   => 'danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
