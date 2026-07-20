<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SizeStandard;
use Illuminate\Database\Seeder;

class SizeStandardSeeder extends Seeder
{
    public function run(): void
    {
        $standards = [
            [
                'size_name' => 'XS',
                'jempol' => 14.0,
                'telunjuk' => 10.0,
                'tengah' => 11.0,
                'manis' => 10.0,
                'kelingking' => 8.0,
            ],
            [
                'size_name' => 'S',
                'jempol' => 15.0,
                'telunjuk' => 11.0,
                'tengah' => 12.0,
                'manis' => 11.0,
                'kelingking' => 9.0,
            ],
            [
                'size_name' => 'M',
                'jempol' => 16.0,
                'telunjuk' => 12.0,
                'tengah' => 13.0,
                'manis' => 12.0,
                'kelingking' => 10.0,
            ],
            [
                'size_name' => 'L',
                'jempol' => 17.0,
                'telunjuk' => 13.0,
                'tengah' => 14.0,
                'manis' => 13.0,
                'kelingking' => 11.0,
            ],
        ];

        foreach ($standards as $standard) {
            SizeStandard::query()->updateOrCreate(
                ['size_name' => $standard['size_name']],
                [
                    ...$standard,
                    'tolerance' => 1.0,
                    'is_active' => true,
                ],
            );
        }
    }
}
