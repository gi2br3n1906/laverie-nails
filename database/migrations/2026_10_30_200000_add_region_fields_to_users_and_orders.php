<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('province_code', 2)->nullable()->index();
            $table->string('province_name', 100)->nullable();
            $table->string('regency_code', 5)->nullable();
            $table->string('regency_name', 100)->nullable();
            $table->string('district_code', 8)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('village_code', 13)->nullable();
            $table->string('village_name', 100)->nullable();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('province_code', 2)->nullable()->index();
            $table->string('province_name', 100)->nullable();
            $table->string('regency_code', 5)->nullable();
            $table->string('regency_name', 100)->nullable();
            $table->string('district_code', 8)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('village_code', 13)->nullable();
            $table->string('village_name', 100)->nullable();
            $table->string('shipping_zone', 20)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'province_code',
                'province_name',
                'regency_code',
                'regency_name',
                'district_code',
                'district_name',
                'village_code',
                'village_name',
                'shipping_zone',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'province_code',
                'province_name',
                'regency_code',
                'regency_name',
                'district_code',
                'district_name',
                'village_code',
                'village_name',
            ]);
        });
    }
};
