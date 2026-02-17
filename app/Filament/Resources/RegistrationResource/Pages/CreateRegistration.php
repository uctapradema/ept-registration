<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['registration_number'] = 'EPT-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
        $data['status'] = 'pending_payment';
        $data['expires_at'] = now()->addDays(3);

        return $data;
    }
}
