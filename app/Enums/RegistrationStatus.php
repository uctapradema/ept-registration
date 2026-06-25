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

    public function tailwindClasses(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            self::AWAITING_VERIFICATION => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            self::VERIFIED => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            self::REJECTED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            self::CANCELLED => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            self::EXPIRED => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
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
