<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Enums\RegistrationStatus;
use App\Events\RegistrationStatusChanged;
use App\Models\ExamSchedule;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function createRegistration(User $user, ExamSchedule $schedule): Registration
    {
        return DB::transaction(function () use ($user, $schedule) {
            $lockedSchedule = ExamSchedule::where('id', $schedule->id)
                ->lockForUpdate()
                ->first();

            if ($lockedSchedule->availableQuota() <= 0) {
                throw new \RuntimeException('Kuota untuk jadwal ini sudah penuh.');
            }

            $registrationNumber = Registration::generateRegistrationNumber($schedule);
            $uniqueCode = Registration::generateUniqueCode($schedule);

            $registration = Registration::create([
                'user_id' => $user->id,
                'exam_schedule_id' => $schedule->id,
                'registration_number' => $registrationNumber,
                'status' => RegistrationStatus::PENDING_PAYMENT->value,
                'expires_at' => now()->addHours($schedule->payment_deadline_hours ?? AppConstants::DEFAULT_PAYMENT_DEADLINE_HOURS),
                'unique_code' => $uniqueCode,
            ]);

            event(new RegistrationStatusChanged($registration, null, RegistrationStatus::PENDING_PAYMENT));

            return $registration;
        });
    }

    public function cancelRegistration(Registration $registration, string $reason): void
    {
        DB::transaction(function () use ($registration, $reason) {
            $oldStatus = $registration->status;

            $registration->update([
                'status' => RegistrationStatus::CANCELLED->value,
                'rejection_reason' => $reason,
            ]);

            event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::CANCELLED));
        });
    }

    public function uploadPayment(Registration $registration, string $filePath, ?string $note = null): void
    {
        $oldStatus = $registration->status;

        $registration->update([
            'payment_proof' => $filePath,
            'payment_uploaded_at' => now(),
            'status' => RegistrationStatus::AWAITING_VERIFICATION->value,
            'payment_note' => $note,
        ]);

        event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::AWAITING_VERIFICATION));
    }
}
