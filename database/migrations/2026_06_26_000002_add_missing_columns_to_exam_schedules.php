<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->string('session')->nullable()->after('title');
            $table->integer('payment_deadline_hours')->nullable()->default(24)->after('registration_deadline');
            $table->string('bank_name')->nullable()->after('price');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->string('account_holder')->nullable()->after('bank_account');
        });
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'session',
                'payment_deadline_hours',
                'bank_name',
                'bank_account',
                'account_holder',
            ]);
        });
    }
};
