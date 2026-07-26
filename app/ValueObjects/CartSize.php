<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\CartSizeType;

final readonly class CartSize
{
    /** @var list<string> */
    public const FINGERS = ['jempol', 'telunjuk', 'tengah', 'manis', 'kelingking'];

    /** @var list<string> */
    public const HANDS = ['right_hand', 'left_hand'];

    /** @param  array<string, mixed>  $payload */
    private function __construct(
        public CartSizeType $type,
        public array $payload,
        public string $signature,
    ) {}

    /** @param  array<string, mixed>  $validated */
    public static function fromValidated(array $validated): self
    {
        $type = CartSizeType::from((string) $validated['size_type']);
        $payload = $type === CartSizeType::Standard
            ? ['size' => (string) $validated['standard_size']]
            : self::canonicalCustomPayload($validated['custom_measurements']);
        $encodedPayload = json_encode($payload, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);

        return new self($type, $payload, hash('sha256', $encodedPayload));
    }

    /**
     * @param  array<string, array<string, float|int|string>>  $measurements
     * @return array<string, array<string, float|int>>
     */
    private static function canonicalCustomPayload(array $measurements): array
    {
        $payload = [];

        foreach (self::HANDS as $hand) {
            foreach (self::FINGERS as $finger) {
                $payload[$hand][$finger] = self::canonicalNumber($measurements[$hand][$finger]);
            }
        }

        return $payload;
    }

    private static function canonicalNumber(float|int|string $value): float|int
    {
        $number = (float) $value;

        return floor($number) === $number ? (int) $number : $number;
    }
}
