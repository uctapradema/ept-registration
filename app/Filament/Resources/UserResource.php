<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                            ->confirmed()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->maxLength(255),

                        Forms\Components\Select::make('role')
                            ->label('Peran')
                            ->options([
                                'admin' => 'Administrator',
                                'finance' => 'Keuangan',
                                'mahasiswa' => 'Mahasiswa',
                            ])
                            ->required()
                            ->default('mahasiswa')
                            ->live(),

                        Forms\Components\TextInput::make('nim')
                            ->label('NIM')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->visible(fn (Forms\Get $get): bool => $get('role') === 'mahasiswa')
                            ->required(fn (Forms\Get $get): bool => $get('role') === 'mahasiswa'),

                        Forms\Components\TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('major')
                            ->label('Program Studi')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => $get('role') === 'mahasiswa'),

                        Forms\Components\TextInput::make('faculty')
                            ->label('Fakultas')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => $get('role') === 'mahasiswa'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'finance' => 'warning',
                        'mahasiswa' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrator',
                        'finance' => 'Keuangan',
                        'mahasiswa' => 'Mahasiswa',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('No. Telepon')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('major')
                    ->label('Program Studi')
                    ->searchable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('faculty')
                    ->label('Fakultas')
                    ->searchable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Administrator',
                        'finance' => 'Keuangan',
                        'mahasiswa' => 'Mahasiswa',
                    ])
                    ->native(false),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', User::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', User::class) ?? false;
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
