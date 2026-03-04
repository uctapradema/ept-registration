<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InputnilaiResource\Pages;
use App\Filament\Resources\InputnilaiResource\RelationManagers;
use App\Models\Inputnilai;
use App\Models\Registration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InputnilaiResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'Input Nilai';

    protected static ?string $navigationGroup = 'Manajemen Ujian';

    protected static ?string $modelLabel = 'Input Nilai';

    protected static ?string $pluralModelLabel = 'Input Nilai';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('Nomor Pendaftaran')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('examSchedule.title')
                    ->label('Jadwal Ujian')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('examSchedule.exam_date')
                    ->label('Tanggal Ujian')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('listening_score')
                    ->label('Listening')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('structure_score')
                    ->label('Structure')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reading_score')
                    ->label('Reading')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Rata-rata')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state && $state >= 450 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('graded_at')
                    ->label('Dinilai Pada')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_schedule_id')
                    ->label('Jadwal Ujian')
                    ->relationship('examSchedule', 'title')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('input_scores')
                    ->label('Input Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->modalHeading(fn (Registration $record) => $record->graded_at ? 'Edit Nilai Ujian' : 'Input Nilai Ujian')
                    ->modalSubmitActionLabel('Simpan')
                    ->form([
                        Forms\Components\Fieldset::make('Nilai Ujian')
                            ->schema([
                                Forms\Components\TextInput::make('listening_score')
                                    ->label('Listening')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required()
                                    ->suffix('/ 100'),
                                Forms\Components\TextInput::make('structure_score')
                                    ->label('Structure')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required()
                                    ->suffix('/ 100'),
                                Forms\Components\TextInput::make('reading_score')
                                    ->label('Reading')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required()
                                    ->suffix('/ 100'),
                            ]),
                    ])
                    ->action(function (Registration $record, array $data): void {
                        $average = round(($data['listening_score'] + $data['structure_score'] + $data['reading_score']) / 3, 2);
                        
                        $record->update([
                            'listening_score' => $data['listening_score'],
                            'structure_score' => $data['structure_score'],
                            'reading_score' => $data['reading_score'],
                            'average_score' => $average,
                            'graded_by' => auth()->user()?->id(),
                            'graded_at' => now(),
                            'ready_for_scoring' => false,
                        ]);
                    })
                    ->fillForm(fn (Registration $record): array => [
                        'listening_score' => $record->listening_score,
                        'structure_score' => $record->structure_score,
                        'reading_score' => $record->reading_score,
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'verified')
            ->where(function ($query) {
                $query->where('ready_for_scoring', true)
                    ->orWhereNotNull('graded_at');
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInputnilais::route('/'),
        ];
    }
}
