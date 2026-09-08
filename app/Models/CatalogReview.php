<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CatalogReviewFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogReview extends Model
{
    /** @use HasFactory<CatalogReviewFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['product_id', 'user_id', 'rating', 'comment'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): Factory
    {
        return CatalogReviewFactory::new();
    }
}
