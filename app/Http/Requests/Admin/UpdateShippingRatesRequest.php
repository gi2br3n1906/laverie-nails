<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingRatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) === true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'java_cost' => ['required', 'integer', 'min:0', 'max:1000000'],
            'outside_java_cost' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'java_cost.required' => 'Tarif Pulau Jawa wajib diisi.',
            'java_cost.integer' => 'Tarif Pulau Jawa harus berupa angka.',
            'outside_java_cost.required' => 'Tarif luar Pulau Jawa wajib diisi.',
            'outside_java_cost.integer' => 'Tarif luar Pulau Jawa harus berupa angka.',
        ];
    }
}
