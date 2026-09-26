<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'code',
        'discount_percentage',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'discount_percentage' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
