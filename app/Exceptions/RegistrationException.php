<?php

namespace App\Exceptions;

use RuntimeException;

class RegistrationException extends RuntimeException
{
    public static function quotaFull(): self
    {
        return new self('Kuota untuk jadwal ini sudah penuh.');
    }

    public static function alreadyRegistered(): self
    {
        return new self('Anda sudah memiliki pendaftaran aktif.');
    }

    public static function scheduleUnavailable(): self
    {
        return new self('Jadwal ini tidak tersedia untuk pendaftaran.');
    }

    public static function paymentExpired(): self
    {
        return new self('Batas waktu pembayaran telah habis.');
    }

    public static function cannotBeCancelled(): self
    {
        return new self('Pendaftaran tidak dapat dibatalkan.');
    }

    public static function invalidPaymentStatus(): self
    {
        return new self('Pembayaran sudah dilakukan atau status tidak valid.');
    }

    public static function examCardNotAvailable(): self
    {
        return new self('Kartu ujian hanya tersedia untuk pendaftaran yang telah terverifikasi.');
    }
}
