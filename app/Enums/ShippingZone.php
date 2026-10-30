<?php

declare(strict_types=1);

namespace App\Enums;

enum ShippingZone: string
{
    case Java = 'java';
    case OutsideJava = 'outside_java';

    public function label(): string
    {
        return match ($this) {
            self::Java => __('ui.status.java'),
            self::OutsideJava => __('ui.status.outside_java'),
        };
    }
}
