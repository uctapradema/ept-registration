<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->integer('listening_score')->nullable()->after('payment_verified_at');
            $table->integer('structure_score')->nullable()->after('listening_score');
            $table->integer('reading_score')->nullable()->after('structure_score');
            $table->decimal('average_score', 5, 2)->nullable()->after('reading_score');
            $table->timestamp('exam_completed_at')->nullable()->after('average_score');
            $table->foreignId('graded_by')->nullable()->constrained('users')->after('exam_completed_at');
            $table->timestamp('graded_at')->nullable()->after('graded_by');
            $table->boolean('ready_for_scoring')->default(false)->after('graded_at');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropColumn([
                'listening_score',
                'structure_score',
                'reading_score',
                'average_score',
                'exam_completed_at',
                'graded_by',
                'graded_at',
                'ready_for_scoring',
            ]);
        });
    }
};
