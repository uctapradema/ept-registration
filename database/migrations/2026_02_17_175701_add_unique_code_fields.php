<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->integer('unique_code_min')->nullable()->default(100);
            $table->integer('unique_code_max')->nullable()->default(999);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->integer('unique_code')->nullable()->after('payment_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn(['unique_code_min', 'unique_code_max']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('unique_code');
        });
    }
};
