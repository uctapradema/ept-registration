<?php

namespace App\Services;

use App\Models\ExamSchedule;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;

class ExportService
{
    public function getParticipantsQuery(?int $examScheduleId = null): Builder
    {
        $query = Registration::with(['user', 'examSchedule'])
            ->verified()
            ->orderBy('payment_verified_at', 'asc');

        if ($examScheduleId) {
            $query->forSchedule($examScheduleId);
        }

        return $query;
    }

    public function generateCsv(?int $examScheduleId = null): void
    {
        $registrations = $this->getParticipantsQuery($examScheduleId)->get();
        $filename = $this->generateFilename($examScheduleId);

        $handle = fopen('php://output', 'w');

        fputcsv($handle, ['No', 'No. Pendaftaran', 'Nama', 'NIM', 'Prodi', 'Fakultas', 'Jadwal', 'Tanggal Ujian', 'Tgl Verifikasi']);

        $no = 1;
        foreach ($registrations as $reg) {
            fputcsv($handle, [
                $no++,
                $reg->registration_number,
                $reg->user->name ?? '-',
                $reg->user->nim ?? '-',
                $reg->user->major ?? '-',
                $reg->user->faculty ?? '-',
                $reg->examSchedule->title ?? '-',
                $reg->examSchedule->exam_date?->format('d F Y') ?? '-',
                $reg->payment_verified_at?->format('d F Y, H:i') ?? '-',
            ]);
        }

        fclose($handle);
    }

    public function getPrintView(?int $examScheduleId = null): string
    {
        $registrations = $this->getParticipantsQuery($examScheduleId)->get();

        return view('filament.pages.participants-print', [
            'registrations' => $registrations,
        ])->render();
    }

    public function generateFilename(?int $scheduleId = null): string
    {
        $base = 'daftar_peserta_ept';

        if ($scheduleId) {
            $examSchedule = ExamSchedule::find($scheduleId);
            if ($examSchedule) {
                $title = str_replace(' ', '_', $examSchedule->title);
                $session = $examSchedule->session;
                $date = $examSchedule->exam_date?->format('Y-m-d') ?? date('Y-m-d');

                return "{$title}_Sesi{$session}_{$date}";
            }
        }

        return $base . '_' . date('Y-m-d');
    }
}
