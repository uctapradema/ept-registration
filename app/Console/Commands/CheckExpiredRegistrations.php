<?php

namespace App\Console\Commands;

use App\Enums\RegistrationStatus;
use App\Events\RegistrationStatusChanged;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckExpiredRegistrations extends Command
{
    protected $signature = 'registrations:check-expired';

    protected $description = 'Check and expire registrations that have passed their payment deadline';

    public function handle(): int
    {
        $now = Carbon::now();
        $expiredCount = 0;

        try {
            DB::beginTransaction();

            $expiredRegistrations = Registration::whereIn('status', [
                RegistrationStatus::PENDING_PAYMENT,
                RegistrationStatus::AWAITING_VERIFICATION,
            ])
                ->where('expires_at', '<', $now)
                ->lockForUpdate()
                ->get();

            $this->info("Found {$expiredRegistrations->count()} expired registrations.");

            foreach ($expiredRegistrations as $registration) {
                $oldStatus = $registration->status;

                $registration->update([
                    'status' => RegistrationStatus::EXPIRED->value,
                ]);

                event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::EXPIRED));

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
            ]);

            return self::FAILURE;

        } catch (Throwable $e) {
            DB::rollBack();

            $this->error('Error: ' . $e->getMessage());
            Log::error('CheckExpiredRegistrations error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return self::FAILURE;
        }
    }
}
