<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'user_id',
        'guest_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'province_id',
        'city_id',
        'courier',
        'shipping_cost',
        'subtotal',
        'grand_total',
        'payment_status',
        'fulfillment_status',
        'tracking_number',
        'snap_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (! is_string($order->getKey()) || $order->getKey() === '') {
                $order->setAttribute('id', 'ORD-'.now()->format('Ymd').'-'.Str::ulid());
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'shipping_cost' => 'integer',
            'subtotal' => 'integer',
            'grand_total' => 'integer',
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
