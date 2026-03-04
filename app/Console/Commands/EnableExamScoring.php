<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Models\ExamSchedule;
use Illuminate\Console\Command;

class EnableExamScoring extends Command
{
    protected $signature = 'exam:enable-scoring';

    protected $description = 'Enable scoring for registrations after 2 hours from exam start time';

    public function handle(): int
    {
        $this->info('Checking registrations to enable scoring...');

        $examSchedules = ExamSchedule::where('is_active', true)->get();
        
        $enabledCount = 0;

        foreach ($examSchedules as $schedule) {
            $examStartTime = $schedule->start_time;
            $examEndTime = $examStartTime->copy()->addHours(2);

            if (now()->gte($examEndTime)) {
                $count = Registration::where('exam_schedule_id', $schedule->id)
                    ->where('status', 'verified')
                    ->where('ready_for_scoring', false)
                    ->whereNull('graded_at')
                    ->update([
                        'ready_for_scoring' => true,
                        'exam_completed_at' => $examEndTime,
                    ]);

                if ($count > 0) {
                    $enabledCount += $count;
                    $this->info("Enabled scoring for {$count} registrations in schedule: {$schedule->title}");
                }
            }
        }

        $this->info("Total registrations enabled for scoring: {$enabledCount}");

        return Command::SUCCESS;
    }
}
