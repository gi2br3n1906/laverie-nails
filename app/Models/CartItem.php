<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CartSizeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'quantity',
        'size_type',
        'size_payload',
        'size_signature',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'size_type' => CartSizeType::class,
            'size_payload' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function subtotalInCents(): int
    {
        return $this->priceInCents() * $this->quantity;
    }

    private function priceInCents(): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $this->product->price, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
