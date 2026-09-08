<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email:rfc', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{8,30}$/'],
            'shipping_address' => ['required', 'string', 'min:10', 'max:1000'],
            'order_notes' => ['nullable', 'string', 'max:500'],
            'province_id' => ['required', 'string', 'max:30'],
            'city_id' => ['required', 'string', 'max:30'],
            'shipping_option' => ['required', 'string', 'regex:/^[a-z0-9_-]+:[A-Za-z0-9_-]+$/', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'customer_email.required' => 'Email wajib diisi.',
            'customer_email.email' => 'Format email belum valid.',
            'customer_phone.required' => 'Nomor telepon wajib diisi.',
            'customer_phone.regex' => 'Format nomor telepon belum valid.',
            'shipping_address.required' => 'Alamat lengkap wajib diisi.',
            'shipping_address.min' => 'Alamat lengkap minimal 10 karakter.',
            'province_id.required' => 'Provinsi wajib dipilih.',
            'city_id.required' => 'Kota atau kabupaten wajib dipilih.',
            'shipping_option.required' => 'Layanan pengiriman wajib dipilih.',
            'shipping_option.regex' => 'Layanan pengiriman tidak valid.',
        ];
    }
}
