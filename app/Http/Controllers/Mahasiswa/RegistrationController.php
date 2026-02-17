<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $registrations = Registration::with('examSchedule')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('mahasiswa.registrations.index', compact('registrations'));
    }

    public function create($schedule_id)
    {
        $user = Auth::user();
        
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

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasActiveRegistration()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda sudah memiliki pendaftaran aktif.');
        }

        $validated = $request->validate([
            'schedule_id' => 'required|exists:exam_schedules,id',
            'agreement' => 'required|accepted',
        ]);

        $schedule = ExamSchedule::findOrFail($validated['schedule_id']);

        try {
            $registration = DB::transaction(function () use ($user, $schedule) {
                $lockedSchedule = ExamSchedule::where('id', $schedule->id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedSchedule->availableQuota() <= 0) {
                    throw new \Exception('Kuota untuk jadwal ini sudah penuh.');
                }

                // Determine session from exam schedule
                $session = $schedule->session ?? '01';

                // Generate sequential number for this schedule
                $registrationCount = Registration::where('exam_schedule_id', $schedule->id)->count() + 1;
                $registrationNumber = 'EPT/' . $session . '/' . $schedule->exam_date->format('dmY') . '/' . str_pad($registrationCount, 4, '0', STR_PAD_LEFT);

                $paymentDeadlineHours = $schedule->payment_deadline_hours ?? 24;

                return Registration::create([
                    'user_id' => $user->id,
                    'exam_schedule_id' => $schedule->id,
                    'registration_number' => $registrationNumber,
                    'status' => 'pending_payment',
                    'expires_at' => now()->addHours($paymentDeadlineHours),
                ]);
            });

            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('success', 'Pendaftaran berhasil dibuat. Silakan upload bukti pembayaran dalam waktu 24 jam.');

        } catch (\Exception $e) {
            return redirect()->route('mahasiswa.schedules.index')
                ->with('error', $e->getMessage());
        }
    }

    public function show(Registration $registration)
    {
        $user = Auth::user();
        
        if ($registration->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $registration->load('examSchedule', 'verifiedBy');
        
        $statusLabels = [
            'pending_payment' => 'Menunggu Pembayaran',
            'awaiting_verification' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'expired' => 'Kadaluarsa',
        ];

        $statusColors = [
            'pending_payment' => 'yellow',
            'awaiting_verification' => 'blue',
            'verified' => 'green',
            'rejected' => 'red',
            'cancelled' => 'gray',
            'expired' => 'gray',
        ];

        return view('mahasiswa.registrations.show', compact('registration', 'statusLabels', 'statusColors'));
    }

    public function uploadPayment(Registration $registration)
    {
        $user = Auth::user();
        
        if ($registration->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($registration->status !== 'pending_payment') {
            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('error', 'Pembayaran sudah dilakukan atau status tidak valid.');
        }

        if ($registration->isExpired()) {
            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('error', 'Batas waktu pembayaran telah habis.');
        }

        return view('mahasiswa.registrations.upload-payment', compact('registration'));
    }

    public function storePayment(Request $request, Registration $registration)
    {
        $user = Auth::user();
        
        if ($registration->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($registration->status !== 'pending_payment') {
            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('error', 'Pembayaran sudah dilakukan atau status tidak valid.');
        }

        if ($registration->isExpired()) {
            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('error', 'Batas waktu pembayaran telah habis.');
        }

        $validated = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'payment_note' => 'nullable|string|max:500',
        ], [
            'payment_proof.required' => 'File bukti pembayaran wajib diupload.',
            'payment_proof.mimes' => 'File harus berformat JPG, JPEG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran file maksimal 5MB.',
        ]);

        Log::info('Starting payment upload for registration: ' . $registration->id);
        
        try {
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                
                Log::info('File received: ' . $file->getClientOriginalName() . ' (' . $file->getSize() . ' bytes)');
                
                // Debug: Check file info
                if (!$file->isValid()) {
                    Log::error('File is not valid: ' . $file->getErrorMessage());
                    return redirect()->back()->with('error', 'File upload gagal. Error: ' . $file->getErrorMessage());
                }
                
                $fileName = 'payment_' . str_replace(['/', '\\'], '_', $registration->registration_number) . '_' . time() . '.' . $file->getClientOriginalExtension();
                Log::info('Saving with filename: ' . $fileName);
                
                $path = $file->storeAs('payments', $fileName, 'public');
                
                Log::info('Stored path returned: ' . ($path ?: 'NULL/FALSE'));

                if (!$path) {
                    Log::error('Failed to store file - path is null/false');
                    return redirect()->back()->with('error', 'Gagal menyimpan file ke storage.');
                }
                
                Log::info('File saved successfully, updating registration...');

                $registration->update([
                    'payment_proof' => $path,
                    'payment_uploaded_at' => now(),
                    'status' => 'awaiting_verification',
                    'payment_note' => $validated['payment_note'] ?? null,
                ]);
                
                Log::info('Registration updated successfully');

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi.',
                        'redirect' => route('mahasiswa.registrations.show', $registration),
                    ]);
                }

                return redirect()->route('mahasiswa.registrations.show', $registration)
                    ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi.');
            }

            Log::warning('No file in request');
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupload file. Pastikan file tidak corrupt.',
                ], 422);
            }
            
            return redirect()->back()->with('error', 'Gagal mengupload file. Pastikan file tidak corrupt.');

        } catch (\Exception $e) {
            \Log::error('Payment upload error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Registration $registration)
    {
        $user = Auth::user();
        
        if ($registration->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($registration->status, ['pending_payment', 'awaiting_verification'])) {
            return redirect()->route('mahasiswa.registrations.show', $registration)
                ->with('error', 'Pendaftaran tidak dapat dibatalkan.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'required|string|min:10|max:500',
        ]);

        try {
            DB::transaction(function () use ($registration, $validated) {
                $registration->update([
                    'status' => 'cancelled',
                    'rejection_reason' => $validated['cancel_reason'],
                ]);
            });

            return redirect()->route('mahasiswa.dashboard')
                ->with('success', 'Pendaftaran berhasil dibatalkan.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
