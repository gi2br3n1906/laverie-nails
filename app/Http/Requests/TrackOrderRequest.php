<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_id' => is_string($this->order_id) ? strtoupper(trim($this->order_id)) : $this->order_id,
            'email' => is_string($this->email) ? strtolower(trim($this->email)) : $this->email,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string', 'regex:/^ORD-\d{8}-[0-9A-HJKMNP-TV-Z]{26}$/'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'order_id.regex' => 'Format Order ID tidak valid.',
        ];
    }
}
