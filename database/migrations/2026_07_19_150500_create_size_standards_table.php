<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_standards', function (Blueprint $table): void {
            $table->id();
            $table->string('size_name')->unique();
            $table->decimal('jempol', 4, 1);
            $table->decimal('telunjuk', 4, 1);
            $table->decimal('tengah', 4, 1);
            $table->decimal('manis', 4, 1);
            $table->decimal('kelingking', 4, 1);
            $table->decimal('tolerance', 3, 1)->default(1.0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_standards');
    }
};
