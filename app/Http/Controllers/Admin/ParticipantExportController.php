<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $examScheduleId = $request->get('exam_schedule_id');

        $query = Registration::with(['user', 'examSchedule'])
            ->where('status', 'verified')
            ->orderBy('payment_verified_at', 'asc');

        if ($examScheduleId) {
            $query->where('exam_schedule_id', $examScheduleId);
        }

        $registrations = $query->get();
        $filename = $this->generateFilename($examScheduleId);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function () use ($registrations) {
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
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request): Response
    {
        $examScheduleId = $request->get('exam_schedule_id');

        $query = Registration::with(['user', 'examSchedule'])
            ->where('status', 'verified')
            ->orderBy('payment_verified_at', 'asc');

        if ($examScheduleId) {
            $query->where('exam_schedule_id', $examScheduleId);
        }

        $registrations = $query->get();

        return response()->make(
            view('filament.pages.participants-print', [
                'registrations' => $registrations,
            ])->render(),
            200,
            ['Content-Type' => 'text/html']
        );
    }

    private function generateFilename(?int $scheduleId): string
    {
        $base = 'daftar_peserta_ept';

        if ($scheduleId) {
            $examSchedule = \App\Models\ExamSchedule::find($scheduleId);
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
