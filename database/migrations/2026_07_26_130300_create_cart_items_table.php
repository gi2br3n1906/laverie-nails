<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('size_type');
            $table->json('size_payload');
            $table->string('size_signature', 64)->index();
            $table->timestamps();

            $table->index(['user_id', 'product_id', 'size_signature']);
            $table->index(['session_id', 'product_id', 'size_signature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
