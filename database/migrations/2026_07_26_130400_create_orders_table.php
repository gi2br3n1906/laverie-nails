<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_id')->nullable()->index();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30);
            $table->text('shipping_address');
            $table->string('province_id');
            $table->string('city_id');
            $table->string('courier');
            $table->unsignedBigInteger('shipping_cost');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('grand_total');
            $table->string('payment_status')->default('pending')->index();
            $table->string('fulfillment_status')->default('pending')->index();
            $table->string('tracking_number')->nullable();
            $table->string('snap_token')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
