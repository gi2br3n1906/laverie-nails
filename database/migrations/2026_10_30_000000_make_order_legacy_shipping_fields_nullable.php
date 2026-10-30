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
        // Legacy RajaOngkir fields are kept for old orders but are no longer
        // collected at checkout. New orders leave them empty until an admin
        // enters the courier and tracking number manually.
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('province_id')->nullable()->change();
            $table->string('city_id')->nullable()->change();
            $table->string('courier')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('orders')->whereNull('province_id')->update(['province_id' => '']);
        DB::table('orders')->whereNull('city_id')->update(['city_id' => '']);
        DB::table('orders')->whereNull('courier')->update(['courier' => '']);

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('province_id')->nullable(false)->change();
            $table->string('city_id')->nullable(false)->change();
            $table->string('courier')->nullable(false)->change();
        });
    }
};
