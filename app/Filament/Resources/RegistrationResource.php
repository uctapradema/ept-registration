<?php

namespace App\Filament\Resources;

use App\Enums\RegistrationStatus;
use App\Filament\Actions\RejectPaymentAction;
use App\Filament\Actions\VerifyPaymentAction;
use App\Filament\Columns\RegistrationColumns;
use App\Filament\Filters\RegistrationFilters;
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

    protected static ?string $navigationGroup = 'Manajemen Ujian';

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
                            ->options(function () {
                                return \App\Models\User::where('role', 'mahasiswa')
                                    ->pluck('name', 'id')
                                    ->mapWithKeys(function ($name, $id) {
                                        $user = \App\Models\User::find($id);
                                        return [$id => "{$name} ({$user->nim})"];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn (): bool => $isAdmin)
                            ->disabled(fn ($record): bool => $record !== null),

                        Forms\Components\Select::make('exam_schedule_id')
                            ->label('Jadwal Ujian')
                            ->relationship('examSchedule', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn (): bool => $isAdmin)
                            ->disabled(fn ($record): bool => $record !== null),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(RegistrationStatus::options())
                            ->required()
                            ->disabled(fn (): bool => ! $isAdmin && ! $isFinance)
                            ->live(),

                        Forms\Components\FileUpload::make('payment_proof')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->disk('public')
                            ->directory('payment-proofs')
                            ->visible(fn (): bool => $isAdmin)
                            ->preserveFilenames(),

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
                            })
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\DateTimePicker::make('payment_uploaded_at')
                            ->label('Waktu Upload Pembayaran')
                            ->visible(fn (): bool => $isAdmin)
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('payment_verified_at')
                            ->label('Waktu Verifikasi')
                            ->visible(fn (): bool => $isAdmin)
                            ->seconds(false),

                        Forms\Components\Select::make('verified_by')
                            ->label('Diverifikasi Oleh')
                            ->options(\App\Models\User::where('role', 'admin')->orWhere('role', 'finance')->pluck('name', 'id'))
                            ->visible(fn (): bool => $isAdmin)
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->actions(self::getActions())
            ->bulkActions(self::getBulkActions())
            ->defaultSort('created_at', 'desc');
    }

    private static function getColumns(): array
    {
        return [
            RegistrationColumns::registrationNumber(),
            RegistrationColumns::participantName(),
            RegistrationColumns::participantNim(),
            RegistrationColumns::examScheduleTitle(),
            RegistrationColumns::examDate(),
            RegistrationColumns::statusBadge(),
            RegistrationColumns::paymentUploadedAt(),
            RegistrationColumns::paymentProofThumbnail(),
            RegistrationColumns::paymentVerifiedAt(),
            RegistrationColumns::verifiedByName(),
            RegistrationColumns::listeningScore(),
            RegistrationColumns::structureScore(),
            RegistrationColumns::readingScore(),
            RegistrationColumns::createdAt(),
        ];
    }

    private static function getFilters(): array
    {
        return [
            RegistrationFilters::statusFilter(),
            RegistrationFilters::examScheduleFilter(),
        ];
    }

    private static function getActions(): array
    {
        return [
            VerifyPaymentAction::make(),
            RejectPaymentAction::make(),
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make()
                ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
        ];
    }

    private static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ]),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isMahasiswa()) {
            $query->forUser($user->id);
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
