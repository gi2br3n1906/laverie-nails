<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\ValueObjects\CartSize;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDefaultSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $rules = ['custom_measurements' => ['required', 'array']];

        foreach (CartSize::HANDS as $hand) {
            $rules["custom_measurements.{$hand}"] = ['required', 'array'];
            foreach (CartSize::FINGERS as $finger) {
                $rules["custom_measurements.{$hand}.{$finger}"] = ['required', 'numeric', 'between:0,25'];
            }
        }

        return $rules;
    }
}