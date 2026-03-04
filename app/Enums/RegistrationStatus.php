<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case AWAITING_VERIFICATION = 'awaiting_verification';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::AWAITING_VERIFICATION => 'Menunggu Verifikasi',
            self::VERIFIED => 'Terverifikasi',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::EXPIRED => 'Kadaluarsa',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'warning',
            self::AWAITING_VERIFICATION => 'info',
            self::VERIFIED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED, self::EXPIRED => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function colors(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->color()])
            ->toArray();
    }
}
