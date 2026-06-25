<?php

namespace App\Filament\Actions;

use App\Models\Registration;
use App\Services\PaymentVerificationService;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\Action;

class RejectPaymentAction
{
    public static function make(): Action
    {
        return Action::make('reject')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->form([
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->rows(3)
                    ->maxLength(65535),
            ])
            ->modalHeading('Tolak Pembayaran')
            ->modalDescription('Berikan alasan penolakan pembayaran ini.')
            ->modalSubmitActionLabel('Ya, Tolak')
            ->visible(function (Registration $record): bool {
                $service = app(PaymentVerificationService::class);
                return $service->canReject($record, auth()->user());
            })
            ->action(function (Registration $record, array $data): void {
                $service = app(PaymentVerificationService::class);
                $service->reject($record, $data['rejection_reason'], auth()->user());
            });
    }
}
