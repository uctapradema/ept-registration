<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_id' => 'required|exists:exam_schedules,id',
            'agreement' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'schedule_id.required' => 'Jadwal ujian wajib dipilih.',
            'schedule_id.exists' => 'Jadwal ujian tidak ditemukan.',
            'agreement.required' => 'Anda harus menyetujui ketentuan.',
            'agreement.accepted' => 'Anda harus menyetujui ketentuan untuk melanjutkan.',
        ];
    }
}
