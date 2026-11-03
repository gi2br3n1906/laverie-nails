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

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'java_cost' => ['required', 'integer', 'min:0', 'max:1000000'],
            'outside_java_cost' => ['required', 'integer', 'min:0', 'max:1000000'],
            'guidance_video_url' => ['nullable', 'url', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || $value === '') {
                    return;
                }

                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));
                $allowed = $host === 'youtu.be' || str_ends_with($host, '.youtube.com') || $host === 'youtube.com';

                if (! $allowed) {
                    $fail('Gunakan link video dari YouTube atau youtu.be.');
                }
            }],
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
            'guidance_video_url.url' => 'Link video harus berupa URL yang valid.',
        ];
    }
}
