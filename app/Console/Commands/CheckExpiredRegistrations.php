<?php

namespace App\Console\Commands;

use App\Models\ExamSchedule;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckExpiredRegistrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registrations:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and expire registrations that have passed their payment deadline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $expiredCount = 0;

        try {
            DB::beginTransaction();

            $expiredRegistrations = Registration::whereIn('status', [
                'pending_payment',
                'awaiting_verification',
            ])
                ->where('expires_at', '<', $now)
                ->lockForUpdate()
                ->get();

            $this->info("Found {$expiredRegistrations->count()} expired registrations.");

            foreach ($expiredRegistrations as $registration) {
                $registration->update([
                    'status' => 'expired',
                ]);

                Log::info('Registration expired', [
                    'registration_id' => $registration->id,
                    'registration_number' => $registration->registration_number,
                    'user_id' => $registration->user_id,
                    'exam_schedule_id' => $registration->exam_schedule_id,
                    'expired_at' => $now,
                    'original_expires_at' => $registration->expires_at,
                ]);

                $expiredCount++;
            }

            DB::commit();

            $this->info("Successfully expired {$expiredCount} registrations.");
            Log::info('CheckExpiredRegistrations command completed', [
                'processed_count' => $expiredCount,
                'timestamp' => $now,
            ]);

            return self::SUCCESS;

        } catch (QueryException $e) {
            DB::rollBack();

            $this->error('Database error: ' . $e->getMessage());
            Log::error('CheckExpiredRegistrations database error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql' => $e->getSql() ?? null,
            ]);

            return self::FAILURE;

        } catch (Throwable $e) {
            DB::rollBack();

            $this->error('Error: ' . $e->getMessage());
            Log::error('CheckExpiredRegistrations error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
