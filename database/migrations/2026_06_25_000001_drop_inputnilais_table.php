<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('inputnilais');
    }

    public function down(): void
    {
        // Cannot be rolled back safely
    }
};
