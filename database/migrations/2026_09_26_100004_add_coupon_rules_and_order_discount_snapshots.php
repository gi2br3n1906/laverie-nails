<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->string('discount_type')->default('percentage');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('minimum_order_amount')->default(0);
            $table->timestamp('expires_at')->nullable();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('coupon_code')->nullable();
            $table->unsignedBigInteger('discount_amount')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['coupon_code', 'discount_amount']);
        });

        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropColumn(['discount_type', 'discount_amount', 'minimum_order_amount', 'expires_at']);
        });
    }
};
