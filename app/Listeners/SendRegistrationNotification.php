<?php

namespace App\Listeners;

use App\Enums\RegistrationStatus;
use App\Events\RegistrationStatusChanged;
use App\Notifications\PaymentRejectedNotification;
use App\Notifications\PaymentVerifiedNotification;
use App\Notifications\RegistrationSuccessNotification;

class SendRegistrationNotification
{
    public function handle(RegistrationStatusChanged $event): void
    {
        $registration = $event->registration->load(['user', 'examSchedule']);

        match ($event->newStatus) {
            RegistrationStatus::PENDING_PAYMENT => $registration->user->notify(
                new RegistrationSuccessNotification($registration)
            ),
            RegistrationStatus::VERIFIED => $registration->user->notify(
                new PaymentVerifiedNotification($registration)
            ),
            RegistrationStatus::REJECTED => $registration->user->notify(
                new PaymentRejectedNotification($registration)
            ),
            default => null,
        };
    }
}
