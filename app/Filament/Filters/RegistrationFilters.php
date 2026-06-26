<?php

namespace App\Filament\Filters;

use App\Enums\RegistrationStatus;
use Filament\Forms;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class RegistrationFilters
{
    public static function statusFilter(): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Status')
            ->options(RegistrationStatus::options())
            ->native(false);
    }

    public static function examScheduleFilter(): SelectFilter
    {
        return SelectFilter::make('exam_schedule_id')
            ->label('Jadwal Ujian')
            ->relationship('examSchedule', 'title')
            ->searchable()
            ->preload()
            ->native(false);
    }

    public static function isActiveFilter(): TernaryFilter
    {
        return TernaryFilter::make('is_active')
            ->label('Status Aktif')
            ->placeholder('Semua')
            ->trueLabel('Aktif')
            ->falseLabel('Tidak Aktif');
    }

    public static function examDateRangeFilter(): Filter
    {
        return Filter::make('exam_date')
            ->label('Rentang Tanggal')
            ->form([
                Forms\Components\DatePicker::make('from')
                    ->label('Dari Tanggal')
                    ->native(false),
                Forms\Components\DatePicker::make('until')
                    ->label('Sampai Tanggal')
                    ->native(false),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['from'],
                        fn (Builder $query, $date): Builder => $query->whereDate('exam_date', '>=', $date)
                    )
                    ->when(
                        $data['until'],
                        fn (Builder $query, $date): Builder => $query->whereDate('exam_date', '<=', $date)
                    );
            });
    }
}
