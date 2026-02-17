<?php

namespace Database\Seeders;

use App\Models\ExamSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Throwable;

class ExamScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Get or create an admin user for created_by
            $adminUser = User::where('role', 'admin')->first();

            if (! $adminUser) {
                throw new \Exception('Admin user not found. Please run UserSeeder first.');
            }

            $schedules = [
                [
                    'title' => 'Sesi Pagi',
                    'exam_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                    'start_time' => '08:00',
                    'end_time' => '10:00',
                    'quota' => 50,
                    'price' => 150000,
                ],
                [
                    'title' => 'Sesi Siang',
                    'exam_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                    'start_time' => '13:00',
                    'end_time' => '15:00',
                    'quota' => 30,
                    'price' => 150000,
                ],
                [
                    'title' => 'Sesi Sore',
                    'exam_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                    'start_time' => '16:00',
                    'end_time' => '18:00',
                    'quota' => 20,
                    'price' => 150000,
                ],
            ];

            foreach ($schedules as $scheduleData) {
                $examDate = Carbon::parse($scheduleData['exam_date']);
                $registrationDeadline = $examDate->copy()->subDays(2);

                ExamSchedule::firstOrCreate(
                    [
                        'title' => $scheduleData['title'],
                        'exam_date' => $scheduleData['exam_date'],
                    ],
                    [
                        'title' => $scheduleData['title'],
                        'exam_date' => $scheduleData['exam_date'],
                        'start_time' => $scheduleData['start_time'],
                        'end_time' => $scheduleData['end_time'],
                        'quota' => $scheduleData['quota'],
                        'registered_count' => 0,
                        'registration_deadline' => $registrationDeadline,
                        'price' => $scheduleData['price'],
                        'description' => null,
                        'is_active' => true,
                        'created_by' => $adminUser->id,
                    ]
                );
            }

        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }
}
