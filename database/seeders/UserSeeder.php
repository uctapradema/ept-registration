<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Admin user
            $admin = User::firstOrCreate(
                ['email' => 'admin@ept.com'],
                [
                    'name' => 'Admin',
                    'email' => 'admin@ept.com',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                ]
            );
            $admin->assignRole('admin');

            // Finance user
            $finance = User::firstOrCreate(
                ['email' => 'finance@ept.com'],
                [
                    'name' => 'Finance',
                    'email' => 'finance@ept.com',
                    'password' => Hash::make('password'),
                    'role' => 'finance',
                ]
            );
            $finance->assignRole('finance');

            // Sample Mahasiswa
            $mahasiswa = User::firstOrCreate(
                ['email' => 'john@student.com'],
                [
                    'name' => 'John Doe',
                    'email' => 'john@student.com',
                    'password' => Hash::make('password'),
                    'role' => 'mahasiswa',
                    'nim' => '2024001',
                    'major' => 'Teknik Informatika',
                    'faculty' => 'Fakultas Teknik',
                ]
            );
            $mahasiswa->assignRole('mahasiswa');

        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }
}
