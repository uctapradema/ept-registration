<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
                File::image()
                    ->types(['jpeg', 'png', 'jpg'])
                    ->and('application/pdf'),
            ],
            'payment_note' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_proof.required' => 'File bukti pembayaran wajib diupload.',
            'payment_proof.mimes' => 'File harus berformat JPG, JPEG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran file maksimal 5MB.',
            'payment_proof.file' => 'File tidak valid.',
            'payment_note.max' => 'Catatan maksimal :max karakter.',
        ];
    }
}
