<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('quota');
            $table->integer('registered_count')->default(0);
            $table->dateTime('registration_deadline');
            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['exam_date', 'is_active']);
            $table->index('registration_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
