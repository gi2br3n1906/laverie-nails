<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'available_sizes',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'available_sizes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<ProductImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sequence')->orderBy('id');
    }

    /** @return HasOne<ProductImage, $this> */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->orderBy('sequence')->orderBy('id');
    }

    /** @return HasMany<CartItem, $this> */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<CatalogReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(CatalogReview::class);
    }

    /** @param  Builder<Product>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeSearchText(Builder $query, ?string $search): void
    {
        if (trim((string) $search) === '') {
            return;
        }

        $term = '%'.mb_strtolower(trim((string) $search)).'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->whereRaw('LOWER(name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
        });
    }

    /** @param  string|int|null $category */
    public function scopeCategory(Builder $query, mixed $category): void
    {
        if ($category === null || $category === '') {
            return;
        }

        if (is_numeric((string) $category)) {
            $query->where('category_id', (int) $category);

            return;
        }

        $query->whereHas('category', static function (Builder $query) use ($category): void {
            $query->where('slug', $category);
        });
    }

    public function scopeSize(Builder $query, ?string $size): void
    {
        if ($size === null || $size === '') {
            return;
        }

        $query->whereJsonContains('available_sizes', $size);
    }

    /**
     * @param  array{search?: string|null, category?: string|null, size?: string|null}  $filters
     */
    public function scopeCatalogFilters(Builder $query, array $filters): void
    {
        $query->searchText($filters['search'] ?? null)
            ->category($filters['category'] ?? null)
            ->size($filters['size'] ?? null);
    }
}
