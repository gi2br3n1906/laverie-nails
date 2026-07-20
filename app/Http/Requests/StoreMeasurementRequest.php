<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeasurementRequest extends FormRequest
{
    /** @var list<string> */
    private const FINGERS = ['jempol', 'telunjuk', 'tengah', 'manis', 'kelingking'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $rules = [
            'right_hand_data' => ['required', 'array:'.implode(',', self::FINGERS)],
            'left_hand_data' => ['nullable', 'array:'.implode(',', self::FINGERS)],
        ];

        foreach (self::FINGERS as $finger) {
            $rules["right_hand_data.{$finger}"] = $this->millimeterRules('right_hand_data');
            $rules["left_hand_data.{$finger}"] = $this->millimeterRules('left_hand_data');
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    private function millimeterRules(string $hand): array
    {
        return ['required_with:'.$hand, 'numeric', 'min:0', 'max:25'];
    }
}
