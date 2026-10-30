<?php

declare(strict_types=1);

return [
    'locales' => [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ],

    'contact' => [
        'whatsapp_number' => env('LAVERIE_WHATSAPP_NUMBER', '6280000000000'),
        'instagram_url' => env('LAVERIE_INSTAGRAM_URL', 'https://www.instagram.com/'),
        'tiktok_url' => env('LAVERIE_TIKTOK_URL', 'https://www.tiktok.com/'),
    ],

    'admin' => [
        'email' => env('LAVERIE_ADMIN_EMAIL'),
    ],

    'demo_seed' => [
        // Production normally refuses the demo catalog. Set this only for a
        // deliberate presentation/demo database, then turn it off again.
        'allowed' => env('LAVERIE_ALLOW_DEMO_SEED', false),
    ],
];
