<?php

namespace App\Filament\Actions;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Services\PaymentVerificationService;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\Action;

class VerifyPaymentAction
{
    public static function make(): Action
    {
        return Action::make('verify')
            ->label('Verifikasi')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Verifikasi Pembayaran')
            ->modalDescription('Apakah Anda yakin ingin memverifikasi pembayaran ini?')
            ->modalSubmitActionLabel('Ya, Verifikasi')
            ->visible(function (Registration $record): bool {
                $service = app(PaymentVerificationService::class);
                return $service->canVerify($record, auth()->user());
            })
            ->action(function (Registration $record): void {
                $service = app(PaymentVerificationService::class);
                $service->verify($record, auth()->user());
            });
    }
}
