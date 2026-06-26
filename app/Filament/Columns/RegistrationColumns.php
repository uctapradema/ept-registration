<?php

namespace App\Filament\Columns;

use App\Enums\RegistrationStatus;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class RegistrationColumns
{
    public static function registrationNumber(): TextColumn
    {
        return TextColumn::make('registration_number')
            ->label('Nomor Pendaftaran')
            ->searchable()
            ->sortable()
            ->copyable();
    }

    public static function participantName(): TextColumn
    {
        return TextColumn::make('user.name')
            ->label('Peserta')
            ->searchable()
            ->sortable();
    }

    public static function participantNim(): TextColumn
    {
        return TextColumn::make('user.nim')
            ->label('NIM')
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    public static function examScheduleTitle(): TextColumn
    {
        return TextColumn::make('examSchedule.title')
            ->label('Jadwal Ujian')
            ->searchable()
            ->sortable()
            ->limit(30);
    }

    public static function examDate(): TextColumn
    {
        return TextColumn::make('examSchedule.exam_date')
            ->label('Tanggal Ujian')
            ->date('d F Y')
            ->sortable();
    }

    public static function statusBadge(): TextColumn
    {
        return TextColumn::make('status')
            ->label('Status')
            ->badge()
            ->color(fn (string $state): string => RegistrationStatus::colors()[$state] ?? 'gray')
            ->formatStateUsing(fn (string $state): string => RegistrationStatus::options()[$state] ?? $state)
            ->sortable();
    }

    public static function paymentUploadedAt(): TextColumn
    {
        return TextColumn::make('payment_uploaded_at')
            ->label('Upload Pembayaran')
            ->dateTime('d F Y, H:i')
            ->sortable()
            ->toggleable()
            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d F Y, H:i') : '-');
    }

    public static function paymentProofThumbnail(): Tables\Columns\ImageColumn
    {
        return Tables\Columns\ImageColumn::make('payment_proof')
            ->label('Bukti Bayar')
            ->disk('public')
            ->size(40)
            ->circular()
            ->toggleable()
            ->visible(fn ($record) => $record && $record->payment_proof);
    }

    public static function paymentVerifiedAt(): TextColumn
    {
        return TextColumn::make('payment_verified_at')
            ->label('Waktu Verifikasi')
            ->dateTime('d F Y, H:i')
            ->sortable()
            ->toggleable();
    }

    public static function verifiedByName(): TextColumn
    {
        return TextColumn::make('verifiedBy.name')
            ->label('Diverifikasi Oleh')
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    public static function listeningScore(): TextColumn
    {
        return TextColumn::make('listening_score')
            ->label('Listening')
            ->numeric()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    public static function structureScore(): TextColumn
    {
        return TextColumn::make('structure_score')
            ->label('Structure')
            ->numeric()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    public static function readingScore(): TextColumn
    {
        return TextColumn::make('reading_score')
            ->label('Reading')
            ->numeric()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    public static function createdAt(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label('Dibuat')
            ->dateTime('d F Y, H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
