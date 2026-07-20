<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nail_catalogs', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->json('images');
            $table->decimal('price', 10, 2);
            $table->string('size');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nail_catalogs');
    }
};
