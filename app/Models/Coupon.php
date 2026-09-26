<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'code',
        'discount_type',
        'discount_percentage',
        'discount_amount',
        'minimum_order_amount',
        'expires_at',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'discount_type' => 'string',
            'discount_percentage' => 'integer',
            'discount_amount' => 'integer',
            'minimum_order_amount' => 'integer',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isValidForSubtotal(int $subtotal): bool
    {
        return $this->is_active
            && (! $this->expires_at || $this->expires_at->isFuture())
            && $subtotal >= $this->minimum_order_amount;
    }

    public function discountFor(int $subtotal): int
    {
        if (! $this->isValidForSubtotal($subtotal) || $subtotal < 1) {
            return 0;
        }

        return min($subtotal, match ($this->discount_type) {
            'fixed' => $this->discount_amount,
            default => intdiv($subtotal * $this->discount_percentage, 100),
        });
    }
}
