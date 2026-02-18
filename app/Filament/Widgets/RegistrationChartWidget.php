<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\ChartWidget;

class RegistrationChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pendaftaran Per Bulan';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $data = $this->getMonthlyData();
        
        return [
            'datasets' => [
                [
                    'label' => 'Pendaftaran',
                    'data' => $data['counts'],
                    'backgroundColor' => '#F59E0B',
                    'borderColor' => '#F59E0B',
                ],
            ],
            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getMonthlyData(): array
    {
        $months = [];
        $counts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            $count = Registration::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $months[] = $monthName;
            $counts[] = $count;
        }

        return [
            'months' => $months,
            'counts' => $counts,
        ];
    }
}
