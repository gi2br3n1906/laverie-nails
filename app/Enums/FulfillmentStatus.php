<?php

declare(strict_types=1);

namespace App\Enums;

enum FulfillmentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('ui.status.fulfillment_pending'),
            self::Processing => __('ui.status.processing'),
            self::Shipped => __('ui.status.shipped'),
            self::Completed => __('ui.status.completed'),
        };
    }
}
