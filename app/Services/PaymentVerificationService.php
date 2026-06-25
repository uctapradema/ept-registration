<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentVerificationService
{
    public function verify(Registration $registration, User $verifier): void
    {
        DB::transaction(function () use ($registration, $verifier) {
            $registration->update([
                'status' => RegistrationStatus::VERIFIED->value,
                'payment_verified_at' => now(),
                'verified_by' => $verifier->id,
            ]);

            $registration->load(['user', 'examSchedule']);
            $registration->user->notify(new \App\Notifications\PaymentVerifiedNotification($registration));
        });
    }

    public function reject(Registration $registration, string $reason, User $rejector): void
    {
        DB::transaction(function () use ($registration, $reason, $rejector) {
            $registration->update([
                'status' => RegistrationStatus::REJECTED->value,
                'rejection_reason' => $reason,
                'payment_verified_at' => null,
                'verified_by' => null,
            ]);

            $registration->load(['user', 'examSchedule']);
            $registration->user->notify(new \App\Notifications\PaymentRejectedNotification($registration));
        });
    }

    public function canVerify(Registration $registration, User $user): bool
    {
        return in_array($registration->status, [
            RegistrationStatus::AWAITING_VERIFICATION->value,
            RegistrationStatus::PENDING_PAYMENT->value,
        ]) && ($user->isAdmin() || $user->isFinance());
    }

    public function canReject(Registration $registration, User $user): bool
    {
        return in_array($registration->status, [
            RegistrationStatus::AWAITING_VERIFICATION->value,
            RegistrationStatus::PENDING_PAYMENT->value,
            RegistrationStatus::VERIFIED->value,
        ]) && ($user->isAdmin() || $user->isFinance());
    }
}
