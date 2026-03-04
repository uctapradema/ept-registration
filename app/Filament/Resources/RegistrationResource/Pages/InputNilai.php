<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Models\Registration;
use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class InputNilai extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected static ?string $title = 'Input Nilai Ujian';

    protected static ?string $navigationLabel = 'Input Nilai';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Manajemen Ujian';

    public function getHeading(): string
    {
        return 'Input Nilai Ujian';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->where('status', 'verified')
            ->where(function (Builder $query) {
                $query->where('ready_for_scoring', true)
                    ->orWhereNotNull('graded_at');
            });
    }

    protected function modifyTable($table)
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('No. Pendaftaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.nim')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('examSchedule.title')
                    ->label('Jadwal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('examSchedule.exam_date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('listening_score')
                    ->label('Listening')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('structure_score')
                    ->label('Structure')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reading_score')
                    ->label('Reading')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('average_score')
                    ->label('Rata-rata')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('graded_at')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->graded_at ? 'Sudah Dinilai' : 'Belum Dinilai')
                    ->colors([
                        'success' => fn ($state) => $state !== null,
                        'warning' => fn ($state) => $state === null,
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_schedule_id')
                    ->label('Jadwal Ujian')
                    ->relationship('examSchedule', 'title')
                    ->preload(),
            ]);
    }
}
