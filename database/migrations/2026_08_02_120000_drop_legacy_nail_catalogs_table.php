<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('nail_catalogs');
    }

    public function down(): void
    {
        // The legacy catalog is intentionally not recreated after unification.
    }
};
