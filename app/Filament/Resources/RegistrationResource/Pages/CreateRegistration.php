<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use App\Models\Registration;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $isAdmin = $user?->isAdmin() ?? false;

        if (!$isAdmin) {
            $data['user_id'] = auth()->id();
            $data['registration_number'] = 'EPT-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
            $data['status'] = 'pending_payment';
            $data['expires_at'] = now()->addDays(3);
        } else {
            if (empty($data['registration_number'])) {
                $schedule = \App\Models\ExamSchedule::find($data['exam_schedule_id']);
                $data['registration_number'] = Registration::generateRegistrationNumber($schedule);
                $data['unique_code'] = Registration::generateUniqueCode($schedule);
                $data['expires_at'] = now()->addHours($schedule->payment_deadline_hours ?? Registration::DEFAULT_PAYMENT_DEADLINE_HOURS);
            }
        }

        return $data;
    }
}
