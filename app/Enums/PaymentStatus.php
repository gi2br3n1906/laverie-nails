<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('ui.status.payment_pending'),
            self::Paid => __('ui.status.paid'),
            self::Failed => __('ui.status.failed'),
            self::Expired => __('ui.status.expired'),
        };
    }
}
