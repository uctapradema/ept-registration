<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Events\RegistrationStatusChanged;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentVerificationService
{
    public function verify(Registration $registration, User $verifier): void
    {
        DB::transaction(function () use ($registration, $verifier) {
            $oldStatus = $registration->status;

            $registration->update([
                'status' => RegistrationStatus::VERIFIED->value,
                'payment_verified_at' => now(),
                'verified_by' => $verifier->id,
            ]);

            event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::VERIFIED));
        });
    }

    public function reject(Registration $registration, string $reason, User $rejector): void
    {
        DB::transaction(function () use ($registration, $reason, $rejector) {
            $oldStatus = $registration->status;

            $registration->update([
                'status' => RegistrationStatus::REJECTED->value,
                'rejection_reason' => $reason,
                'payment_verified_at' => null,
                'verified_by' => null,
            ]);

            event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::REJECTED));
        });
    }

    public function canVerify(Registration $registration, User $user): bool
    {
        return in_array($registration->status, [
            RegistrationStatus::AWAITING_VERIFICATION,
            RegistrationStatus::PENDING_PAYMENT,
        ]) && ($user->isAdmin() || $user->isFinance());
    }

    public function canReject(Registration $registration, User $user): bool
    {
        return in_array($registration->status, [
            RegistrationStatus::AWAITING_VERIFICATION,
            RegistrationStatus::PENDING_PAYMENT,
            RegistrationStatus::VERIFIED,
        ]) && ($user->isAdmin() || $user->isFinance());
    }
}
