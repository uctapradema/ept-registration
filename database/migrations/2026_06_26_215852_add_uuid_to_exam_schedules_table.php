<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
        });

        $schedules = DB::table('exam_schedules')->select('id')->get();
        foreach ($schedules as $schedule) {
            DB::table('exam_schedules')
                ->where('id', $schedule->id)
                ->update(['uuid' => Str::uuid()->toString()]);
        }
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
