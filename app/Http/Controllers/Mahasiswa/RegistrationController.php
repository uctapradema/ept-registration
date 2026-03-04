<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\StoreRegistrationRequest;
use App\Http\Requests\Mahasiswa\CancelRegistrationRequest;
use App\Http\Requests\Mahasiswa\StorePaymentRequest;
use App\Models\ExamSchedule;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService
    ) {}

    public function index()
    {
        $registrations = Registration::with('examSchedule')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('mahasiswa.registrations.index', compact('registrations'));
    }

    public function create($schedule_id)
    {
        $user = auth()->user();
        
        if ($user->hasActiveRegistration()) {
            $existingRegistration = Registration::with('examSchedule')
                ->where('user_id', $user->id)
                ->active()
                ->first();
            
            return redirect()->route('mahasiswa.registrations.show', $existingRegistration)
                ->with('warning', 'Anda sudah memiliki pendaftaran aktif. Selesaikan atau batalkan pendaftaran sebelumnya terlebih dahulu.');
        }

        $schedule = ExamSchedule::findOrFail($schedule_id);
        
        if (!$schedule->isAvailable()) {
            return redirect()->route('mahasiswa.schedules.index')
                ->with('error', 'Jadwal ini tidak tersedia untuk pendaftaran.');
        }

        return view('mahasiswa.registrations.create', compact('schedule'));
    }

    public function store(StoreRegistrationRequest $request)
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

    public function show(Registration $registration)
    {
        $this->authorize($registration);

        $registration->load('examSchedule', 'verifiedBy');
        
        return view('mahasiswa.registrations.show', [
            'registration' => $registration,
        ]);
    }

    public function uploadPayment(Registration $registration)
    {
        $this->authorize('uploadPayment', $registration);
        $this->validatePaymentStatus($registration);

        return view('mahasiswa.registrations.upload-payment', compact('registration'));
    }

    public function storePayment(StorePaymentRequest $request, Registration $registration)
    {
        $this->authorize('uploadPayment', $registration);
        $this->validatePaymentStatus($registration);

        try {
            $file = $request->file('payment_proof');
            $path = $this->storePaymentFile($file, $registration);

            $this->registrationService->uploadPayment($registration, $path, $request->payment_note);

            return $this->buildResponse($request, $registration);

        } catch (\Exception $e) {
            return $this->handleError($request, $e);
        }
    }

    public function cancel(CancelRegistrationRequest $request, Registration $registration)
    {
        $this->authorize('cancel', $registration);

        if (!in_array($registration->status, [RegistrationStatus::PENDING_PAYMENT->value, RegistrationStatus::AWAITING_VERIFICATION->value])) {
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

    public function card(Registration $registration)
    {
        $this->authorize('viewCard', $registration);

        if ($registration->status !== RegistrationStatus::VERIFIED->value) {
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
        if ($registration->status !== RegistrationStatus::PENDING_PAYMENT->value) {
            throw new \RuntimeException('Pembayaran sudah dilakukan atau status tidak valid.');
        }

        if ($registration->isExpired()) {
            throw new \RuntimeException('Batas waktu pembayaran telah habis.');
        }
    }

    private function storePaymentFile($file, Registration $registration): string
    {
        // Sanitize extension - hanya alphanumeric
        $extension = $file->getClientOriginalExtension();
        $safeExtension = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($extension));
        
        // Generate random filename untuk keamanan
        $randomName = bin2hex(random_bytes(16));
        $fileName = $randomName . '.' . $safeExtension;
        
        // Simpan di storage non-public
        return $file->storeAs('payments', $fileName, 'private');
    }

    private function buildResponse(Request $request, Registration $registration)
    {
        $message = 'Bukti pembayaran berhasil diupload. Menunggu verifikasi.';
        $redirect = route('mahasiswa.registrations.show', $registration);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirect,
            ]);
        }

        return redirect($redirect)->with('success', $message);
    }

    private function handleError(Request $request, \Exception $e)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return redirect()->back()->with('error', $e->getMessage());
    }
}
