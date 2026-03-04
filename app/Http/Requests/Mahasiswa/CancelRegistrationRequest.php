<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class CancelRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancel_reason' => 'required|string|min:10|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
            'cancel_reason.min' => 'Alasan pembatalan minimal :min karakter.',
            'cancel_reason.max' => 'Alasan pembatalan maksimal :max karakter.',
        ];
    }
}
