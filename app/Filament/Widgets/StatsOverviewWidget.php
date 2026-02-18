<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use App\Models\ExamSchedule;
use Filament\Widgets\ChartWidget;

class StatsOverviewWidget extends ChartWidget
{
    protected static ?string $heading = 'Statistik Pendaftaran';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $pendingPayment = Registration::where('status', 'pending_payment')->count();
        $awaitingVerification = Registration::where('status', 'awaiting_verification')->count();
        $verified = Registration::where('status', 'verified')->count();
        $rejected = Registration::where('status', 'rejected')->count();
        $expired = Registration::where('status', 'expired')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Pendaftaran',
                    'data' => [$pendingPayment, $awaitingVerification, $verified, $rejected, $expired],
                    'backgroundColor' => [
                        '#F59E0B',
                        '#3B82F6',
                        '#10B981',
                        '#EF4444',
                        '#6B7280',
                    ],
                ],
            ],
            'labels' => [
                'Menunggu Pembayaran',
                'Menunggu Verifikasi',
                'Terverifikasi',
                'Ditolak',
                'Kedaluwarsa',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
