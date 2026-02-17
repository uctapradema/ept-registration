<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Pendaftaran';

    protected static ?string $modelLabel = 'Pendaftaran';

    protected static ?string $pluralModelLabel = 'Pendaftaran';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isFinance = $user?->isFinance() ?? false;

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pendaftaran')
                    ->schema([
                        Forms\Components\TextInput::make('registration_number')
                            ->label('Nomor Pendaftaran')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('user_id')
                            ->label('Peserta')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('exam_schedule_id')
                            ->label('Jadwal Ujian')
                            ->relationship('examSchedule', 'title')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending_payment' => 'Menunggu Pembayaran',
                                'awaiting_verification' => 'Menunggu Verifikasi',
                                'verified' => 'Terverifikasi',
                                'rejected' => 'Ditolak',
                                'expired' => 'Kadaluarsa',
                            ])
                            ->required()
                            ->disabled(fn (): bool => ! $isAdmin && ! $isFinance)
                            ->live(),

                        Forms\Components\Placeholder::make('payment_proof_display')
                            ->label('Bukti Pembayaran')
                            ->content(function ($record) {
                                if ($record && $record->payment_proof) {
                                    $imageUrl = asset('storage/' . $record->payment_proof);
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="mt-2">' .
                                        '<img src="' . $imageUrl . '" alt="Bukti Pembayaran" class="max-w-full h-auto rounded-lg border border-gray-300" style="max-height: 400px;">' .
                                        '<div class="mt-2">' .
                                        '<a href="' . $imageUrl . '" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm underline">Buka di tab baru</a>' .
                                        '</div>' .
                                        '</div>'
                                    );
                                }
                                return 'Belum ada bukti pembayaran';
                            }),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->rows(3)
                            ->maxLength(65535)
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'rejected')
                            ->disabled(fn (): bool => ! $isAdmin && ! $isFinance)
                            ->required(fn (Forms\Get $get): bool => $get('status') === 'rejected')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('payment_uploaded_at')
                            ->label('Waktu Upload Pembayaran')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DateTimePicker::make('payment_verified_at')
                            ->label('Waktu Verifikasi')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('verified_by')
                            ->label('Diverifikasi Oleh')
                            ->relationship('verifiedBy', 'name')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_payment' => 'warning',
                        'awaiting_verification' => 'info',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_payment' => 'Menunggu Pembayaran',
                        'awaiting_verification' => 'Menunggu Verifikasi',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        'expired' => 'Kadaluarsa',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_uploaded_at')
                    ->label('Upload Pembayaran')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d F Y, H:i') : '-'),

                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Bukti Bayar')
                    ->disk('public')
                    ->size(40)
                    ->circular()
                    ->toggleable()
                    ->visible(fn ($record) => $record && $record->payment_proof),

                Tables\Columns\TextColumn::make('payment_verified_at')
                    ->label('Waktu Verifikasi')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('verifiedBy.name')
                    ->label('Diverifikasi Oleh')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending_payment' => 'Menunggu Pembayaran',
                        'awaiting_verification' => 'Menunggu Verifikasi',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        'expired' => 'Kadaluarsa',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('exam_schedule_id')
                    ->label('Jadwal Ujian')
                    ->relationship('examSchedule', 'title')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran')
                    ->modalDescription('Apakah Anda yakin ingin memverifikasi pembayaran ini?')
                    ->modalSubmitActionLabel('Ya, Verifikasi')
                    ->visible(fn (Registration $record): bool =>
                        in_array($record->status, ['awaiting_verification', 'pending_payment']) &&
                        (auth()->user()?->isAdmin() || auth()->user()?->isFinance())
                    )
                    ->action(function (Registration $record): void {
                        $record->update([
                            'status' => 'verified',
                            'payment_verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                    }),

                Tables\Actions\Action::make('reject')
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
                    ->visible(fn (Registration $record): bool =>
                        in_array($record->status, ['awaiting_verification', 'pending_payment', 'verified']) &&
                        (auth()->user()?->isAdmin() || auth()->user()?->isFinance())
                    )
                    ->action(function (Registration $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'payment_verified_at' => null,
                            'verified_by' => null,
                        ]);
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isMahasiswa()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'view' => Pages\ViewRegistration::route('/{record}'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', Registration::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', Registration::class) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can('view', $record) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('update', $record) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete', $record) ?? false;
    }
}
