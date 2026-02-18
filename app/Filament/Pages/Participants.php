<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Registration;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Participants extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Daftar Peserta';

    protected static ?string $navigationGroup = 'Manajemen Ujian';

    protected static ?string $title = 'Daftar Peserta Ujian';

    protected static string $view = 'filament.pages.participants';

    public $registrations;
    public $examScheduleId;

    public function mount(): void
    {
        $action = request()->get('action');

        if ($action === 'export') {
            $this->exportExcel()->send();
            exit;
        }

        if ($action === 'print') {
            $this->printPdf()->send();
            exit;
        }
    }

    protected function getViewData(): array
    {
        $this->examScheduleId = request()->get('exam_schedule_id');

        $query = Registration::with(['user', 'examSchedule'])
            ->where('status', 'verified')
            ->orderBy('payment_verified_at', 'asc');

        if ($this->examScheduleId) {
            $query->where('exam_schedule_id', $this->examScheduleId);
        }

        $this->registrations = $query->get();

        return [
            'registrations' => $this->registrations,
            'examScheduleId' => $this->examScheduleId,
        ];
    }

    public function exportExcel(): StreamedResponse
    {
        $examScheduleId = request()->get('exam_schedule_id');

        $query = Registration::with(['user', 'examSchedule'])
            ->where('status', 'verified')
            ->orderBy('payment_verified_at', 'asc');

        if ($examScheduleId) {
            $query->where('exam_schedule_id', $examScheduleId);
        }

        $registrations = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="daftar_peserta_ept_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($registrations) {
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
                    $reg->examSchedule->exam_date ? $reg->examSchedule->exam_date->format('d F Y') : '-',
                    $reg->payment_verified_at ? $reg->payment_verified_at->format('d F Y, H:i') : '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printPdf(): \Illuminate\Http\Response
    {
        $examScheduleId = request()->get('exam_schedule_id');

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
}
