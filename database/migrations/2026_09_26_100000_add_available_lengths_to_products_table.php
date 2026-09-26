<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('available_lengths')->nullable()->after('available_sizes');
        });

        DB::table('products')->whereNull('available_lengths')->update([
            'available_lengths' => json_encode(['Short', 'Medium', 'Long']),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('available_lengths');
        });
    }
};
