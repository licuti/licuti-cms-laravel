<?php

namespace App\Enums;

enum AttributeType: string
{
    case SELECT = 'select';
    case COLOR = 'color';
    case BUTTON = 'button';
    case RADIO = 'radio';

    public function label(): string
    {
        return match ($this) {
            self::SELECT => __('Hộp chọn (Select)'),
            self::COLOR => __('Màu sắc (Color Swatch)'),
            self::BUTTON => __('Nút bấm (Button/Text)'),
            self::RADIO => __('Nút chọn đơn (Radio)'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::COLOR => 'danger',
            self::SELECT => 'primary',
            self::BUTTON => 'info',
            self::RADIO => 'warning',
        };
    }
}
