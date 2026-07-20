<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MeasurementFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    /** @use HasFactory<MeasurementFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'right_hand_data',
        'left_hand_data',
        'classified_size_right',
        'classified_size_left',
        'confidence_score_right',
        'confidence_score_left',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'right_hand_data' => 'array',
            'left_hand_data' => 'array',
            'confidence_score_right' => 'decimal:2',
            'confidence_score_left' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): Factory
    {
        return MeasurementFactory::new();
    }
}
