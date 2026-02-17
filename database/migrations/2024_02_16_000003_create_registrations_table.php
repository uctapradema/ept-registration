<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('exam_schedule_id')
                ->constrained('exam_schedules')
                ->onDelete('restrict');
            $table->string('registration_number')->unique();
            $table->enum('status', [
                'pending_payment',
                'awaiting_verification',
                'verified',
                'rejected',
                'expired',
                'completed',
            ])->default('pending_payment');
            $table->string('payment_proof')->nullable();
            $table->timestamp('payment_uploaded_at')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['exam_schedule_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
