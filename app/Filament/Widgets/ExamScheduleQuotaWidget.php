<?php

namespace App\Filament\Widgets;

use App\Models\ExamSchedule;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class ExamScheduleQuotaWidget extends TableWidget
{
    protected static ?string $heading = 'Kuota Jadwal Ujian';

    protected int|string|array $columnSpan = 'full';

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->label('Jadwal')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('exam_date')
                ->label('Tanggal')
                ->date('d F Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('session')
                ->label('Sesi'),

            Tables\Columns\TextColumn::make('registrations_count')
                ->label('Terdaftar'),

            Tables\Columns\TextColumn::make('quota')
                ->label('Kuota'),

            Tables\Columns\TextColumn::make('available')
                ->label('Tersedia')
                ->getStateUsing(function (Model $record): int {
                    return $record->quota - $record->registrations_count;
                })
                ->color(function (Model $record): string {
                    $available = $record->quota - $record->registrations_count;
                    if ($available <= 0) {
                        return 'danger';
                    } elseif ($available <= 5) {
                        return 'warning';
                    }
                    return 'success';
                }),

            Tables\Columns\TextColumn::make('percentage')
                ->label('Kepadatan')
                ->getStateUsing(function (Model $record): string {
                    if ($record->quota > 0) {
                        $percentage = round(($record->registrations_count / $record->quota) * 100);
                        return $percentage . '%';
                    }
                    return '0%';
                })
                ->color(function (Model $record): string {
                    $percentage = $record->quota > 0 
                        ? ($record->registrations_count / $record->quota) * 100 
                        : 0;
                    
                    if ($percentage >= 100) {
                        return 'danger';
                    } elseif ($percentage >= 80) {
                        return 'warning';
                    }
                    return 'success';
                }),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return ExamSchedule::query()
            ->withCount(['registrations' => function ($query) {
                $query->whereIn('status', ['pending_payment', 'awaiting_verification', 'verified']);
            }])
            ->orderBy('exam_date', 'desc')
            ->limit(10);
    }
}
