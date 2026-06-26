<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Constants\AppConstants;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\StoreRegistrationRequest;
use App\Http\Requests\Mahasiswa\CancelRegistrationRequest;
use App\Http\Requests\Mahasiswa\StorePaymentRequest;
use App\Models\ExamSchedule;
use App\Models\Registration;
use App\Services\FileStorageService;
use App\Services\ResponseService;
use App\Services\RegistrationService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService,
        private FileStorageService $fileStorageService,
        private ResponseService $responseService
    ) {}

    public function index(): View
    {
        $registrations = Registration::with('examSchedule')
            ->forUser(auth()->id())
            ->latest()
            ->paginate(AppConstants::DEFAULT_PAGE_SIZE);

        return view('mahasiswa.registrations.index', compact('registrations'));
    }

    public function create(int $scheduleId): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasActiveRegistration()) {
            $existingRegistration = Registration::with('examSchedule')
                ->forUser($user->id)
                ->active()
                ->first();

            return redirect()->route('mahasiswa.registrations.show', $existingRegistration)
                ->with('warning', 'Anda sudah memiliki pendaftaran aktif. Selesaikan atau batalkan pendaftaran sebelumnya terlebih dahulu.');
        }

        $schedule = ExamSchedule::findOrFail($scheduleId);

        if (! $schedule->isAvailable()) {
            return redirect()->route('mahasiswa.schedules.index')
                ->with('error', 'Jadwal ini tidak tersedia untuk pendaftaran.');
        }

        return view('mahasiswa.registrations.create', compact('schedule'));
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasActiveRegistration()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda sudah memiliki pendaftaran aktif.');
        }

        $schedule = ExamSchedule::findOrFail($request->schedule_id);

        try {
            $registration = $this->registrationService->createRegistration($user, $schedule);

            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('success', 'Pendaftaran berhasil dibuat. Silakan upload bukti pembayaran dalam waktu 24 jam.');

        } catch (\RuntimeException $e) {
            return redirect()->route('mahasiswa.schedules.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('mahasiswa.schedules.index')
                ->with('error', 'Terjadi kesalahan saat menyimpan pendaftaran.');
        }
    }

    public function show(Registration $registration): View
    {
        $this->authorize($registration);

        $registration->load('examSchedule', 'verifiedBy');

        return view('mahasiswa.registrations.show', [
            'registration' => $registration,
        ]);
    }

    public function uploadPayment(Registration $registration): View
    {
        $this->authorize('uploadPayment', $registration);
        $this->validatePaymentStatus($registration);

        return view('mahasiswa.registrations.upload-payment', compact('registration'));
    }

    public function storePayment(StorePaymentRequest $request, Registration $registration): JsonResponse|RedirectResponse
    {
        $this->authorize('uploadPayment', $registration);
        $this->validatePaymentStatus($registration);

        try {
            $path = $this->fileStorageService->storePaymentProof($request->file('payment_proof'));
            $this->registrationService->uploadPayment($registration, $path, $request->payment_note);

            $message = 'Bukti pembayaran berhasil diupload. Menunggu verifikasi.';
            $redirect = route('mahasiswa.registrations.show', $registration);

            return $this->responseService->success($request, $message, $redirect);

        } catch (\Exception $e) {
            return $this->responseService->error($request, $e->getMessage());
        }
    }

    public function cancel(CancelRegistrationRequest $request, Registration $registration): RedirectResponse
    {
        $this->authorize('cancel', $registration);

        if (! $registration->canBeCancelled()) {
            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('error', 'Pendaftaran tidak dapat dibatalkan.');
        }

        try {
            $this->registrationService->cancelRegistration($registration, $request->cancel_reason);

            return redirect()->route('mahasiswa.dashboard')
                ->with('success', 'Pendaftaran berhasil dibatalkan.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function card(Registration $registration): Response|RedirectResponse
    {
        $this->authorize('viewCard', $registration);

        if ($registration->status !== RegistrationStatus::VERIFIED) {
            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('error', 'Kartu ujian hanya tersedia untuk pendaftaran yang telah terverifikasi.');
        }

        $registration->load('examSchedule', 'user');

        $pdf = app('dompdf.wrapper')->loadView('mahasiswa.registrations.card', [
            'registration' => $registration,
        ]);

        $pdf->setPaper('A5', 'landscape');

        $filename = 'kartu-ujian-' . str_replace('/', '-', $registration->registration_number) . '.pdf';

        return $pdf->stream($filename);
    }

    private function validatePaymentStatus(Registration $registration): void
    {
        if ($registration->status !== RegistrationStatus::PENDING_PAYMENT) {
            throw new \RuntimeException('Pembayaran sudah dilakukan atau status tidak valid.');
        }

        if ($registration->isExpired()) {
            throw new \RuntimeException('Batas waktu pembayaran telah habis.');
        }
    }
}
