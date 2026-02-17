<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamScheduleResource\Pages;
use App\Models\ExamSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamScheduleResource extends Resource
{
    protected static ?string $model = ExamSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Jadwal Ujian';

    protected static ?string $modelLabel = 'Jadwal Ujian';

    protected static ?string $pluralModelLabel = 'Jadwal Ujian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Ujian')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: EPT Batch 1 2024'),

                        Forms\Components\DatePicker::make('exam_date')
                            ->label('Tanggal Ujian')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y'),

                        Forms\Components\Select::make('session')
                            ->label('Sesi')
                            ->required()
                            ->options(\App\Models\ExamSchedule::getSessionOptions())
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $times = \App\Models\ExamSchedule::getSessionTimes($state);
                                $set('start_time', $times['start']);
                                $set('end_time', $times['end']);
                            }),

                        Forms\Components\TextInput::make('quota')
                            ->label('Kuota')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(50)
                            ->helperText('Jumlah maksimum peserta yang dapat mendaftar'),

                        Forms\Components\DateTimePicker::make('registration_deadline')
                            ->label('Batas Pendaftaran')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y, H:i'),

                        Forms\Components\TextInput::make('payment_deadline_hours')
                            ->label('Batas Waktu Pembayaran (Jam)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(168)
                            ->default(24)
                            ->helperText('Batas waktu mahasiswa untuk upload bukti pembayaran setelah mendaftar (1-168 jam)'),

                        Forms\Components\TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->default(0)
                            ->step(1000),

                        Forms\Components\Section::make('Informasi Rekening Pembayaran')
                            ->schema([
                                Forms\Components\TextInput::make('bank_name')
                                    ->label('Nama Bank')
                                    ->required()
                                    ->default('Bank BCA')
                                    ->placeholder('Contoh: Bank BCA, Bank Mandiri'),

                                Forms\Components\TextInput::make('bank_account')
                                    ->label('Nomor Rekening')
                                    ->required()
                                    ->default('123-456-7890')
                                    ->placeholder('Contoh: 123-456-7890'),

                                Forms\Components\TextInput::make('account_holder')
                                    ->label('Nama Pemilik Rekening')
                                    ->required()
                                    ->default('EPT')
                                    ->placeholder('Contoh: EPT Universitas'),
                            ])->collapsed(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->required()
                            ->default(true)
                            ->helperText('Jadwal yang tidak aktif tidak akan ditampilkan di halaman pendaftaran'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('session')
                    ->label('Sesi')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        '01' => '01 - Pagi',
                        '02' => '02 - Siang',
                        '03' => '03 - Sore',
                        default => '-',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('exam_date')
                    ->label('Tanggal Ujian')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu')
                    ->formatStateUsing(fn (ExamSchedule $record): string => $record->start_time->format('H:i') . ' - ' . $record->end_time->format('H:i')),

                Tables\Columns\TextColumn::make('quota')
                    ->label('Kuota')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('registered_count')
                    ->label('Terdaftar')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (ExamSchedule $record): string => $record->registeredCount() . ' / ' . $record->quota)
                    ->color(fn (ExamSchedule $record): string => $record->registeredCount() >= $record->quota ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('payment_deadline_hours')
                    ->label('Batas Bayar')
                    ->formatStateUsing(fn (int $state): string => $state . ' jam')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\Filter::make('exam_date')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('exam_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('exam_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('exam_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamSchedules::route('/'),
            'create' => Pages\CreateExamSchedule::route('/create'),
            'edit' => Pages\EditExamSchedule::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', ExamSchedule::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', ExamSchedule::class) ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('update', $record) ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('delete', $record) ?? false;
    }
}
