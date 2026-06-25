# IMPLEMENTATION PLAN — EPT APPLICATION

**Tanggal:** 25 Juni 2026
**Versi:** 1.0
**Status:** Ready for Implementation
**Tech Stack:** Laravel 12 + Filament 3 + Spatie Permission + MySQL + Tailwind CSS + DomPDF

---

## DAFTAR ISI

1. [Ringkasan Proyek](#1-ringkasan-proyek)
2. [Arsitektur Saat Ini](#2-arsitektur-saat-ini)
3. [Daftar Masalah (Bug & Kode Quality)](#3-daftar-masalah)
4. [Fase 1: Bug Fixes & Konsolidasi Database](#fase-1-bug-fixes--konsolidasi-database)
5. [Fase 2: Refactor Architecture](#fase-2-refactor-architecture)
6. [Fase 3: Security & Authorization](#fase-3-security--authorization)
7. [Fase 4: Fitur Baru — Admin](#fase-4-fitur-baru--admin)
8. [Fase 5: Fitur Baru — Mahasiswa](#fase-5-fitur-baru--mahasiswa)
9. [Fase 6: Performance & Optimization](#fase-6-performance--optimization)
10. [Fase 7: Testing & Deployment](#fase-7-testing--deployment)
11. [Ringkasan File](#ringkasan-file)
12. [Urutan Eksekusi](#urutan-eksekusi)
13. [Checklist Progress](#checklist-progress)

---

## 1. Ringkasan Proyek

Aplikasi EPT (English Proficiency Test) adalah sistem pendaftaran ujian bahasa Inggris untuk Universitas Ngudi Waluyo.

**2 Sisi Aplikasi:**
- **User/Mahasiswa:** `https://ept.uctapradema.id/mahasiswa` — pendaftaran, upload pembayaran, download kartu ujian
- **Admin:** `https://ept.uctapradema.id/admin` — manajemen jadwal, verifikasi pembayaran, input nilai

**Tech Stack:**
| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 12.x |
| Admin Panel | Filament 3.x |
| Auth & RBAC | Laravel Breeze + Spatie Permission |
| Database | MySQL |
| Frontend | Tailwind CSS + Blade + Alpine.js |
| PDF | barryvdh/laravel-dompdf |
| Captcha | mews/captcha |
| Build Tool | Vite |

**Roles:**
| Role | Deskripsi | Permissions |
|------|-----------|-------------|
| `admin` | Administrator penuh | Semua permissions |
| `finance` | Tim keuangan | View & verify/reject registration |
| `mahasiswa` | Mahasiswa/peserta | Daftar ujian, upload bayar, lihat kartu |

**Flow Utama:**
```
Mahasiswa Register → Pilih Jadwal → Bayar (unique code) → Upload Bukti →
Admin/Finance Verifikasi → Download Kartu Ujian → Ujian → Input Nilai → Selesai
```

---

## 2. Arsitektur Saat Ini

### Database Schema
```
users (id, name, email, password, role, nim, phone, major, faculty, deleted_at)
├── registrations (id, user_id, exam_schedule_id, registration_number, status, payment_proof, ...)
│   ├── listening_score, structure_score, reading_score, average_score
│   ├── graded_by, graded_at, ready_for_scoring
│   └── unique_code, payment_note
├── inputnilais (id, registration_id, listening_score, structure_score, reading_score, ...)
│   └── [REDUNDANT — should be dropped]
│
exam_schedules (id, title, exam_date, start_time, end_time, quota, price, ...)
├── unique_code_min, unique_code_max
├── bank_name, bank_account, account_holder
└── registration_deadline, payment_deadline_hours, is_active, created_by
```

**Status Enum (registrations):**
`pending_payment` → `awaiting_verification` → `verified` | `rejected` | `expired` | `cancelled`

### File Structure (Key)
```
app/
├── Console/Commands/
│   ├── CheckExpiredRegistrations.php    # Cron: cek expired registrations
│   └── EnableExamScoring.php            # Cron: enable scoring setelah ujian
├── Enums/
│   └── RegistrationStatus.php           # Enum status pendaftaran
├── Filament/
│   ├── Pages/
│   │   ├── Dashboard.php                # Admin dashboard
│   │   └── Participants.php             # Daftar peserta (export/print)
│   ├── Resources/
│   │   ├── ExamScheduleResource.php     # CRUD jadwal ujian
│   │   ├── InputnilaiResource.php       # Input nilai (model: Registration)
│   │   ├── RegistrationResource.php     # Kelola pendaftaran + verify/reject
│   │   └── UserResource.php             # CRUD pengguna
│   └── Widgets/
│       ├── ExamScheduleQuotaWidget.php  # Tabel kuota jadwal
│       ├── RegistrationChartWidget.php  # Grafik pendaftaran/bulan
│       └── StatsOverviewWidget.php      # Doughnut chart status
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                        # Breeze auth controllers
│   │   ├── Mahasiswa/
│   │   │   ├── DashboardController.php
│   │   │   ├── RegistrationController.php
│   │   │   └── ScheduleController.php
│   │   └── Admin/
│   │       └── ParticipantController.php
│   ├── Middleware/
│   │   └── EnsureMahasiswa.php          # Middleware role check
│   └── Requests/Mahasiswa/
│       ├── CancelRegistrationRequest.php
│       ├── StorePaymentRequest.php
│       └── StoreRegistrationRequest.php
├── Models/
│   ├── ExamSchedule.php
│   ├── Inputnilai.php                   # [REDUNDANT]
│   ├── Registration.php
│   └── User.php
├── Notifications/
│   ├── PaymentRejectedNotification.php
│   ├── PaymentVerifiedNotification.php
│   └── RegistrationSuccessNotification.php
├── Policies/
│   ├── ExamSchedulePolicy.php
│   ├── RegistrationPolicy.php
│   └── UserPolicy.php
├── Services/
│   └── RegistrationService.php
└── View/Components/
    ├── AppLayout.php
    └── GuestLayout.php
```

---

## 3. Daftar Masalah

### Bug Kritis

| # | Masalah | Lokasi | Severity |
|---|---------|--------|----------|
| 1 | Scope `Active()` salah kapitalisasi — akan throw error | `User.php:83` | CRITICAL |
| 2 | Dual storage nilai — `registrations` + `inputnilais` table | `InputnilaiResource.php` | HIGH |
| 3 | Registration number race condition — `count()+1` tanpa lock | `Registration.php:136` | HIGH |
| 4 | Payment proof disk mismatch — simpan `private`, tampilkan `public` | `RegistrationController.php:184` | HIGH |
| 5 | `Participants.php` pakai `exit` — tidak valid di Laravel | `Participants.php:34` | HIGH |
| 6 | `CreateRegistration` generate nomor format beda | `CreateRegistration.php:20` | MEDIUM |

### Code Quality

| # | Masalah | Lokasi |
|---|---------|--------|
| 7 | Business logic di Filament inline action, bukan service layer | `RegistrationResource.php:152-195` |
| 8 | Tidak ada Event/Listener untuk state machine | Seluruh kodebase |
| 9 | Naming konsistensi — `Inputnilai` (Indonesia) vs English | Model naming |
| 10 | `InputnilaiResource` pakai model `Registration` bukan `Inputnilai` | `InputnilaiResource.php` |
| 11 | Missing return type di beberapa method controller | Controllers |
| 12 | Registration `completed` status ada di DB enum tapi tidak dihandle | `Registration.php` |

### Security

| # | Masalah | Lokasi |
|---|---------|--------|
| 13 | Export CSV tidak ada authorization check | `Participants.php` |
| 14 | Payment proof disimpan di `private` tapi ditampilkan via `public` | Controller vs Filament |
| 15 | CAPTCHA hanya di login & register, tidak di action sensitif lain | Routes |
| 16 | Tidak ada audit trail untuk action sensitif | Seluruh kodebase |

### Performance

| # | Masalah | Lokasi |
|---|---------|--------|
| 17 | N+1 queries di Filament resource tables | RegistrationResource, InputnilaiResource |
| 18 | Dashboard statistics tidak di-cache | Widgets |
| 19 | `EnableExamScoring` jalan setiap menit (terlalu sering) | `console.php` |
| 20 | Missing database indexes di beberapa query pattern | Migrations |

---

## FASE 1: Bug Fixes & Konsolidasi Database

**Estimasi:** 8-10 jam
**Dependensi:** Tidak ada (bisa mulai duluan)

---

### 1.1 Fix Scope `Active()` Salah Kapitalisasi

**File:** `app/Models/User.php`
**Baris:** 83
**Masalah:** PHP scope names case-sensitive. `Active()` tidak akan dikenali sebagai scope Eloquent.

```php
// SEBELUM (SALAH):
public function hasActiveRegistration(): bool
{
    return $this->registrations()
        ->Active()  // ← case salah
        ->exists();
}

// SESUDAH (BENAR):
public function hasActiveRegistration(): bool
{
    return $this->registrations()
        ->active()  // ← lowercase
        ->exists();
}
```

**Estimasi:** 5 menit

---

### 1.2 Hapus Model `Inputnilai` — Konsolidasi ke `registrations`

**Masalah:** Tabel `inputnilais` menyimpan data yang SAMA dengan kolom di `registrations` (`listening_score`, `structure_score`, `reading_score`, `average_score`). `InputnilaiResource` menggunakan model `Registration`, bukan `Inputnilai`. Ini source of truth conflict.

**Action Items:**

1. **Buat migration baru** `database/migrations/xxxx_drop_inputnilais_table.php`:
   ```php
   <?php
   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration
   {
       public function up(): void
       {
           Schema::dropIfExists('inputnilais');
       }

       public function down(): void
       {
           // Tidak bisa di-rollback dengan aman
       }
   };
   ```

2. **Hapus file:**
   - `app/Models/Inputnilai.php`
   - `database/migrations/2026_02_27_020312_create_inputnilais_table.php`

3. **Update `InputnilaiResource.php`** — pastikan tidak ada reference ke model `Inputnilai` (sudah benar, pakai `Registration::class`)

4. **Update Seeder jika ada** — hapus referensi ke `Inputnilai`

**Estimasi:** 1 jam

---

### 1.3 Fix Registration Number Race Condition

**File:** `app/Models/Registration.php`
**Baris:** 136-143
**Masalah:** `count() + 1` tanpa row lock bisa menghasilkan nomor duplikat jika ada concurrent registration.

```php
// SEBELUM (RACE CONDITION):
public static function generateRegistrationNumber(ExamSchedule $schedule): string
{
    $session = $schedule->session ?? '01';
    $count = self::where('exam_schedule_id', $schedule->id)->count() + 1;
    return 'EPT/' . $session . '/' . $schedule->exam_date->format('dmY') . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

// SESUDAH (SAFE):
public static function generateRegistrationNumber(ExamSchedule $schedule): string
{
    $session = $schedule->session ?? '01';

    $count = DB::table('registrations')
        ->where('exam_schedule_id', $schedule->id)
        ->lockForUpdate()
        ->count() + 1;

    return 'EPT/' . $session . '/' . $schedule->exam_date->format('dmY') . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
}
```

**Note:** Method ini harus dipanggil DALAM transaction yang sudah lock row (sudah benar di `RegistrationService::createRegistration()` yang pakai `DB::transaction` + `lockForUpdate`).

**Estimasi:** 30 menit

---

### 1.4 Fix Payment Proof Disk Mismatch

**Masalah:**
- `RegistrationController::storePaymentFile()` simpan ke `private` disk
- `RegistrationResource` tampilkan via `disk('public')`

**Solution (Opsi A — Recommended):** Simpan langsung ke `public` disk.

**File diubah:** `app/Http/Controllers/Mahasiswa/RegistrationController.php`

```php
// SEBELUM:
private function storePaymentFile($file, $registration): string
{
    $extension = $file->getClientOriginalExtension();
    $safeExtension = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($extension));
    $randomName = bin2hex(random_bytes(16));
    $fileName = $randomName . '.' . $safeExtension;
    return $file->storeAs('payments', $fileName, 'private');
}

// SESUDAH:
private function storePaymentFile($file, $registration): string
{
    $extension = $file->getClientOriginalExtension();
    $safeExtension = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($extension));
    $randomName = bin2hex(random_bytes(16));
    $fileName = $randomName . '.' . $safeExtension;
    return $file->storeAs('payments', $fileName, 'public');
}
```

**Pastikan:**
- `config/filesystems.php` punya disk `public` yang benar
- `php artisan storage:link` sudah dijalankan di production
- `.env` punya `FILESYSTEM_DISK=local`

**Estimasi:** 1 jam

---

### 1.5 Fix `Participants.php` — Hapus `exit`

**File:** `app/Filament/Pages/Participants.php`
**Masalah:** `exit` di dalam Filament Page method tidak valid — menghentikan seluruh aplikasi.

**Solution:** Refactor export & print ke dedicated methods yang return response, bukan dipanggil dari `mount()`.

```php
// SEBELUM (SALAH):
public function mount(): void
{
    $action = request()->get('action');
    if ($action === 'export') {
        $this->exportExcel()->send();
        exit;  // ← BAD
    }
    if ($action === 'print') {
        $this->printPdf()->send();
        exit;  // ← BAD
    }
}

// SESUDAH: Hapus action handling dari mount()
// Export dan print harus diakses via route terpisah atau Filament Action buttons
// yang return response langsung.
```

**Rekomendasi:**
1. Buat route terpisah untuk export:
   ```php
   // routes/web.php atau routes/admin.php
   Route::get('/admin/participants/export', [ParticipantExportController::class, 'export'])
       ->middleware(['auth', 'admin']);
   Route::get('/admin/participants/print', [ParticipantExportController::class, 'print'])
       ->middleware(['auth', 'admin']);
   ```
2. Atau gunakan Filament Action buttons yang return `response()->stream()`

**Estimasi:** 2 jam

---

### 1.6 Fix `ExamSchedule` Time Casting

**File:** `app/Models/ExamSchedule.php`
**Baris:** 36-37
**Masalah:** Kolom DB type `time` di-cast sebagai `datetime` — Carbon akan menambahkan tanggal Today secara otomatis, tidak konsisten.

```php
// SEBELUM:
protected function casts(): array
{
    return [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        // ...
    ];
}

// SESUDAH (Option 1 - cast sebagai string):
protected function casts(): array
{
    return [
        'start_time' => 'string',
        'end_time' => 'string',
        // ...
    ];
}

// SESUDAH (Option 2 - gunakan accessor):
public function getStartTimeFormattedAttribute(): string
{
    return $this->attributes['start_time'];
}
```

**Estimasi:** 1 jam

---

### 1.7 Fix `CreateRegistration` Inconsistent Registration Number

**File:** `app/Filament/Resources/RegistrationResource/Pages/CreateRegistration.php`
**Masalah:** Generate format `EPT-YYYYMMDD-UNIQUEID` berbeda dari `Registration::generateRegistrationNumber()` yang `EPT/session/ddmmyyyy/0001`.

```php
// SEBELUM:
$data['registration_number'] = 'EPT-' . now()->format('Ymd') . '-' . strtoupper(uniqid());

// SESUDAH:
$schedule = \App\Models\ExamSchedule::find($data['exam_schedule_id']);
$data['registration_number'] = Registration::generateRegistrationNumber($schedule);
$data['unique_code'] = Registration::generateUniqueCode($schedule);
```

**Estimasi:** 30 menit

---

### 1.8 Tambah Database Indexes

**File:** `database/migrations/xxxx_add_performance_indexes.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->index(['is_active', 'registration_deadline', 'exam_date'], 'idx_schedules_active_deadline');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'idx_registrations_user_status');
        });
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_active_deadline');
        });
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('idx_registrations_user_status');
        });
    }
};
```

**Estimasi:** 30 menit

---

### 1.9 Pastikan Kolom `unique_code` dan Bank Info Ada

**File yang sudah ada:**
- `database/migrations/2026_02_17_175701_add_unique_code_fields.php` — ✅ sudah ada

**Pastikan kolom berikut ada di `exam_schedules`:**
- `unique_code_min`, `unique_code_max`
- `bank_name`, `bank_account`, `account_holder`
- `payment_deadline_hours`

Jika ada yang belum ada, buat migration tambahan.

**Estimasi:** 30 menit

---

## FASE 2: Refactor Architecture

**Estimasi:** 10-12 jam
**Dependensi:** Fase 1 selesai

---

### 2.1 Pindahkan Business Logic ke Service Layer

**Masalah:** Verify & reject logic ada di Filament inline action (`RegistrationResource.php:152-195`), bukan di service.

**File baru:** `app/Services/RegistrationVerificationService.php`

```php
<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\PaymentVerifiedNotification;
use App\Notifications\PaymentRejectedNotification;
use App\Events\RegistrationStatusChanged;
use Illuminate\Support\Facades\DB;

class RegistrationVerificationService
{
    public function verify(Registration $registration, User $verifier): void
    {
        DB::transaction(function () use ($registration, $verifier) {
            $oldStatus = $registration->status;

            $registration->update([
                'status' => RegistrationStatus::VERIFIED->value,
                'payment_verified_at' => now(),
                'verified_by' => $verifier->id,
            ]);

            event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::VERIFIED->value));

            $registration->load(['user', 'examSchedule']);
            $registration->user->notify(new PaymentVerifiedNotification($registration));
        });
    }

    public function reject(Registration $registration, string $reason, User $rejector): void
    {
        DB::transaction(function () use ($registration, $reason, $rejector) {
            $oldStatus = $registration->status;

            $registration->update([
                'status' => RegistrationStatus::REJECTED->value,
                'rejection_reason' => $reason,
                'payment_verified_at' => null,
                'verified_by' => null,
            ]);

            event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::REJECTED->value));

            $registration->load(['user', 'examSchedule']);
            $registration->user->notify(new PaymentRejectedNotification($registration));
        });
    }
}
```

**Update `RegistrationResource.php`** — gunakan service:

```php
// SEBELUM (inline action):
->action(function (Registration $record): void {
    $record->update([...]);
    $record->user->notify(new PaymentVerifiedNotification($record));
})

// SESUDAH (panggil service):
->action(function (Registration $record): void {
    app(RegistrationVerificationService::class)->verify($record, auth()->user());
})
```

**Estimasi:** 3 jam

---

### 2.2 Buat Event/Listener untuk State Machine

**File baru:** `app/Events/RegistrationStatusChanged.php`

```php
<?php

namespace App\Events;

use App\Models\Registration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistrationStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public string $oldStatus,
        public string $newStatus,
    ) {}
}
```

**File baru:** `app/Listeners/SendRegistrationNotification.php`

```php
<?php

namespace App\Listeners;

use App\Events\RegistrationStatusChanged;
use App\Notifications\PaymentVerifiedNotification;
use App\Notifications\PaymentRejectedNotification;
use App\Notifications\RegistrationExpiredNotification;

class SendRegistrationNotification
{
    public function handle(RegistrationStatusChanged $event): void
    {
        $registration = $event->registration;
        $registration->load(['user', 'examSchedule']);

        match ($event->newStatus) {
            'verified' => $registration->user->notify(new PaymentVerifiedNotification($registration)),
            'rejected' => $registration->user->notify(new PaymentRejectedNotification($registration)),
            'expired' => $registration->user->notify(new RegistrationExpiredNotification($registration)),
            default => null,
        };
    }
}
```

**File baru:** `app/Listeners/LogRegistrationActivity.php`

```php
<?php

namespace App\Listeners;

use App\Events\RegistrationStatusChanged;
use Spatie\Activitylog\Facades\Activity;

class LogRegistrationActivity
{
    public function handle(RegistrationStatusChanged $event): void
    {
        Activity::causedBy(auth()->user())
            ->performedOn($event->registration)
            ->withProperties([
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ])
            ->event('registration_status_changed')
            ->log("Registration status changed from {$event->oldStatus} to {$event->newStatus}");
    }
}
```

**Register di `app/Providers/AppServiceProvider.php`:**

```php
use App\Events\RegistrationStatusChanged;
use App\Listeners\SendRegistrationNotification;
use App\Listeners\LogRegistrationActivity;

public function boot(): void
{
    // ... existing policy registrations ...

    Event::listen(RegistrationStatusChanged::class, SendRegistrationNotification::class);
    Event::listen(RegistrationStatusChanged::class, LogRegistrationActivity::class);
}
```

**Estimasi:** 4 jam

---

### 2.3 Rename `InputnilaiResource` → `ScoringResource`

**File:** `app/Filament/Resources/InputnilaiResource.php`

**Action:**
1. Rename file ke `ScoringResource.php`
2. Update class name: `InputnilaiResource` → `ScoringResource`
3. Update all references:
   - `InputnilaiResource\Pages\ListInputnilais` → `ScoringResource\Pages\ListScorings`
   - Rename directory: `InputnilaiResource/` → `ScoringResource/`
   - Rename page file: `ListInputnilais.php` → `ListScorings.php`
4. Update `AdminPanelProvider.php` — resource discovery akan otomatis find

**Estimasi:** 2 jam

---

### 2.4 Pisahkan RegistrationResource Actions ke Komponen

**File baru:** `app/Filament/Actions/VerifyPaymentAction.php`

```php
<?php

namespace App\Filament\Actions;

use App\Models\Registration;
use App\Services\RegistrationVerificationService;
use Filament\Actions\Action;
use Filament\Forms;

class VerifyPaymentAction
{
    public static function make(): Action
    {
        return Action::make('verify')
            ->label('Verifikasi')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Verifikasi Pembayaran')
            ->modalDescription('Apakah Anda yakin ingin memverifikasi pembayaran ini?')
            ->modalSubmitActionLabel('Ya, Verifikasi')
            ->visible(fn (Registration $record): bool =>
                in_array($record->status, ['awaiting_verification', 'pending_payment']) &&
                (auth()->user()?->isAdmin() || auth()->user()?->isFinance())
            )
            ->action(function (Registration $record): void {
                app(RegistrationVerificationService::class)->verify($record, auth()->user());
            });
    }
}
```

**File baru:** `app/Filament/Actions/RejectPaymentAction.php`

```php
<?php

namespace App\Filament\Actions;

use App\Models\Registration;
use App\Services\RegistrationVerificationService;
use Filament\Actions\Action;
use Filament\Forms;

class RejectPaymentAction
{
    public static function make(): Action
    {
        return Action::make('reject')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->form([
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->rows(3)
                    ->maxLength(65535),
            ])
            ->modalHeading('Tolak Pembayaran')
            ->modalDescription('Berikan alasan penolakan pembayaran ini.')
            ->modalSubmitActionLabel('Ya, Tolak')
            ->visible(fn (Registration $record): bool =>
                in_array($record->status, ['awaiting_verification', 'pending_payment', 'verified']) &&
                (auth()->user()?->isAdmin() || auth()->user()?->isFinance())
            )
            ->action(function (Registration $record, array $data): void {
                app(RegistrationVerificationService::class)->reject(
                    $record,
                    $data['rejection_reason'],
                    auth()->user()
                );
            });
    }
}
```

**Update `RegistrationResource.php`:**

```php
use App\Filament\Actions\VerifyPaymentAction;
use App\Filament\Actions\RejectPaymentAction;

// Di method table():
->actions([
    VerifyPaymentAction::make(),
    RejectPaymentAction::make(),
    Tables\Actions\ViewAction::make(),
    Tables\Actions\EditAction::make()
        ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
])
```

**Estimasi:** 3 jam

---

### 2.5 Konsolidasi Naming Convention

**Action Items:**

1. **Hapus `Inputnilai` model** (sudah di Fase 1.2)
2. **Rename `InputnilaiResource`** → `ScoringResource` (sudah di Fase 2.3)
3. **Update `Registration` model** — handle `completed` status:
   ```php
   public function getStatusLabelAttribute(): string
   {
       return match($this->status) {
           'pending_payment' => 'Menunggu Pembayaran',
           'awaiting_verification' => 'Menunggu Verifikasi',
           'verified' => 'Terverifikasi',
           'rejected' => 'Ditolak',
           'cancelled' => 'Dibatalkan',
           'expired' => 'Kadaluarsa',
           'completed' => 'Selesai',
           default => $this->status,
       };
   }
   ```
4. **Pastikan semua naming konsisten:** model English, method camelCase, file PascalCase

**Estimasi:** 1 jam

---

## FASE 3: Security & Authorization

**Estimasi:** 6-8 jam
**Dependensi:** Fase 1 selesai

---

### 3.1 Authorization di Export Action

**File:** `app/Filament/Pages/Participants.php`

```php
// Tambah authorization check di method exportExcel():
public function exportExcel(): StreamedResponse
{
    // Authorization check
    if (!auth()->user()?->can('viewAny', Registration::class)) {
        abort(403, 'Unauthorized');
    }

    // ... existing export logic ...
}
```

**Estimasi:** 30 menit

---

### 3.2 Tambah Rate Limiting

**File:** `routes/web.php`

```php
use Illuminate\Support\Facades\RateLimiter;

// Rate limit untuk registration creation
Route::middleware(['auth', 'mahasiswa', 'throttle:3,1'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::post('/registrations', [RegistrationController::class, 'store'])
        ->name('registrations.store');
});

// Rate limit untuk payment upload
Route::middleware(['auth', 'mahasiswa', 'throttle:5,1'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::post('/registrations/{registration}/payment', [RegistrationController::class, 'storePayment'])
        ->name('registrations.payment.store');
});
```

**Estimasi:** 1 jam

---

### 3.3 Validasi File Upload Lebih Ketat

**File:** `app/Http/Requests/Mahasiswa/StorePaymentRequest.php`

```php
use Illuminate\Validation\Rules\File;

public function rules(): array
{
    return [
        'payment_proof' => [
            'required',
            'file',
            'mimes:jpg,jpeg,png,pdf',
            'max:2048',  // 2MB (lebih ketat dari 5MB)
            File::image()
                ->types(['jpeg', 'png', 'jpg'])
                ->minResolution(100000)  // Minimum 100x100 pixels
                ->and('application/pdf'),
        ],
        'payment_note' => 'nullable|string|max:500',
    ];
}
```

**Estimasi:** 1 jam

---

### 3.4 Tambah Permission-Based Checks di Filament

**File:** Semua Filament Resources

**Action:** Pastikan `canViewAny()`, `canCreate()`, `canEdit()`, `canDelete()` menggunakan Spatie permissions:

```php
// Contoh di RegistrationResource:
public static function canViewAny(): bool
{
    return auth()->user()?->can('registration:view_all', Registration::class) ?? false;
}

public static function canCreate(): bool
{
    return auth()->user()?->can('registration:create', Registration::class) ?? false;
}

// Update RoleSeeder untuk tambah permissions yang sesuai
```

**Estimasi:** 2 jam

---

### 3.5 Audit Trail / Activity Log

**Install Package:**
```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

**File baru:** `app/Filament/Resources/ActivityLogResource.php`

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Log Aktivitas';
    protected static ?string $navigationGroup = 'Pengaturan';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')->label('Oleh')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Aksi')->searchable(),
                Tables\Columns\TextColumn::make('subject_type')->label('Tipe')->searchable(),
                Tables\Columns\TextColumn::make('event')->label('Event')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d F Y, H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs::route('/'),
        ];
    }
}
```

**Estimasi:** 3 jam

---

## FASE 4: Fitur Baru — Admin

**Estimasi:** 12-15 jam
**Dependensi:** Fase 2 selesai

---

### 4.1 Bulk Actions untuk Verifikasi

**File:** `app/Filament/Resources/RegistrationResource.php`

```php
// Tambah di method table():
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\DeleteBulkAction::make()
            ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),

        Tables\Actions\BulkAction::make('verify_bulk')
            ->label('Verifikasi Terpilih')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Verifikasi Massal')
            ->modalDescription('Verifikasi semua pendaftaran terpilih?')
            ->modalSubmitActionLabel('Ya, Verifikasi')
            ->deselectRecordsAfterCompletion()
            ->action(function ($records): void {
                $service = app(RegistrationVerificationService::class);
                foreach ($records as $record) {
                    if (in_array($record->status, ['awaiting_verification', 'pending_payment'])) {
                        $service->verify($record, auth()->user());
                    }
                }
            })
            ->visible(fn (): bool => auth()->user()?->isAdmin() || auth()->user()?->isFinance()),

        Tables\Actions\BulkAction::make('export_selected')
            ->label('Export Terpilih')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function ($records) {
                // Export selected records
            }),
    ]),
]),
```

**Estimasi:** 3 jam

---

### 4.2 Master Data Tables

#### Migration 1: `create_banks_table`
```php
Schema::create('banks', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->string('logo')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Migration 2: `create_faculties_table`
```php
Schema::create('faculties', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Migration 3: `create_majors_table`
```php
Schema::create('majors', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Migration 4: `create_exam_sessions_table`
```php
Schema::create('exam_sessions', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->time('start_time');
    $table->time('end_time');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Models
- `app/Models/Bank.php`
- `app/Models/Faculty.php`
- `app/Models/Major.php`
- `app/Models/ExamSession.php`

#### Update Existing Models
- `ExamSchedule` → tambah relasi ke `ExamSession`, bank info dari `banks` table
- `User` → tambah relasi ke `Faculty` dan `Major`

#### Filament Resources
- `app/Filament/Resources/BankResource.php`
- `app/Filament/Resources/FacultyResource.php`
- `app/Filament/Resources/MajorResource.php`

#### Seeders
- `database/seeders/BankSeeder.php`
- `database/seeders/FacultySeeder.php`
- `database/seeders/MajorSeeder.php`
- `database/seeders/ExamSessionSeeder.php`

#### Update Register Form
- `resources/views/auth/register.blade.php` — gunakan dynamic dropdown dari master data

**Estimasi:** 6 jam

---

### 4.3 Duplikat Jadwal

**File:** `app/Filament/Resources/ExamScheduleResource.php`

```php
use Filament\Actions\Action;

// Tambah di method table() actions():
Tables\Actions\Action::make('duplicate')
    ->label('Duplikat')
    ->icon('heroicon-o-document-duplicate')
    ->color('gray')
    ->requiresConfirmation()
    ->modalHeading('Duplikat Jadwal')
    ->modalDescription('Buat salinan jadwal ini?')
    ->modalSubmitActionLabel('Ya, Duplikat')
    ->action(function (ExamSchedule $record): void {
        $newSchedule = $record->replicate([
            'title' => $record->title . ' (Copy)',
            'is_active' => false,
        ]);
        $newSchedule->save();
    })
    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
```

**Estimasi:** 2 jam

---

### 4.4 Export Excel dengan Styling

**Install:**
```bash
composer require maatwebsite/excel
```

**File baru:** `app/Exports/ParticipantsExport.php`

```php
<?php

namespace App\Exports;

use App\Models\Registration;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?int $examScheduleId = null
    ) {}

    public function query()
    {
        $query = Registration::with(['user', 'examSchedule'])
            ->where('status', 'verified')
            ->orderBy('payment_verified_at', 'asc');

        if ($this->examScheduleId) {
            $query->where('exam_schedule_id', $this->examScheduleId);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['No', 'No. Pendaftaran', 'Nama', 'NIM', 'Prodi', 'Fakultas', 'Jadwal', 'Tanggal Ujian', 'Tgl Verifikasi'];
    }

    public function map($registration): array
    {
        return [
            $this->rowIndex++,
            $registration->registration_number,
            $registration->user->name ?? '-',
            $registration->user->nim ?? '-',
            $registration->user->major ?? '-',
            $registration->user->faculty ?? '-',
            $registration->examSchedule->title ?? '-',
            $registration->examSchedule->exam_date?->format('d F Y') ?? '-',
            $registration->payment_verified_at?->format('d F Y, H:i') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
```

**Update `Participants.php`:**

```php
use App\Exports\ParticipantsExport;
use Maatwebsite\Excel\Facades\Excel;

public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
{
    $examScheduleId = request()->get('exam_schedule_id');
    $filename = 'daftar_peserta_ept_' . date('Y-m-d') . '.xlsx';

    return Excel::download(new ParticipantsExport($examScheduleId), $filename);
}
```

**Estimasi:** 3 jam

---

### 4.5 Broadcast Pengumuman

#### Migration: `create_announcements_table`
```php
Schema::create('announcements', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->enum('target_role', ['all', 'mahasiswa', 'admin', 'finance'])->default('all');
    $table->foreignId('target_schedule_id')->nullable()->constrained('exam_schedules')->nullOnDelete();
    $table->boolean('is_active')->default(true);
    $table->timestamp('published_at')->nullable();
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    $table->timestamps();
});
```

#### Model: `app/Models/Announcement.php`
#### Filament Resource: `app/Filament/Resources/AnnouncementResource.php`
#### Notification: `app/Notifications/AnnouncementNotification.php`

**Update Dashboard Mahasiswa:**
```blade
{{-- resources/views/mahasiswa/dashboard.blade.php --}}
{{-- Tambah section announcements --}}
@foreach($announcements as $announcement)
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
        <h3 class="font-bold">{{ $announcement->title }}</h3>
        <p>{{ $announcement->content }}</p>
    </div>
@endforeach
```

**Estimasi:** 4 jam

---

## FASE 5: Fitur Baru — Mahasiswa

**Estimasi:** 8-10 jam
**Dependensi:** Fase 2 selesai

---

### 5.1 Notifikasi Email (Perluas yang Sudah Ada)

**File baru:** `app/Notifications/RegistrationExpiredNotification.php`

```php
<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RegistrationExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Registration $registration) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $schedule = $this->registration->examSchedule;

        return (new MailMessage)
            ->subject('Pendaftaran Kadaluarsa - EPT')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Pendaftaran Anda telah **kadaluarsa** karena batas waktu pembayaran telah habis.')
            ->line('**Nomor Pendaftaran:** ' . $this->registration->registration_number)
            ->line('**Jadwal:** ' . ($schedule->title ?? '-'))
            ->line('')
            ->line('Silakan daftar ulang jika masih ingin mengikuti ujian.')
            ->action('Daftar Ulang', route('mahasiswa.schedules.index'));
    }
}
```

**File baru:** `app/Notifications/PaymentReminderNotification.php`

```php
<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Registration $registration,
        public string $reminderType // '12h' or '2h'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $timeLeft = $this->registration->expires_at->diffForHumans();
        $urgency = $this->reminderType === '2h' ? 'SEGERA' : '';

        return (new MailMessage)
            ->subject("Reminder Pembayaran {$urgency} - EPT")
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line("Ini adalah pengingat bahwa batas waktu pembayaran Anda tinggal **{$timeLeft}**.")
            ->line('**Nomor Pendaftaran:** ' . $this->registration->registration_number)
            ->line('**Total:** Rp ' . number_format($this->registration->total_payment, 0, ',', '.'))
            ->line('')
            ->line('Silakan upload bukti pembayaran sebelum batas waktu habis.')
            ->action('Upload Pembayaran', route('mahasiswa.registrations.payment', $this->registration));
    }
}
```

**Estimasi:** 2 jam

---

### 5.2 Payment Reminder Command

**File baru:** `app/Console/Commands/SendPaymentReminders.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'reminders:send-payment';
    protected $description = 'Send payment reminders before deadline';

    public function handle(): int
    {
        $now = now();

        // Reminder 12 jam sebelum expired
        $registrations12h = Registration::whereIn('status', ['pending_payment', 'awaiting_verification'])
            ->whereBetween('expires_at', [$now->copy()->addHours(11), $now->copy()->addHours(13)])
            ->whereNull('reminder_12h_sent_at')
            ->with(['user', 'examSchedule'])
            ->get();

        foreach ($registrations12h as $registration) {
            $registration->user->notify(new PaymentReminderNotification($registration, '12h'));
            $registration->update(['reminder_12h_sent_at' => $now]);
        }

        // Reminder 2 jam sebelum expired
        $registrations2h = Registration::whereIn('status', ['pending_payment', 'awaiting_verification'])
            ->whereBetween('expires_at', [$now->copy()->addHours(1), $now->copy()->addHours(3)])
            ->whereNull('reminder_2h_sent_at')
            ->with(['user', 'examSchedule'])
            ->get();

        foreach ($registrations2h as $registration) {
            $registration->user->notify(new PaymentReminderNotification($registration, '2h'));
            $registration->update(['reminder_2h_sent_at' => $now]);
        }

        $this->info("Sent " . $registrations12h->count() . " 12h reminders and " . $registrations2h->count() . " 2h reminders.");

        return self::SUCCESS;
    }
}
```

#### Migration: `add_reminder_fields_to_registrations`
```php
Schema::table('registrations', function (Blueprint $table) {
    $table->timestamp('reminder_12h_sent_at')->nullable()->after('ready_for_scoring');
    $table->timestamp('reminder_2h_sent_at')->nullable()->after('reminder_12h_sent_at');
});
```

#### Register di `routes/console.php`:
```php
Schedule::command(SendPaymentReminders::class)->hourly();
```

**Estimasi:** 2 jam

---

### 5.3 Search & Filter Riwayat Pendaftaran

**File:** `resources/views/mahasiswa/registrations/index.blade.php`

Tambahkan search bar dan filter:
```blade
<form method="GET" class="mb-4">
    <div class="flex gap-4">
        <input type="text" name="search" placeholder="Cari nomor pendaftaran..."
               value="{{ request('search') }}"
               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <select name="status" class="rounded-md border-gray-300">
            <option value="">Semua Status</option>
            <option value="pending_payment" {{ request('status') === 'pending_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
            <option value="awaiting_verification" {{ request('status') === 'awaiting_verification' ? 'selected' : '' }}>Menunggu Verifikasi</option>
            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
        </select>
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md">Filter</button>
    </div>
</form>
```

**Update Controller:**
```php
public function index()
{
    $query = Registration::with('examSchedule')
        ->where('user_id', auth()->id());

    if ($search = request('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('registration_number', 'like', "%{$search}%")
              ->orWhereHas('examSchedule', function ($q2) use ($search) {
                  $q2->where('title', 'like', "%{$search}%");
              });
        });
    }

    if ($status = request('status')) {
        $query->where('status', $status);
    }

    $registrations = $query->latest()->paginate(10)->withQueryString();

    return view('mahasiswa.registrations.index', compact('registrations'));
}
```

**Estimasi:** 2 jam

---

### 5.4 Real-time Countdown Timer

**File:** `resources/views/mahasiswa/registrations/show.blade.php`

Tambahkan JavaScript countdown:
```blade
@push('scripts')
<script>
    function startCountdown(expiresAt) {
        const countdownEl = document.getElementById('countdown');
        const interval = setInterval(() => {
            const now = new Date().getTime();
            const distance = new Date(expiresAt).getTime() - now;

            if (distance < 0) {
                clearInterval(interval);
                countdownEl.innerHTML = '<span class="text-red-600 font-bold">KADALUARSA</span>';
                setTimeout(() => location.reload(), 2000);
                return;
            }

            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownEl.innerHTML = `${hours}j ${minutes}m ${seconds}s`;
        }, 1000);
    }

    startCountdown('{{ $registration->expires_at->toIso8601String() }}');
</script>
@endpush

{{-- Di dalam template: --}}
<div id="countdown" class="text-2xl font-bold"></div>
```

**Estimasi:** 1 jam

---

### 5.5 Upload Foto Profil

#### Migration: `add_photo_to_users_table`
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('photo')->nullable()->after('faculty');
});
```

#### Update `ProfileController::update()`
```php
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();
    $data = $request->validated();

    if ($request->hasFile('photo')) {
        // Hapus foto lama
        if ($user->photo) {
            Storage::disk('public')->delete('avatars/' . $user->photo);
        }
        $data['photo'] = $request->file('photo')->store('avatars', 'public');
    }

    $user->fill($data);

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    $user->save();

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}
```

#### Update `ProfileUpdateRequest`
```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        'photo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png'],
    ];
}
```

#### Update View: `resources/views/profile/edit.blade.php`
```blade
<div class="col-span-6 sm:col-span-4">
    <x-input-label for="photo" value="Foto Profil" />
    @if($user->photo)
        <img src="{{ asset('storage/' . $user->photo) }}" class="mt-2 w-20 h-20 rounded-full object-cover">
    @endif
    <x-text-input id="photo" name="photo" type="file" class="mt-1 block w-full" accept="image/*" />
    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
</div>
```

**Estimasi:** 2 jam

---

### 5.6 Reset Password

**Status:** Sudah mayoritas ready.

**Action Items:**
1. Pastikan route `forgot-password` dan `reset-password` bisa diakses (sudah ada di `routes/auth.php`)
2. Update `.env` — set `MAIL_MAILER=smtp` dengan credentials yang benar
3. Tambah link "Lupa Password?" di halaman login:

```blade
{{-- resources/views/auth/login.blade.php --}}
@if(Route::has('password.request'))
    <a href="{{ route('password.request') }}" class="underline text-sm text-gray-600 hover:text-gray-900">
        Lupa password?
    </a>
@endif
```

4. Test flow reset password end-to-end

**Estimasi:** 1 jam

---

## FASE 6: Performance & Optimization

**Estimasi:** 4-6 jam
**Dependensi:** Fase 2 selesai

---

### 6.1 Fix N+1 Queries

**File:** `app/Filament/Resources/RegistrationResource.php`

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    // Tambah eager loading
    $query->with(['user', 'examSchedule', 'verifiedBy', 'gradedBy']);

    if ($user?->isMahasiswa()) {
        $query->where('user_id', $user->id);
    }

    return $query;
}
```

**File:** `app/Filament/Resources/InputnilaiResource.php` (atau `ScoringResource.php`)
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['user', 'examSchedule', 'gradedBy'])
        ->where('status', 'verified')
        ->where(function ($query) {
            $query->where('ready_for_scoring', true)
                ->orWhereNotNull('graded_at');
        });
}
```

**Estimasi:** 1 jam

---

### 6.2 Cache Dashboard Statistics

**File:** `app/Filament/Widgets/StatsOverviewWidget.php`

```php
use Illuminate\Support\Facades\Cache;

protected function getData(): array
{
    return Cache::remember('dashboard_stats', 300, function () {
        return [
            'pending_payment' => Registration::where('status', 'pending_payment')->count(),
            'awaiting_verification' => Registration::where('status', 'awaiting_verification')->count(),
            'verified' => Registration::where('status', 'verified')->count(),
            'rejected' => Registration::where('status', 'rejected')->count(),
            'expired' => Registration::where('status', 'expired')->count(),
        ];
    });
}
```

**Tambahkan cache flush** saat ada perubahan data (event listener):
```php
// Di RegistrationStatusChanged listener:
Cache::forget('dashboard_stats');
```

**Estimasi:** 1 jam

---

### 6.3 Cache Chart Data

**File:** `app/Filament/Widgets/RegistrationChartWidget.php`

```php
use Illuminate\Support\Facades\Cache;

protected function getMonthlyData(): array
{
    return Cache::remember('registration_chart', 3600, function () {
        $months = [];
        $counts = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            $count = Registration::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $months[] = $monthName;
            $counts[] = $count;
        }

        return ['months' => $months, 'counts' => $counts];
    });
}
```

**Estimasi:** 30 menit

---

### 6.4 Optimize `EnableExamScoring` Frequency

**File:** `routes/console.php`

```php
// SEBELUM:
Schedule::command(EnableExamScoring::class)->everyMinute();

// SESUDAH:
Schedule::command(EnableExamScoring::class)->everyFiveMinutes();
```

**Estimasi:** 5 menit

---

### 6.5 Add Performance Indexes

**File:** `database/migrations/xxxx_add_performance_indexes.php` (sudah dibuat di Fase 1.8)

**Estimasi:** (sudah termasuk di Fase 1.8)

---

### 6.6 Optimize Filament Table Queries

**File:** Semua Filament Resources

**Action:**
1. Tambah `searchColumns` yang tepat:
   ```php
   Tables\Columns\TextColumn::make('user.name')
       ->searchable(['user.name', 'user.nim'])
       ->sortable()
   ```

2. Gunakan `distinct()` jika ada join
3. Limit default pagination:
   ```php
   protected static ?int $recordsPerPage = 15;
   ```

**Estimasi:** 1 jam

---

## FASE 7: Testing & Deployment

**Estimasi:** 6-8 jam
**Dependensi:** Semua fase selesai

---

### 7.1 Unit Tests

**File:** `tests/Unit/Services/RegistrationServiceTest.php`

```php
<?php

namespace Tests\Unit\Services;

use App\Enums\RegistrationStatus;
use App\Models\ExamSchedule;
use App\Models\Registration;
use App\Models\User;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RegistrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RegistrationService();
    }

    public function test_create_registration_success(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $schedule = ExamSchedule::factory()->create(['quota' => 50, 'is_active' => true]);

        $registration = $this->service->createRegistration($user, $schedule);

        $this->assertNotNull($registration);
        $this->assertEquals($user->id, $registration->user_id);
        $this->assertEquals($schedule->id, $registration->exam_schedule_id);
        $this->assertEquals(RegistrationStatus::PENDING_PAYMENT->value, $registration->status);
        $this->assertNotNull($registration->expires_at);
        $this->assertNotNull($registration->unique_code);
    }

    public function test_create_registration_quota_full_throws_exception(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $schedule = ExamSchedule::factory()->create(['quota' => 1]);

        // Fill the quota
        $this->service->createRegistration(User::factory()->create(['role' => 'mahasiswa']), $schedule);

        $this->expectException(\RuntimeException::class);
        $this->service->createRegistration($user, $schedule);
    }

    public function test_cancel_registration(): void
    {
        $registration = Registration::factory()->create(['status' => 'pending_payment']);

        $this->service->cancelRegistration($registration, 'Alasan pembatalan yang cukup panjang');

        $registration->refresh();
        $this->assertEquals(RegistrationStatus::CANCELLED->value, $registration->status);
        $this->assertEquals('Alasan pembatalan yang cukup panjang', $registration->rejection_reason);
    }

    public function test_upload_payment(): void
    {
        $registration = Registration::factory()->create(['status' => 'pending_payment']);

        $this->service->uploadPayment($registration, 'payments/proof.jpg', 'Nota bayar');

        $registration->refresh();
        $this->assertEquals(RegistrationStatus::AWAITING_VERIFICATION->value, $registration->status);
        $this->assertEquals('payments/proof.jpg', $registration->payment_proof);
    }
}
```

**File:** `tests/Unit/Models/RegistrationTest.php`

```php
<?php

namespace Tests\Unit\Models;

use App\Models\ExamSchedule;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_registration_number(): void
    {
        $schedule = ExamSchedule::factory()->create([
            'session' => '01',
            'exam_date' => '2026-03-15',
        ]);

        $number = Registration::generateRegistrationNumber($schedule);

        $this->assertStringStartsWith('EPT/01/', $number);
        $this->assertStringContainsString('15032026', $number);
    }

    public function test_calculate_average_score(): void
    {
        $registration = Registration::factory()->create([
            'listening_score' => 80,
            'structure_score' => 70,
            'reading_score' => 90,
        ]);

        $average = $registration->calculateAverageScore();

        $this->assertEquals(80.0, $average);
    }

    public function test_calculate_average_score_with_null(): void
    {
        $registration = Registration::factory()->create([
            'listening_score' => 80,
            'structure_score' => null,
            'reading_score' => 90,
        ]);

        $average = $registration->calculateAverageScore();

        $this->assertNull($average);
    }

    public function test_total_payment_attribute(): void
    {
        $schedule = ExamSchedule::factory()->create(['price' => 150000]);
        $registration = Registration::factory()->create([
            'exam_schedule_id' => $schedule->id,
            'unique_code' => 500,
        ]);

        $this->assertEquals(150500, $registration->total_payment);
    }
}
```

**File:** `tests/Unit/Models/ExamScheduleTest.php`

```php
<?php

namespace Tests\Unit\Models;

use App\Models\ExamSchedule;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_count(): void
    {
        $schedule = ExamSchedule::factory()->create();
        Registration::factory()->count(3)->create([
            'exam_schedule_id' => $schedule->id,
            'status' => 'verified',
        ]);

        $this->assertEquals(3, $schedule->registeredCount());
    }

    public function test_available_quota(): void
    {
        $schedule = ExamSchedule::factory()->create(['quota' => 50]);
        Registration::factory()->count(3)->create([
            'exam_schedule_id' => $schedule->id,
            'status' => 'verified',
        ]);

        $this->assertEquals(47, $schedule->availableQuota());
    }

    public function test_is_available(): void
    {
        $schedule = ExamSchedule::factory()->create([
            'is_active' => true,
            'registration_deadline' => now()->addDays(7),
            'quota' => 50,
        ]);

        Registration::factory()->count(3)->create([
            'exam_schedule_id' => $schedule->id,
            'status' => 'verified',
        ]);

        $this->assertTrue($schedule->isAvailable());
    }
}
```

**Estimasi:** 4 jam

---

### 7.2 Feature Tests

**File:** `tests/Feature/RegistrationFlowTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\ExamSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $mahasiswa;
    private ExamSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $this->schedule = ExamSchedule::factory()->create([
            'is_active' => true,
            'quota' => 50,
            'registration_deadline' => now()->addDays(7),
            'price' => 150000,
        ]);
    }

    public function test_mahasiswa_can_view_schedules(): void
    {
        $response = $this->actingAs($this->mahasiswa)
            ->get(route('mahasiswa.schedules.index'));

        $response->assertOk();
    }

    public function test_mahasiswa_can_register(): void
    {
        $response = $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.registrations.store'), [
                'schedule_id' => $this->schedule->id,
                'agreement' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'user_id' => $this->mahasiswa->id,
            'exam_schedule_id' => $this->schedule->id,
            'status' => 'pending_payment',
        ]);
    }

    public function test_mahasiswa_cannot_register_twice(): void
    {
        // Create first registration
        $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.registrations.store'), [
                'schedule_id' => $this->schedule->id,
                'agreement' => true,
            ]);

        // Try to register again
        $response = $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.registrations.store'), [
                'schedule_id' => $this->schedule->id,
                'agreement' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_mahasiswa_cannot_access_admin_panel(): void
    {
        $response = $this->actingAs($this->mahasiswa)
            ->get('/admin');

        $response->assertRedirect();
    }

    public function test_finance_can_verify_payment(): void
    {
        $finance = User::factory()->create(['role' => 'finance']);
        $registration = \App\Models\Registration::factory()->create([
            'user_id' => $this->mahasiswa->id,
            'exam_schedule_id' => $this->schedule->id,
            'status' => 'awaiting_verification',
        ]);

        // This would be tested via Filament action, not direct HTTP
        $this->actingAs($finance);

        // Assert finance can view registrations
        $this->assertTrue($finance->isFinance());
    }
}
```

**Estimasi:** 3 jam

---

### 7.3 Deployment Checklist

**Pre-Deployment:**
- [ ] Semua tests pass: `php artisan test`
- [ ] No debug code left: `APP_DEBUG=false` di `.env`
- [ ] `.env` production configured:
  - [ ] `APP_URL=https://ept.uctapradema.id`
  - [ ] `MAIL_MAILER=smtp` (bukan log)
  - [ ] `DB_PASSWORD` bukan placeholder
  - [ ] `APP_KEY` sudah di-generate
- [ ] Database backup sebelum migration

**Deployment Commands:**
```bash
# Pull code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Run migrations
php artisan migrate --force

# Seed if needed
php artisan db:seed --class=RoleSeeder

# Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Cron Job (di server):**
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Queue Worker (jika pakai queue untuk notifications):**
```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

**Post-Deployment Verification:**
- [ ] Homepage loads: `https://ept.uctapradema.id`
- [ ] Login works: `https://ept.uctapradema.id/login`
- [ ] Admin panel accessible: `https://ept.uctapradema.id/admin`
- [ ] Registration flow works end-to-end
- [ ] Payment upload works
- [ ] Notifications sending correctly
- [ ] Export/print functions work

**Estimasi:** 1 jam

---

## Ringkasan File

### File yang Diubah (Existing)

| # | File | Perubahan |
|---|------|-----------|
| 1 | `app/Models/User.php` | Fix scope, tambah relasi, tambah photo field |
| 2 | `app/Models/Registration.php` | Fix race condition, tambah status handling, reminder fields |
| 3 | `app/Models/ExamSchedule.php` | Fix time casting, tambah relasi ExamSession |
| 4 | `app/Services/RegistrationService.php` | Tambah state transition methods |
| 5 | `app/Http/Controllers/Mahasiswa/RegistrationController.php` | Fix disk, tambah search/filter |
| 6 | `app/Filament/Resources/RegistrationResource.php` | Refactor actions, bulk actions, eager loading |
| 7 | `app/Filament/Resources/InputnilaiResource.php` | Rename ke ScoringResource |
| 8 | `app/Filament/Pages/Participants.php` | Fix exit, refactor export |
| 9 | `app/Filament/Widgets/StatsOverviewWidget.php` | Tambah caching |
| 10 | `app/Filament/Widgets/RegistrationChartWidget.php` | Tambah caching |
| 11 | `app/Console/Commands/EnableExamScoring.php` | Optimize frequency |
| 12 | `app/Providers/AppServiceProvider.php` | Register event listeners |
| 13 | `routes/web.php` | Rate limiting, search params |
| 14 | `routes/console.php` | Update schedule frequency |
| 15 | `resources/views/mahasiswa/dashboard.blade.php` | Tambah announcements |
| 16 | `resources/views/mahasiswa/registrations/index.blade.php` | Search & filter |
| 17 | `resources/views/mahasiswa/registrations/show.blade.php` | Countdown JS |
| 18 | `resources/views/auth/login.blade.php` | Link forgot password |
| 19 | `resources/views/profile/edit.blade.php` | Foto profil upload |
| 20 | `database/seeders/RoleSeeder.php` | Update permissions |

### File yang Dibuat (New)

| # | File | Deskripsi |
|---|------|-----------|
| 1 | `app/Services/RegistrationVerificationService.php` | Verify & reject logic |
| 2 | `app/Events/RegistrationStatusChanged.php` | Event state change |
| 3 | `app/Listeners/SendRegistrationNotification.php` | Handle notification |
| 4 | `app/Listeners/LogRegistrationActivity.php` | Activity log |
| 5 | `app/Filament/Actions/VerifyPaymentAction.php` | Reusable action |
| 6 | `app/Filament/Actions/RejectPaymentAction.php` | Reusable action |
| 7 | `app/Models/Bank.php` | Master bank |
| 8 | `app/Models/Faculty.php` | Master fakultas |
| 9 | `app/Models/Major.php` | Master prodi |
| 10 | `app/Models/ExamSession.php` | Master sesi ujian |
| 11 | `app/Models/Announcement.php` | Pengumuman |
| 12 | `app/Filament/Resources/BankResource.php` | CRUD bank |
| 13 | `app/Filament/Resources/FacultyResource.php` | CRUD fakultas |
| 14 | `app/Filament/Resources/MajorResource.php` | CRUD prodi |
| 15 | `app/Filament/Resources/AnnouncementResource.php` | CRUD pengumuman |
| 16 | `app/Filament/Resources/ActivityLogResource.php` | View activity log |
| 17 | `app/Exports/ParticipantsExport.php` | Excel export |
| 18 | `app/Exports/RegistrationsExport.php` | Excel export |
| 19 | `app/Notifications/RegistrationExpiredNotification.php` | Notifikasi expired |
| 20 | `app/Notifications/PaymentReminderNotification.php` | Reminder pembayaran |
| 21 | `app/Notifications/AnnouncementNotification.php` | Notifikasi pengumuman |
| 22 | `app/Console/Commands/SendPaymentReminders.php` | Reminder command |
| 23 | `database/migrations/xxxx_drop_inputnilais_table.php` | Hapus tabel inputnilais |
| 24 | `database/migrations/xxxx_add_photo_to_users_table.php` | Foto profil |
| 25 | `database/migrations/xxxx_create_banks_table.php` | Master bank |
| 26 | `database/migrations/xxxx_create_faculties_table.php` | Master fakultas |
| 27 | `database/migrations/xxxx_create_majors_table.php` | Master prodi |
| 28 | `database/migrations/xxxx_create_exam_sessions_table.php` | Master sesi |
| 29 | `database/migrations/xxxx_create_announcements_table.php` | Pengumuman |
| 30 | `database/migrations/xxxx_add_reminder_fields_to_registrations.php` | Reminder fields |
| 31 | `database/migrations/xxxx_add_performance_indexes.php` | Performance indexes |
| 32 | `database/seeders/BankSeeder.php` | Seed bank |
| 33 | `database/seeders/FacultySeeder.php` | Seed fakultas |
| 34 | `database/seeders/MajorSeeder.php` | Seed prodi |
| 35 | `database/seeders/ExamSessionSeeder.php` | Seed sesi |
| 36 | `tests/Unit/Services/RegistrationServiceTest.php` | Unit test |
| 37 | `tests/Unit/Services/RegistrationVerificationServiceTest.php` | Unit test |
| 38 | `tests/Unit/Models/RegistrationTest.php` | Unit test |
| 39 | `tests/Unit/Models/ExamScheduleTest.php` | Unit test |
| 40 | `tests/Feature/RegistrationFlowTest.php` | Feature test |

### File yang Dihapus (Deprecated)

| # | File | Alasan |
|---|------|--------|
| 1 | `app/Models/Inputnilai.php` | Redundant |
| 2 | `database/migrations/2026_02_27_020312_create_inputnilais_table.php` | Hapus tabel |

---

## Urutan Eksekusi

```
FASE 1 (Bug Fixes)
├── 1.1 Fix scope Active() ──────────────────────────┐
├── 1.2 Hapus Inputnilai model ──────────────────────┤
├── 1.3 Fix race condition ──────────────────────────┤
├── 1.4 Fix payment disk ────────────────────────────┼──→ FASE 2 (Refactor)
├── 1.5 Fix exit di Participants ────────────────────┤         │
├── 1.6 Fix time casting ────────────────────────────┤         ├──→ FASE 4 (Admin Features)
├── 1.7 Fix registration number ─────────────────────┤         │         │
├── 1.8 Tambah indexes ──────────────────────────────┤         │         ├──→ FASE 7 (Testing)
└── 1.9 Verify kolom exist ──────────────────────────┘         │         │
                                                                │         │
FASE 3 (Security) ─────────────────────────────────────────────┤         │
├── 3.1 Authorization export ──────────────────────────────────┤         │
├── 3.2 Rate limiting ─────────────────────────────────────────┤         │
├── 3.3 File upload validation ────────────────────────────────┤         │
├── 3.4 Permission-based checks ───────────────────────────────┤         │
└── 3.5 Activity log ──────────────────────────────────────────┘         │
                                                                          │
FASE 5 (Mahasiswa Features) ────────────────────────────────────────────┤
├── 5.1 Email notifications ────────────────────────────────────────────┤
├── 5.2 Payment reminder command ───────────────────────────────────────┤
├── 5.3 Search & filter ────────────────────────────────────────────────┤
├── 5.4 Countdown timer ────────────────────────────────────────────────┤
├── 5.5 Upload foto profil ─────────────────────────────────────────────┤
└── 5.6 Reset password ─────────────────────────────────────────────────┘
                                                                          │
FASE 6 (Performance) ──────────────────────────────────────────────────┘
├── 6.1 Fix N+1 queries
├── 6.2 Cache dashboard stats
├── 6.3 Cache chart data
├── 6.4 Optimize command frequency
├── 6.5 Database indexes
└── 6.6 Optimize Filament tables
```

---

## Checklist Progress

### Fase 1: Bug Fixes & Konsolidasi
- [ ] 1.1 Fix scope `Active()` → `active()`
- [ ] 1.2 Hapus model `Inputnilai` & migration
- [ ] 1.3 Fix registration number race condition
- [ ] 1.4 Fix payment proof disk mismatch
- [ ] 1.5 Fix `exit` di `Participants.php`
- [ ] 1.6 Fix `ExamSchedule` time casting
- [ ] 1.7 Fix `CreateRegistration` inconsistent number
- [ ] 1.8 Tambah performance indexes
- [ ] 1.9 Verifikasi kolom exist

### Fase 2: Refactor Architecture
- [ ] 2.1 Buat `RegistrationVerificationService`
- [ ] 2.2 Buat Event/Listener state machine
- [ ] 2.3 Rename `InputnilaiResource` → `ScoringResource`
- [ ] 2.4 Pisahkan actions ke komponen
- [ ] 2.5 Konsolidasi naming convention

### Fase 3: Security & Authorization
- [ ] 3.1 Authorization di export
- [ ] 3.2 Rate limiting
- [ ] 3.3 File upload validation ketat
- [ ] 3.4 Permission-based checks
- [ ] 3.5 Activity log

### Fase 4: Fitur Baru — Admin
- [ ] 4.1 Bulk actions verifikasi
- [ ] 4.2 Master data tables (4 tabel)
- [ ] 4.3 Duplikat jadwal
- [ ] 4.4 Export Excel dengan styling
- [ ] 4.5 Broadcast pengumuman

### Fase 5: Fitur Baru — Mahasiswa
- [ ] 5.1 Email notifications (expired & reminder)
- [ ] 5.2 Payment reminder command
- [ ] 5.3 Search & filter riwayat
- [ ] 5.4 Real-time countdown timer
- [ ] 5.5 Upload foto profil
- [ ] 5.6 Reset password

### Fase 6: Performance & Optimization
- [ ] 6.1 Fix N+1 queries
- [ ] 6.2 Cache dashboard statistics
- [ ] 6.3 Cache chart data
- [ ] 6.4 Optimize command frequency
- [ ] 6.5 Database indexes
- [ ] 6.6 Optimize Filament tables

### Fase 7: Testing & Deployment
- [ ] 7.1 Unit tests
- [ ] 7.2 Feature tests
- [ ] 7.3 Deployment checklist

---

**Total Estimasi:** 54-69 jam kerja
**Dibuat:** 25 Juni 2026
**Status:** Siap Implementasi
