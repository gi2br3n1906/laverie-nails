<?php

declare(strict_types=1);

return [
    'accepted' => ':attribute harus disetujui.',
    'array' => ':attribute harus berupa array.',
    'boolean' => ':attribute harus berupa nilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'array' => ':attribute tidak boleh lebih dari :max item.',
        'file' => ':attribute tidak boleh lebih dari :max kilobita.',
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes' => ':attribute harus berupa file dengan tipe: :values.',
    'min' => [
        'array' => ':attribute harus memiliki paling sedikit :min item.',
        'file' => ':attribute harus paling sedikit :min kilobita.',
        'numeric' => ':attribute harus paling sedikit :min.',
        'string' => ':attribute harus paling sedikit :min karakter.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'required' => ':attribute wajib diisi.',
    'required_if' => ':attribute wajib diisi ketika :other bernilai :value.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',
    'url' => ':attribute harus berupa URL yang valid.',

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'password',
        'phone' => 'nomor telepon',
        'address' => 'alamat',
        'customer_name' => 'nama lengkap',
        'customer_email' => 'email',
        'customer_phone' => 'nomor telepon',
        'shipping_address' => 'alamat rinci',
    ],
];
