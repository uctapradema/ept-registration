<?php

namespace App\Filament\Pages;

use App\Services\ExportService;
use Filament\Pages\Page;
use Illuminate\Http\Response;
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

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() || auth()->user()?->isFinance();
    }

    public function mount(): void
    {
        $this->examScheduleId = request()->get('exam_schedule_id');
    }

    protected function getViewData(): array
    {
        $this->registrations = app(ExportService::class)
            ->getParticipantsQuery($this->examScheduleId)
            ->get();

        return [
            'registrations' => $this->registrations,
            'examScheduleId' => $this->examScheduleId,
        ];
    }

    public function exportExcel(): StreamedResponse
    {
        $examScheduleId = request()->get('exam_schedule_id');
        $exportService = app(ExportService::class);
        $filename = $exportService->generateFilename($examScheduleId);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        return response()->stream(function () use ($examScheduleId) {
            app(ExportService::class)->generateCsv($examScheduleId);
        }, 200, $headers);
    }

    public function printPdf(): Response
    {
        $examScheduleId = request()->get('exam_schedule_id');

        return response()->make(
            app(ExportService::class)->getPrintView($examScheduleId),
            200,
            ['Content-Type' => 'text/html']
        );
    }
}
