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
            self::ACTIVE   => 'green',
            self::INACTIVE => 'amber',
            self::BANNED   => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
