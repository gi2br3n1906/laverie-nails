<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_reviews', function (Blueprint $table): void {
            $table->dropForeign(['catalog_id']);
            $table->dropUnique(['catalog_id', 'user_id']);
            $table->renameColumn('catalog_id', 'product_id');
        });

        Schema::table('catalog_reviews', function (Blueprint $table): void {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['product_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('catalog_reviews', function (Blueprint $table): void {
            $table->dropUnique(['product_id', 'user_id']);
            $table->dropForeign(['product_id']);
            $table->renameColumn('product_id', 'catalog_id');
        });

        Schema::table('catalog_reviews', function (Blueprint $table): void {
            $table->foreign('catalog_id')->references('id')->on('nail_catalogs')->cascadeOnDelete();
            $table->unique(['catalog_id', 'user_id']);
        });
    }
};
