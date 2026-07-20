<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Measurement;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;

class MeasurementService
{
    public function __construct(
        private readonly NailClassifierService $nailClassifierService,
        private readonly ConnectionInterface $database,
    ) {}

    /**
     * @param  array<string, array<string, int|float|string>|null>  $attributes
     */
    public function classifyAndStore(array $attributes, ?User $user): Measurement
    {
        /** @var array<string, int|float|string> $rightHandData */
        $rightHandData = $this->normalizeHand($attributes['right_hand_data']);
        /** @var array<string, int|float|string>|null $leftHandData */
        $leftHandData = isset($attributes['left_hand_data'])
            ? $this->normalizeHand($attributes['left_hand_data'])
            : null;

        return $this->database->transaction(function () use ($rightHandData, $leftHandData, $user): Measurement {
            $rightResult = $this->nailClassifierService->classifyHand($rightHandData);
            $leftResult = $leftHandData === null
                ? null
                : $this->nailClassifierService->classifyHand($leftHandData);

            return Measurement::query()->create([
                'user_id' => $user?->id,
                'right_hand_data' => $rightHandData,
                'left_hand_data' => $leftHandData,
                'classified_size_right' => $rightResult['size'],
                'classified_size_left' => $leftResult['size'] ?? null,
                'confidence_score_right' => $rightResult['confidence'],
                'confidence_score_left' => $leftResult['confidence'] ?? null,
            ]);
        });
    }

    /** @return Collection<int, Measurement> */
    public function historyFor(User $user): Collection
    {
        $query = Measurement::query()->latest();

        if (! $user->hasRole(UserRole::Admin)) {
            $query->whereBelongsTo($user);
        }

        return $query->get();
    }

    public function delete(Measurement $measurement): void
    {
        $measurement->delete();
    }

    /**
     * @param  array<string, int|float|string>  $hand
     * @return array<string, float>
     */
    private function normalizeHand(array $hand): array
    {
        return array_map(static fn (int|float|string $value): float => (float) $value, $hand);
    }
}
