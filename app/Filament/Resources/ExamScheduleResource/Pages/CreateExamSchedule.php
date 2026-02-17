<?php

namespace App\Filament\Resources\ExamScheduleResource\Pages;

use App\Filament\Resources\ExamScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExamSchedule extends CreateRecord
{
    protected static string $resource = ExamScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
