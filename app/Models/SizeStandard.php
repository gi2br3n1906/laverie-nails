<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SizeStandardFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizeStandard extends Model
{
    /** @use HasFactory<SizeStandardFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'size_name',
        'jempol',
        'telunjuk',
        'tengah',
        'manis',
        'kelingking',
        'tolerance',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jempol' => 'decimal:1',
            'telunjuk' => 'decimal:1',
            'tengah' => 'decimal:1',
            'manis' => 'decimal:1',
            'kelingking' => 'decimal:1',
            'tolerance' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): Factory
    {
        return SizeStandardFactory::new();
    }
}
