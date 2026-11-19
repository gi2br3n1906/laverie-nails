<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\HeroBannerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroBanner extends Model
{
    /** @use HasFactory<HeroBannerFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'image_path',
        'is_active',
        'placement',
        'sequence',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'placement' => 'string',
            'sequence' => 'integer',
        ];
    }

    /** @param  Builder<HeroBanner>  $query */
    public function scopeActiveOrdered(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sequence')->orderBy('id');
    }

    /** @param Builder<HeroBanner> $query */
    public function scopeForPlacement(Builder $query, string $placement): void
    {
        $query->where('placement', $placement)
            ->where('is_active', true)
            ->orderBy('sequence')
            ->orderByDesc('id');
    }
}
