<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CatalogSize;
use Database\Factories\NailCatalogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NailCatalog extends Model
{
    /** @use HasFactory<NailCatalogFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['title', 'description', 'images', 'price', 'size', 'is_active'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'size' => CatalogSize::class,
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<CatalogReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(CatalogReview::class, 'catalog_id');
    }

    /** @param Builder<NailCatalog> $query */
    public function scopePubliclyVisible(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<NailCatalog> $query */
    public function scopeForSize(Builder $query, ?CatalogSize $size): void
    {
        $query->when($size, fn (Builder $query, CatalogSize $size): Builder => $query->where('size', $size->value));
    }

    protected static function newFactory(): Factory
    {
        return NailCatalogFactory::new();
    }
}
