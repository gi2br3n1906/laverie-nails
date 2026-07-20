<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('right_hand_data');
            $table->json('left_hand_data')->nullable();
            $table->string('classified_size_right');
            $table->string('classified_size_left')->nullable();
            $table->decimal('confidence_score_right', 5, 2);
            $table->decimal('confidence_score_left', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
