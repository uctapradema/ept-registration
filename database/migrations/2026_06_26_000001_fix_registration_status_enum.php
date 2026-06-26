<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->enum('status', [
                'pending_payment',
                'awaiting_verification',
                'verified',
                'rejected',
                'cancelled',
                'expired',
                'completed',
            ])->default('pending_payment')->change();
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->enum('status', [
                'pending_payment',
                'awaiting_verification',
                'verified',
                'rejected',
                'expired',
                'completed',
            ])->default('pending_payment')->change();
        });
    }
};
