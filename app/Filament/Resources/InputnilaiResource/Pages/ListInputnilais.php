<?php

namespace App\Filament\Resources\InputnilaiResource\Pages;

use App\Filament\Resources\InputnilaiResource;
use App\Models\ExamSchedule;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Forms\Components\Select;

class ListInputnilais extends ListRecords
{
    protected static string $resource = InputnilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('filter_jadwal')
                ->label('Filter Jadwal')
                ->icon('heroicon-o-funnel')
                ->color('gray')
                ->modalHeading('Pilih Jadwal Ujian')
                ->modalSubmitActionLabel('Terapkan')
                ->form([
                    Select::make('exam_schedule_id')
                        ->label('Jadwal Ujian')
                        ->options(ExamSchedule::all()->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $this->tableFilters = ['exam_schedule_id' => ['value' => $data['exam_schedule_id'] ?? null]];
                })
                ->fillForm(fn () => [
                    'exam_schedule_id' => $this->tableFilters['exam_schedule_id']['value'] ?? null,
                ]),
        ];
    }
}
