<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ExamSchedule;
use App\Models\Registration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating dummy data...');

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@ept.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nim' => null,
                'phone' => '081234567890',
                'major' => null,
                'faculty' => null,
            ]
        );
        $this->command->info('Created admin: admin@ept.test / password');

        // Create Finance
        $finance = User::firstOrCreate(
            ['email' => 'finance@ept.test'],
            [
                'name' => 'Finance Officer',
                'password' => Hash::make('password'),
                'role' => 'finance',
                'nim' => null,
                'phone' => '081234567891',
                'major' => null,
                'faculty' => null,
            ]
        );
        $this->command->info('Created finance: finance@ept.test / password');

        // Create Mahasiswa
        $mahasiswas = [
            ['name' => 'Ahmad Fauzi', 'nim' => '1234567890', 'email' => 'ahmad@ept.test', 'phone' => '081234567001', 'major' => 'Teknik Informatika', 'faculty' => 'FTI'],
            ['name' => 'Siti Nurhaliza', 'nim' => '1234567891', 'email' => 'siti@ept.test', 'phone' => '081234567002', 'major' => 'Sistem Informasi', 'faculty' => 'FTI'],
            ['name' => 'Budi Santoso', 'nim' => '1234567892', 'email' => 'budi@ept.test', 'phone' => '081234567003', 'major' => 'Teknik Komputer', 'faculty' => 'FTI'],
            ['name' => 'Dewi Lestari', 'nim' => '1234567893', 'email' => 'dewi@ept.test', 'phone' => '081234567004', 'major' => 'Manajemen Informatika', 'faculty' => 'FTI'],
            ['name' => 'Rudi Hermawan', 'nim' => '1234567894', 'email' => 'rudi@ept.test', 'phone' => '081234567005', 'major' => 'Teknik Informatika', 'faculty' => 'FTI'],
        ];

        $users = [];
        foreach ($mahasiswas as $mhs) {
            $user = User::firstOrCreate(
                ['email' => $mhs['email']],
                [
                    'name' => $mhs['name'],
                    'password' => Hash::make('password'),
                    'role' => 'mahasiswa',
                    'nim' => $mhs['nim'],
                    'phone' => $mhs['phone'],
                    'major' => $mhs['major'],
                    'faculty' => $mhs['faculty'],
                ]
            );
            $users[] = $user;
        }
        $this->command->info('Created 5 mahasiswa users');

        // Create Exam Schedules
        $schedules = [
            [
                'title' => 'EPT Regular - Januari 2026',
                'session' => '01',
                'exam_date' => '2026-02-15',
                'start_time' => '2026-02-15 09:00:00',
                'end_time' => '2026-02-15 11:00:00',
                'quota' => 30,
                'registration_deadline' => '2026-02-14 23:59:59',
                'payment_deadline_hours' => 24,
                'price' => 150000,
                'bank_name' => 'Bank Central Asia',
                'bank_account' => '1234567890',
                'account_holder' => 'PT EPT Indonesia',
                'description' => 'EPT Regular bulan Januari',
                'is_active' => true,
                'created_by' => $admin->id,
                'unique_code_min' => 100,
                'unique_code_max' => 999,
            ],
            [
                'title' => 'EPT Regular - Februari 2026',
                'session' => '01',
                'exam_date' => '2026-02-20',
                'start_time' => '2026-02-20 09:00:00',
                'end_time' => '2026-02-20 11:00:00',
                'quota' => 10,
                'registration_deadline' => '2026-02-19 23:59:59',
                'payment_deadline_hours' => 24,
                'price' => 150000,
                'bank_name' => 'Bank Central Asia',
                'bank_account' => '1234567890',
                'account_holder' => 'PT EPT Indonesia',
                'description' => 'EPT Regular bulan Februari - Kuota Terbatas',
                'is_active' => true,
                'created_by' => $admin->id,
                'unique_code_min' => 100,
                'unique_code_max' => 999,
            ],
            [
                'title' => 'EPT Regular - Maret 2026',
                'session' => '02',
                'exam_date' => '2026-03-01',
                'start_time' => '2026-03-01 13:00:00',
                'end_time' => '2026-03-01 15:00:00',
                'quota' => 5,
                'registration_deadline' => '2026-02-28 23:59:59',
                'payment_deadline_hours' => 24,
                'price' => 150000,
                'bank_name' => 'Bank Central Asia',
                'bank_account' => '1234567890',
                'account_holder' => 'PT EPT Indonesia',
                'description' => 'EPT Regular bulan Maret - Hampir Penuh',
                'is_active' => true,
                'created_by' => $admin->id,
                'unique_code_min' => 100,
                'unique_code_max' => 999,
            ],
            [
                'title' => 'EPT Regular - April 2026',
                'session' => '03',
                'exam_date' => '2026-04-15',
                'start_time' => '2026-04-15 15:30:00',
                'end_time' => '2026-04-15 17:30:00',
                'quota' => 50,
                'registration_deadline' => '2026-04-14 23:59:59',
                'payment_deadline_hours' => 24,
                'price' => 150000,
                'bank_name' => 'Bank Central Asia',
                'bank_account' => '1234567890',
                'account_holder' => 'PT EPT Indonesia',
                'description' => 'EPT Regular bulan April - Kuota Banyak',
                'is_active' => true,
                'created_by' => $admin->id,
                'unique_code_min' => 100,
                'unique_code_max' => 999,
            ],
        ];

        $examSchedules = [];
        foreach ($schedules as $schedule) {
            $examSchedule = ExamSchedule::firstOrCreate(
                ['title' => $schedule['title']],
                $schedule
            );
            $examSchedules[] = $examSchedule;
        }
        $this->command->info('Created 4 exam schedules');

        // Create Registrations with different statuses
        $registrations = [
            // Registration 1 - Pending Payment
            [
                'user_id' => $users[0]->id,
                'exam_schedule_id' => $examSchedules[0]->id,
                'registration_number' => 'EPT/01/15022026/0001',
                'status' => 'pending_payment',
                'unique_code' => 123,
                'expires_at' => now()->addHours(24),
            ],
            // Registration 2 - Awaiting Verification
            [
                'user_id' => $users[1]->id,
                'exam_schedule_id' => $examSchedules[0]->id,
                'registration_number' => 'EPT/01/15022026/0002',
                'status' => 'awaiting_verification',
                'payment_proof' => 'payment-proofs/dummy.jpg',
                'payment_uploaded_at' => now()->subHours(2),
                'unique_code' => 234,
                'expires_at' => now()->addHours(24),
            ],
            // Registration 3 - Verified (sudah verifikasi)
            [
                'user_id' => $users[2]->id,
                'exam_schedule_id' => $examSchedules[0]->id,
                'registration_number' => 'EPT/01/15022026/0003',
                'status' => 'verified',
                'payment_proof' => 'payment-proofs/dummy.jpg',
                'payment_uploaded_at' => now()->subDays(2),
                'payment_verified_at' => now()->subDays(2),
                'verified_by' => $admin->id,
                'unique_code' => 345,
                'expires_at' => now()->subDays(3),
            ],
            // Registration 4 - Verified dengan nilai (sudah dinilai)
            [
                'user_id' => $users[3]->id,
                'exam_schedule_id' => $examSchedules[0]->id,
                'registration_number' => 'EPT/01/15022026/0004',
                'status' => 'verified',
                'payment_proof' => 'payment-proofs/dummy.jpg',
                'payment_uploaded_at' => now()->subDays(3),
                'payment_verified_at' => now()->subDays(3),
                'verified_by' => $admin->id,
                'unique_code' => 456,
                'expires_at' => now()->subDays(4),
                'listening_score' => 80,
                'structure_score' => 75,
                'reading_score' => 85,
                'average_score' => 80.00,
                'graded_by' => $admin->id,
                'graded_at' => now()->subDays(2),
            ],
            // Registration 5 - Rejected
            [
                'user_id' => $users[4]->id,
                'exam_schedule_id' => $examSchedules[0]->id,
                'registration_number' => 'EPT/01/15022026/0005',
                'status' => 'rejected',
                'payment_proof' => 'payment-proofs/dummy.jpg',
                'payment_uploaded_at' => now()->subDays(5),
                'payment_verified_at' => now()->subDays(4),
                'verified_by' => $admin->id,
                'rejection_reason' => 'Bukti pembayaran tidak jelas',
                'unique_code' => 567,
                'expires_at' => now()->subDays(6),
            ],
            // Registration 6 - Verified di jadwal kedua (kuota terbatas)
            [
                'user_id' => $users[0]->id,
                'exam_schedule_id' => $examSchedules[1]->id,
                'registration_number' => 'EPT/01/20022026/0001',
                'status' => 'verified',
                'payment_proof' => 'payment-proofs/dummy.jpg',
                'payment_uploaded_at' => now()->subDays(2),
                'payment_verified_at' => now()->subDays(2),
                'verified_by' => $admin->id,
                'unique_code' => 111,
                'expires_at' => now()->subDays(3),
            ],
            // Registration 7 - Verified & ready for scoring
            [
                'user_id' => $users[1]->id,
                'exam_schedule_id' => $examSchedules[1]->id,
                'registration_number' => 'EPT/01/20022026/0002',
                'status' => 'verified',
                'payment_proof' => 'payment-proofs/dummy.jpg',
                'payment_uploaded_at' => now()->subDays(3),
                'payment_verified_at' => now()->subDays(3),
                'verified_by' => $admin->id,
                'unique_code' => 222,
                'expires_at' => now()->subDays(4),
                'ready_for_scoring' => true,
            ],
            // Registration 8 - Expired
            [
                'user_id' => $users[2]->id,
                'exam_schedule_id' => $examSchedules[1]->id,
                'registration_number' => 'EPT/01/20022026/0003',
                'status' => 'expired',
                'unique_code' => 333,
                'expires_at' => now()->subDays(1),
            ],
            // Registration 9 - Cancelled
            [
                'user_id' => $users[3]->id,
                'exam_schedule_id' => $examSchedules[2]->id,
                'registration_number' => 'EPT/02/01032026/0001',
                'status' => 'cancelled',
                'payment_proof' => 'payment-proofs/dummy.jpg',
                'payment_uploaded_at' => now()->subDays(10),
                'rejection_reason' => 'Mahasiswa membatalkan sendiri',
                'unique_code' => 444,
                'expires_at' => now()->subDays(9),
            ],
            // Registration 10 - Verified dengan nilai bagus
            [
                'user_id' => $users[4]->id,
                'exam_schedule_id' => $examSchedules[2]->id,
                'registration_number' => 'EPT/02/01032026/0002',
                'status' => 'verified',
                'payment_proof' => 'payment-proofs/dummy.jpg',
                'payment_uploaded_at' => now()->subDays(5),
                'payment_verified_at' => now()->subDays(5),
                'verified_by' => $admin->id,
                'unique_code' => 555,
                'expires_at' => now()->subDays(6),
                'listening_score' => 90,
                'structure_score' => 88,
                'reading_score' => 92,
                'average_score' => 90.00,
                'graded_by' => $admin->id,
                'graded_at' => now()->subDays(3),
            ],
        ];

        foreach ($registrations as $reg) {
            Registration::firstOrCreate(
                ['registration_number' => $reg['registration_number']],
                $reg
            );
        }
        $this->command->info('Created 10 registrations with various statuses');

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('Dummy Data Created Successfully!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('  Admin:  admin@ept.test / password');
        $this->command->info('  Finance: finance@ept.test / password');
        $this->command->info('  Mahasiswa: ahmad@ept.test / password');
        $this->command->info('');
        $this->command->info('Data Summary:');
        $this->command->info('  - Users: ' . User::count() . ' (1 admin, 1 finance, ' . User::where('role', 'mahasiswa')->count() . ' mahasiswa)');
        $this->command->info('  - Exam Schedules: ' . ExamSchedule::count());
        $this->command->info('  - Registrations: ' . Registration::count());
        $this->command->info('');
    }
}
