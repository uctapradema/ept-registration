<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['mahasiswa', 'admin', 'finance'])
                ->default('mahasiswa')
                ->after('password');
            $table->string('nim')
                ->unique()
                ->nullable()
                ->after('role');
            $table->string('phone')
                ->nullable()
                ->after('nim');
            $table->string('major')
                ->nullable()
                ->after('phone');
            $table->string('faculty')
                ->nullable()
                ->after('major');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nim']);
            $table->dropColumn(['role', 'nim', 'phone', 'major', 'faculty', 'deleted_at']);
        });
    }
};
