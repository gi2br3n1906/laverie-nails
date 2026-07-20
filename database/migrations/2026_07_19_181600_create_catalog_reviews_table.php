<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalog_id')->constrained('nail_catalogs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->timestamps();
            $table->unique(['catalog_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_reviews');
    }
};
