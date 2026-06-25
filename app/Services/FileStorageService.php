<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileStorageService
{
    public function storePaymentProof(UploadedFile $file, string $directory = 'payments', string $disk = 'public'): string
    {
        $extension = $file->getClientOriginalExtension();
        $safeExtension = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($extension));
        $randomName = bin2hex(random_bytes(16));
        $fileName = $randomName . '.' . $safeExtension;

        return $file->storeAs($directory, $fileName, $disk);
    }
}
