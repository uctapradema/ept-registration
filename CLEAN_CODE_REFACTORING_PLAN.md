# CLEAN CODE REFACTORING PLAN — EPT APPLICATION

**Tanggal:** 25 Juni 2026
**Versi:** 1.0
**Skor Clean Code Saat Ini:** 4.4/10
**Target Skor:** 8.5/10
**Estimasi Total:** 25-30 jam

---

## DAFTAR ISI

1. [Prinsip Clean Code yang Dilanggar](#prinsip-clean-code)
2. [Fase A: Naming & Identifiers](#fase-a-naming--identifiers)
3. [Fase B: Separation of Concerns](#fase-b-separation-of-concerns)
4. [Fase C: Function & Method Refactoring](#fase-c-function--method-refactoring)
5. [Fase D: DRY — Eliminate Duplication](#fase-d-dry--eliminate-duplication)
6. [Fase E: Type Safety & Strictness](#fase-e-type-safety--strictness)
7. [Fase F: Error Handling Consistency](#fase-f-error-handling-consistency)
8. [Fase G: Code Organization](#fase-g-code-organization)
9. [Fase H: Dead Code Removal](#fase-h-dead-code-removal)
10. [Ringkasan Transformasi File](#ringkasan-transformasi)
11. [Before/After Comparison](#beforeafter-comparison)
12. [Checklist](#checklist)

---

## Prinsip Clean Code

| Prinsip | Deskripsi | Violation di Kode Ini |
|---------|-----------|----------------------|
| **Meaningful Names** | Nama harus reveal intent | `Inputnilai`, CSS di model attributes |
| **Small Functions** | Fungsi harus single-purpose | `table()` 120+ baris |
| **SRP (Single Responsibility)** | Satu class = satu alasan change | `RegistrationResource` god class |
| **DRY** | Jangan duplikasi logic | `calculateAverageScore()` duplikat |
| **No Side Effects** | Function harus predictable | `exit()` di `Participants.php` |
| **Minimal Dependencies** | Loose coupling | Controller langsung query DB |
| **Consistent Patterns** | Konsisten di seluruh kodebase | Mixed error handling |
| **Testable** | Mudah di-unit test | Logic berceceran, hard to mock |

---

## Fase A: Naming & Identifiers

**Estimasi:** 3-4 jam
**Prinsip:** *"Names should reveal intent. Avoid abbreviations. Be consistent."*

---

### A.1 Rename Model `Inputnilai` → `Scoring`

**Alasan:** `Inputnilai` adalah Bahasa Indonesia, semua model lain English. Tidak konsisten dan tidak pronounceable.

**Files diubah:**

#### Hapus & Buat Ulang Migration

```php
// database/migrations/2026_02_27_212333_drop_inputnilais_table.php
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
        // Tidak bisa di-rollback
    }
};
```

#### Hapus Model Lama
```
HAPUS: app/Models/Inputnilai.php
```

#### Rename Filament Resource

```
SEBELUM:
  app/Filament/Resources/InputnilaiResource.php
  app/Filament/Resources/InputnilaiResource/Pages/ListInputnilais.php

SESUDAH:
  app/Filament/Resources/ScoringResource.php
  app/Filament/Resources/ScoringResource/Pages/ListScorings.php
```

#### Update `ScoringResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScoringResource\Pages;
use App\Models\Registration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScoringResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'Input Nilai';

    protected static ?string $navigationGroup = 'Manajemen Ujian';

    protected static ?string $modelLabel = 'Input Nilai';

    protected static ?string $pluralModelLabel = 'Input Nilai';

    // ... form, table, getEloquentQuery tetap sama
    // hanya class name dan file location yang berubah

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScorings::route('/'),
        ];
    }
}
```

#### Update `ListScorings.php`

```php
<?php

namespace App\Filament\Resources\ScoringResource\Pages;

use App\Filament\Resources\ScoringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScorings extends ListRecords
{
    protected static string $resource = ScoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
```

**Estimasi:** 1 jam

---

### A.2 Hapus UI Logic dari Model `Registration`

**Masalah:** Model punya CSS classes dan label text — ini presentation concern.

**SEBELUM (`Registration.php`):**

```php
public function getStatusColorAttribute(): string
{
    return match($this->status) {
        'pending_payment' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'awaiting_verification' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'verified' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'expired' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        default => 'bg-gray-100 text-gray-800',
    };
}

public function getStatusLabelAttribute(): string
{
    return match($this->status) {
        'pending_payment' => 'Menunggu Pembayaran',
        'awaiting_verification' => 'Menunggu Verifikasi',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'expired' => 'Kadaluarsa',
        default => $this->status,
    };
}
```

**SESUDAH — Pindahkan ke Enum `RegistrationStatus`:**

```php
<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case AWAITING_VERIFICATION = 'awaiting_verification';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::AWAITING_VERIFICATION => 'Menunggu Verifikasi',
            self::VERIFIED => 'Terverifikasi',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::EXPIRED => 'Kadaluarsa',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'warning',
            self::AWAITING_VERIFICATION => 'info',
            self::VERIFIED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED, self::EXPIRED => 'gray',
        };
    }

    public function tailwindClasses(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            self::AWAITING_VERIFICATION => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            self::VERIFIED => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            self::REJECTED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            self::CANCELLED => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            self::EXPIRED => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function colors(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->color()])
            ->toArray();
    }
}
```

**Hapus dari `Registration.php`:**

```php
// HAPUS method-method ini dari Registration.php:
// - getStatusColorAttribute()
// - getStatusLabelAttribute()

// Model sekarang hanya punya:
protected function casts(): array
{
    return [
        'status' => RegistrationStatus::class,  // Gunakan Enum cast
        // ...
    ];
}
```

**Update View yang menggunakan:**

```blade
<!-- SEBELUM: -->
<span class="{{ $registration->status_color }}">
    {{ $registration->status_label }}
</span>

<!-- SESUDAH: -->
<span class="{{ $registration->status->tailwindClasses() }}">
    {{ $registration->status->label() }}
</span>
```

**Estimasi:** 2 jam

---

### A.3 Extract Magic Numbers ke Constants

**SEBELUM:**

```php
// DashboardController.php
$recentRegistrations = Registration::with('examSchedule')
    ->where('user_id', $user->id)
    ->history()
    ->latest()
    ->take(5)  // Magic number
    ->get();

// RegistrationController.php
$registrations = $query->paginate(10);  // Magic number

// ExamSchedule.php
if ($available <= 10) {  // Magic number
    return 'limited';
}
```

**SESUDAH — Tambah Constants:**

```php
// app/Constants/AppConstants.php
<?php

namespace App\Constants;

final class AppConstants
{
    // Pagination
    public const DEFAULT_PAGE_SIZE = 10;
    public const DASHBOARD_RECENT_LIMIT = 5;

    // Quota
    public const QUOTA_LOW_THRESHOLD = 10;

    // Payment
    public const MIN_CANCEL_REASON_LENGTH = 10;
    public const MAX_CANCEL_REASON_LENGTH = 500;

    // Unique Code
    public const DEFAULT_UNIQUE_CODE_MIN = 100;
    public const DEFAULT_UNIQUE_CODE_MAX = 999;

    // Scoring
    public const MAX_SCORE = 100;
    public const PASSING_SCORE = 450;

    private function __construct() {}
}
```

**Update penggunaan:**

```php
use App\Constants\AppConstants;

// DashboardController.php
->take(AppConstants::DASHBOARD_RECENT_LIMIT)

// RegistrationController.php
->paginate(AppConstants::DEFAULT_PAGE_SIZE)

// ExamSchedule.php
if ($available <= AppConstants::QUOTA_LOW_THRESHOLD) {
    return 'limited';
}
```

**Estimasi:** 1 jam

---

## Fase B: Separation of Concerns

**Estimasi:** 6-8 jam
**Prinsip:** *"A class should have only one reason to change (SRP)."*

---

### B.1 Buat `ScoringService` — Pindahkan Business Logic dari Filament

**File baru:** `app/Services/ScoringService.php`

```php
<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ScoringService
{
    public const MIN_SCORE = 0;
    public const MAX_SCORE = 100;

    /**
     * Input or update exam scores for a registration.
     */
    public function inputScores(
        Registration $registration,
        int $listeningScore,
        int $structureScore,
        int $readingScore,
        User $gradedBy
    ): Registration {
        $this->validateScores($listeningScore, $structureScore, $readingScore);

        $averageScore = $this->calculateAverage($listeningScore, $structureScore, $readingScore);

        $registration->update([
            'listening_score' => $listeningScore,
            'structure_score' => $structureScore,
            'reading_score' => $readingScore,
            'average_score' => $averageScore,
            'graded_by' => $gradedBy->id,
            'graded_at' => now(),
            'ready_for_scoring' => false,
        ]);

        return $registration->fresh();
    }

    /**
     * Calculate average score from three components.
     */
    public function calculateAverage(int $listening, int $structure, int $reading): float
    {
        return round(($listening + $structure + $reading) / 3, 2);
    }

    /**
     * Validate that all scores are within acceptable range.
     */
    private function validateScores(int $listening, int $structure, int $reading): void
    {
        $scores = ['listening' => $listening, 'structure' => $structure, 'reading' => $reading];

        foreach ($scores as $component => $score) {
            if ($score < self::MIN_SCORE || $score > self::MAX_SCORE) {
                throw new \InvalidArgumentException(
                    "Score {$component} must be between " . self::MIN_SCORE . " and " . self::MAX_SCORE
                );
            }
        }
    }
}
```

**Update `InputnilaiResource.php` (atau `ScoringResource.php`):**

```php
// SEBELUM (logic langsung di action):
->action(function (Registration $record, array $data): void {
    $average = round(($data['listening_score'] + $data['structure_score'] + $data['reading_score']) / 3, 2);

    $record->update([
        'listening_score' => $data['listening_score'],
        'structure_score' => $data['structure_score'],
        'reading_score' => $data['reading_score'],
        'average_score' => $average,
        'graded_by' => auth()->user()?->id(),
        'graded_at' => now(),
        'ready_for_scoring' => false,
    ]);
})

// SESUDAH (delegate ke service):
->action(function (Registration $record, array $data): void {
    app(ScoringService::class)->inputScores(
        $record,
        $data['listening_score'],
        $data['structure_score'],
        $data['reading_score'],
        auth()->user()
    );
})
```

**Estimasi:** 2 jam

---

### B.2 Refactor `RegistrationResource` — Extract Actions

**File baru:** `app/Filament/Actions/VerifyPaymentAction.php`

```php
<?php

namespace App\Filament\Actions;

use App\Models\Registration;
use App\Services\PaymentVerificationService;
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
                $record->isAwaitingVerification() &&
                auth()->user()->canVerifyPayment()
            )
            ->action(function (Registration $record): void {
                app(PaymentVerificationService::class)->verify(
                    $record,
                    auth()->user()
                );
            });
    }
}
```

**File baru:** `app/Filament/Actions/RejectPaymentAction.php`

```php
<?php

namespace App\Filament\Actions;

use App\Models\Registration;
use App\Services\PaymentVerificationService;
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
                $record->isAwaitingVerification() &&
                auth()->user()->canVerifyPayment()
            )
            ->action(function (Registration $record, array $data): void {
                app(PaymentVerificationService::class)->reject(
                    $record,
                    $data['rejection_reason'],
                    auth()->user()
                );
            });
    }
}
```

**File baru:** `app/Services/PaymentVerificationService.php`

```php
<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Events\RegistrationStatusChanged;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentVerificationService
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

            event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::VERIFIED));
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

            event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::REJECTED));
        });
    }
}
```

**Update `RegistrationResource.php` — Sekarang lebih bersih:**

```php
use App\Filament\Actions\VerifyPaymentAction;
use App\Filament\Actions\RejectPaymentAction;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ... columns tetap sama
        ])
        ->filters([
            // ... filters tetap sama
        ])
        ->actions([
            VerifyPaymentAction::make(),
            RejectPaymentAction::make(),
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make()
                ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
        ])
        // ... bulk actions tetap sama
}
```

**Estimasi:** 3 jam

---

### B.3 Pindahkan Query Logic ke Query Scope

**SEBELUM — Duplikasi query di banyak tempat:**

```php
// DashboardController.php
$activeRegistration = Registration::with('examSchedule')
    ->where('user_id', $user->id)
    ->active()
    ->latest()
    ->first();

// Participants.php
$query = Registration::with(['user', 'examSchedule'])
    ->where('status', 'verified')
    ->orderBy('payment_verified_at', 'asc');

// InputnilaiResource.php
return parent::getEloquentQuery()
    ->where('status', 'verified')
    ->where(function ($query) {
        $query->where('ready_for_scoring', true)
            ->orWhereNotNull('graded_at');
    });
```

**SESUDAH — Buat Query Scopes di `Registration.php`:**

```php
<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Builder;

class Registration extends Model
{
    // ... existing code ...

    /**
     * Scope: Registrations yang sedang aktif (belum selesai).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RegistrationStatus::PENDING_PAYMENT,
            RegistrationStatus::AWAITING_VERIFICATION,
            RegistrationStatus::VERIFIED,
        ]);
    }

    /**
     * Scope: Registrations yang sudah selesai (history).
     */
    public function scopeHistory(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RegistrationStatus::VERIFIED,
            RegistrationStatus::REJECTED,
            RegistrationStatus::CANCELLED,
            RegistrationStatus::EXPIRED,
        ]);
    }

    /**
     * Scope: Registrations yang menunggu verifikasi.
     */
    public function scopeAwaitingVerification(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RegistrationStatus::PENDING_PAYMENT,
            RegistrationStatus::AWAITING_VERIFICATION,
        ]);
    }

    /**
     * Scope: Registrations yang siap dinilai.
     */
    public function scopeReadyForScoring(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::VERIFIED)
            ->where(function (Builder $q) {
                $q->where('ready_for_scoring', true)
                    ->orWhereNotNull('graded_at');
            });
    }

    /**
     * Scope: Registrations yang sudah verified (untuk export/print).
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::VERIFIED);
    }

    /**
     * Scope: Filter by user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by exam schedule.
     */
    public function scopeForSchedule(Builder $query, int $scheduleId): Builder
    {
        return $query->where('exam_schedule_id', $scheduleId);
    }
}
```

**Update semua file yang menggunakan:**

```php
// DashboardController.php — SEBELUM:
$activeRegistration = Registration::with('examSchedule')
    ->where('user_id', $user->id)
    ->active()
    ->latest()
    ->first();

// DashboardController.php — SESUDAH:
$activeRegistration = Registration::with('examSchedule')
    ->forUser($user->id)
    ->active()
    ->latest()
    ->first();

// Participants.php — SEBELUM:
$query = Registration::with(['user', 'examSchedule'])
    ->where('status', 'verified')
    ->orderBy('payment_verified_at', 'asc');

// Participants.php — SESUDAH:
$query = Registration::with(['user', 'examSchedule'])
    ->verified()
    ->orderBy('payment_verified_at', 'asc');

// InputnilaiResource.php — SEBELUM:
return parent::getEloquentQuery()
    ->where('status', 'verified')
    ->where(function ($query) {
        $query->where('ready_for_scoring', true)
            ->orWhereNotNull('graded_at');
    });

// InputnilaiResource.php — SESUDAH:
return parent::getEloquentQuery()->readyForScoring();
```

**Estimasi:** 2 jam

---

### B.4 Pindahkan Export Logic ke Dedicated Export Class

**File baru:** `app/Exports/ParticipantsExport.php`

```php
<?php

namespace App\Exports;

use App\Models\Registration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private int $rowIndex = 1;

    public function __construct(
        private ?int $examScheduleId = null
    ) {}

    public function collection(): Collection
    {
        $query = Registration::with(['user', 'examSchedule'])
            ->verified()
            ->orderBy('payment_verified_at', 'asc');

        if ($this->examScheduleId) {
            $query->forSchedule($this->examScheduleId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Pendaftaran',
            'Nama',
            'NIM',
            'Prodi',
            'Fakultas',
            'Jadwal',
            'Tanggal Ujian',
            'Tgl Verifikasi',
        ];
    }

    public function map(mixed $registration): array
    {
        return [
            $this->rowIndex++,
            $registration->registration_number,
            $registration->user?->name ?? '-',
            $registration->user?->nim ?? '-',
            $registration->user?->major ?? '-',
            $registration->user?->faculty ?? '-',
            $registration->examSchedule?->title ?? '-',
            $registration->examSchedule?->exam_date?->format('d F Y') ?? '-',
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

**Simplify `Participants.php`:**

```php
// SEBELUM (60+ baris export logic):
public function exportExcel(): StreamedResponse
{
    // ... 60 baris logic
}

// SESUDAH (5 baris):
public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
{
    $examScheduleId = request()->get('exam_schedule_id');
    $filename = $this->generateFilename($examScheduleId, 'xlsx');

    return Excel::download(new ParticipantsExport($examScheduleId), $filename);
}

private function generateFilename(?int $scheduleId, string $extension): string
{
    $base = 'daftar_peserta_ept';

    if ($scheduleId) {
        $schedule = ExamSchedule::find($scheduleId);
        if ($schedule) {
            $title = str_replace(' ', '_', $schedule->title);
            $date = $schedule->exam_date?->format('Y-m-d') ?? date('Y-m-d');
            return "{$title}_Sesi{$schedule->session}_{$date}.{$extension}";
        }
    }

    return "{$base}_" . date('Y-m-d') . ".{$extension}";
}
```

**Estimasi:** 2 jam

---

## Fase C: Function & Method Refactoring

**Estimasi:** 4-5 jam
**Prinsip:** *"Functions should be small. They should do one thing. They should be at one level of abstraction."*

---

### C.1 Break Down `RegistrationResource::table()` — Extract Columns

**SEBELUM (120+ baris):**

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ... 15 columns, 60+ baris
        ])
        ->filters([
            // ... filters
        ])
        ->actions([
            // ... inline actions dengan business logic
        ])
        ->bulkActions([
            // ...
        ]);
}
```

**SESUDAH — Extract ke Methods:**

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Columns;
use App\Filament\Actions;
use App\Filament\Filters;

class RegistrationResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->actions(self::getActions())
            ->bulkActions(self::getBulkActions())
            ->defaultSort('created_at', 'desc');
    }

    private static function getColumns(): array
    {
        return [
            Columns::registrationNumber(),
            Columns::participantName(),
            Columns::participantNim(),
            Columns::examScheduleTitle(),
            Columns::examDate(),
            Columns::statusBadge(),
            Columns::paymentUploadedAt(),
            Columns::paymentProofThumbnail(),
            Columns::paymentVerifiedAt(),
            Columns::verifiedByName(),
            Columns::createdAt(),
        ];
    }

    private static function getFilters(): array
    {
        return [
            Filters::statusFilter(),
            Filters::examScheduleFilter(),
        ];
    }

    private static function getActions(): array
    {
        return [
            Actions\VerifyPaymentAction::make(),
            Actions\RejectPaymentAction::make(),
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make()
                ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
        ];
    }

    private static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
                Actions\VerifyBulkAction::make(),
                Actions\ExportSelectedBulkAction::make(),
            ]),
        ];
    }
}
```

**File baru:** `app/Filament/Columns.php` — Reusable Column Definitions

```php
<?php

namespace App\Filament;

use App\Enums\RegistrationStatus;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class Columns
{
    public static function registrationNumber(): TextColumn
    {
        return TextColumn::make('registration_number')
            ->label('No. Pendaftaran')
            ->searchable()
            ->sortable()
            ->copyable();
    }

    public static function participantName(): TextColumn
    {
        return TextColumn::make('user.name')
            ->label('Peserta')
            ->searchable()
            ->sortable();
    }

    public static function participantNim(): TextColumn
    {
        return TextColumn::make('user.nim')
            ->label('NIM')
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    public static function examScheduleTitle(): TextColumn
    {
        return TextColumn::make('examSchedule.title')
            ->label('Jadwal Ujian')
            ->searchable()
            ->sortable()
            ->limit(30);
    }

    public static function examDate(): TextColumn
    {
        return TextColumn::make('examSchedule.exam_date')
            ->label('Tanggal Ujian')
            ->date('d F Y')
            ->sortable();
    }

    public static function statusBadge(): TextColumn
    {
        return TextColumn::make('status')
            ->label('Status')
            ->badge()
            ->color(fn (string $state): string => RegistrationStatus::tryFrom($state)?->color() ?? 'gray')
            ->formatStateUsing(fn (string $state): string => RegistrationStatus::tryFrom($state)?->label() ?? $state)
            ->sortable();
    }

    public static function paymentUploadedAt(): TextColumn
    {
        return TextColumn::make('payment_uploaded_at')
            ->label('Upload Pembayaran')
            ->dateTime('d F Y, H:i')
            ->sortable()
            ->toggleable();
    }

    public static function paymentProofThumbnail(): Tables\Columns\ImageColumn
    {
        return Tables\Columns\ImageColumn::make('payment_proof')
            ->label('Bukti Bayar')
            ->disk('public')
            ->size(40)
            ->circular()
            ->toggleable()
            ->visible(fn ($record) => $record && $record->payment_proof);
    }

    public static function paymentVerifiedAt(): TextColumn
    {
        return TextColumn::make('payment_verified_at')
            ->label('Waktu Verifikasi')
            ->dateTime('d F Y, H:i')
            ->sortable()
            ->toggleable();
    }

    public static function verifiedByName(): TextColumn
    {
        return TextColumn::make('verifiedBy.name')
            ->label('Diverifikasi Oleh')
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    public static function createdAt(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label('Dibuat')
            ->dateTime('d F Y, H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
```

**Estimasi:** 2 jam

---

### C.2 Extract `Registration` Model Methods — Simplify

**SEBELUM (`Registration.php` — terlalu banyak method):**

```php
class Registration extends Model
{
    // ... 200+ baris
    public function getStatusLabelAttribute(): string { ... }
    public function getStatusColorAttribute(): string { ... }
    public function getHistoryNoteAttribute(): ?string { ... }
    public function getTotalPaymentAttribute(): int { ... }
    public static function generateRegistrationNumber(): string { ... }
    public static function generateUniqueCode(): int { ... }
    public function calculateAverageScore(): ?float { ... }
    public function scopeActive(): Builder { ... }
    public function scopeHistory(): Builder { ... }
    public function scopeReadyForScoring(): Builder { ... }
    // ... masih banyak lagi
}
```

**SESUDAH — Pindahkan ke tempat yang tepat:**

```php
<?php

namespace App\Models;

use App\Constants\AppConstants;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    public const PAYMENT_DEADLINE_HOURS = AppConstants::DEFAULT_PAYMENT_DEADLINE_HOURS;

    protected $fillable = [
        'user_id',
        'exam_schedule_id',
        'registration_number',
        'status',
        'payment_proof',
        'payment_note',
        'payment_uploaded_at',
        'payment_verified_at',
        'verified_by',
        'rejection_reason',
        'expires_at',
        'unique_code',
        'listening_score',
        'structure_score',
        'reading_score',
        'average_score',
        'exam_completed_at',
        'graded_by',
        'graded_at',
        'ready_for_scoring',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'payment_uploaded_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'exam_completed_at' => 'datetime',
            'graded_at' => 'datetime',
            'listening_score' => 'integer',
            'structure_score' => 'integer',
            'reading_score' => 'integer',
            'average_score' => 'decimal:2',
            'ready_for_scoring' => 'boolean',
        ];
    }

    // ─── Relationships ──────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // ─── Query Scopes ───────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RegistrationStatus::PENDING_PAYMENT,
            RegistrationStatus::AWAITING_VERIFICATION,
            RegistrationStatus::VERIFIED,
        ]);
    }

    public function scopeHistory(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RegistrationStatus::VERIFIED,
            RegistrationStatus::REJECTED,
            RegistrationStatus::CANCELLED,
            RegistrationStatus::EXPIRED,
        ]);
    }

    public function scopeAwaitingVerification(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RegistrationStatus::PENDING_PAYMENT,
            RegistrationStatus::AWAITING_VERIFICATION,
        ]);
    }

    public function scopeReadyForScoring(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::VERIFIED)
            ->where(function (Builder $q) {
                $q->where('ready_for_scoring', true)
                    ->orWhereNotNull('graded_at');
            });
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::VERIFIED);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSchedule(Builder $query, int $scheduleId): Builder
    {
        return $query->where('exam_schedule_id', $scheduleId);
    }

    // ─── Business Logic ─────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isAwaitingVerification(): bool
    {
        return in_array($this->status, [
            RegistrationStatus::PENDING_PAYMENT,
            RegistrationStatus::AWAITING_VERIFICATION,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return $this->isAwaitingVerification();
    }

    // ─── Accessors ──────────────────────────────────────

    public function getTotalPaymentAttribute(): int
    {
        $price = $this->examSchedule->price ?? 0;

        return $price + ($this->unique_code ?? 0);
    }
}
```

**Estimasi:** 2 jam

---

### C.3 Simplify `RegistrationController` — Extract Helpers

**SEBELUM (`RegistrationController.php`):**

```php
class RegistrationController extends Controller
{
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

    private function storePaymentFile($file, $registration): string
    {
        $extension = $file->getClientOriginalExtension();
        $safeExtension = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($extension));
        $randomName = bin2hex(random_bytes(16));
        $fileName = $randomName . '.' . $safeExtension;
        return $file->storeAs('payments', $fileName, 'public');
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
```

**SESUDAH — Extract ke Service + Helper:**

```php
<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\StorePaymentRequest;
use App\Models\Registration;
use App\Services\FileStorageService;
use App\Services\PaymentService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private FileStorageService $fileStorage,
        private ResponseService $response,
    ) {}

    public function storePayment(StorePaymentRequest $request, Registration $registration)
    {
        $this->authorize('uploadPayment', $registration);
        $this->paymentService->validatePaymentStatus($registration);

        try {
            $path = $this->fileStorage->storePayment(
                $request->file('payment_proof')
            );

            $this->paymentService->uploadProof(
                $registration,
                $path,
                $request->payment_note
            );

            return $this->response->success(
                $request,
                'Bukti pembayaran berhasil diupload. Menunggu verifikasi.',
                route('mahasiswa.registrations.show', $registration)
            );

        } catch (\Exception $e) {
            return $this->response->error($request, $e->getMessage());
        }
    }
}
```

**File baru:** `app/Services/FileStorageService.php`

```php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileStorageService
{
    public function storePayment(UploadedFile $file): string
    {
        $safeExtension = $this->sanitizeExtension($file->getClientOriginalExtension());
        $randomName = bin2hex(random_bytes(16));
        $fileName = "{$randomName}.{$safeExtension}";

        return $file->storeAs('payments', $fileName, 'public');
    }

    public function storeAvatar(UploadedFile $file): string
    {
        return $file->store('avatars', 'public');
    }

    private function sanitizeExtension(string $extension): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', strtolower($extension));
    }
}
```

**File baru:** `app/Services/ResponseService.php`

```php
<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResponseService
{
    public function success(Request $request, string $message, string $redirect): JsonResponse|RedirectResponse
    {
        if ($this->isJsonRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirect,
            ]);
        }

        return redirect($redirect)->with('success', $message);
    }

    public function error(Request $request, string $message, int $status = 500): JsonResponse|RedirectResponse
    {
        if ($this->isJsonRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->with('error', $message);
    }

    private function isJsonRequest(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }
}
```

**Estimasi:** 1 jam

---

## Fase D: DRY — Eliminate Duplication

**Estimasi:** 2-3 jam
**Prinsip:** *"Every piece of knowledge must have a single, unambiguous, authoritative representation."*

---

### D.1 Hapus `calculateAverageScore()` Duplikat

**Masalah:** Method identik ada di `Registration.php` DAN `Inputnilai.php` (yang sudah dihapus).

**SESUDAH — Cukup satu tempat di `ScoringService`:**

```php
// app/Services/ScoringService.php
public function calculateAverage(int $listening, int $structure, int $reading): float
{
    return round(($listening + $structure + $reading) / 3, 2);
}

// Registration.php — TIDAK perlu method ini lagi
// Cukup akses langsung:
$registration->average_score  // Sudah ada di database
```

**Estimasi:** 30 menit

---

### D.2 Consolidate Notification Dispatch — Event-Based

**SEBELUM — Notification dikirim di banyak tempat:**

```php
// RegistrationService.php
$registration->user->notify(new RegistrationSuccessNotification($registration));

// RegistrationResource.php (inline action)
$record->user->notify(new PaymentVerifiedNotification($record));
$record->user->notify(new PaymentRejectedNotification($record));

// RegistrationController.php
// Tidak konsisten — ada yang kirim, ada yang tidak
```

**SESUDAH — Satu tempat via Event Listener:**

```php
// app/Listeners/SendRegistrationNotification.php
class SendRegistrationNotification
{
    public function handle(RegistrationStatusChanged $event): void
    {
        $registration = $event->registration->load(['user', 'examSchedule']);

        match ($event->newStatus) {
            RegistrationStatus::PENDING_PAYMENT => $registration->user->notify(
                new RegistrationSuccessNotification($registration)
            ),
            RegistrationStatus::VERIFIED => $registration->user->notify(
                new PaymentVerifiedNotification($registration)
            ),
            RegistrationStatus::REJECTED => $registration->user->notify(
                new PaymentRejectedNotification($registration)
            ),
            RegistrationStatus::EXPIRED => $registration->user->notify(
                new RegistrationExpiredNotification($registration)
            ),
            default => null,
        };
    }
}

// SEKARANG — Cukup dispatch event, notification otomatis terkirim:
// PaymentVerificationService.php
event(new RegistrationStatusChanged($registration, $oldStatus, RegistrationStatus::VERIFIED));

// Tidak perlu $registration->user->notify() di mana-mana lagi!
```

**Estimasi:** 1 jam

---

### D.3 Consolidate Repeated Filament Filter Definitions

**File baru:** `app/Filament/Filters.php`

```php
<?php

namespace App\Filament;

use App\Enums\RegistrationStatus;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;

class Filters
{
    public static function statusFilter(): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Status')
            ->options(RegistrationStatus::options())
            ->native(false);
    }

    public static function examScheduleFilter(): SelectFilter
    {
        return SelectFilter::make('exam_schedule_id')
            ->label('Jadwal Ujian')
            ->relationship('examSchedule', 'title')
            ->searchable()
            ->preload()
            ->native(false);
    }

    public static function isActiveFilter(): TernaryFilter
    {
        return TernaryFilter::make('is_active')
            ->label('Status Aktif')
            ->placeholder('Semua')
            ->trueLabel('Aktif')
            ->falseLabel('Tidak Aktif');
    }

    public static function examDateRangeFilter(): Filter
    {
        return Filter::make('exam_date')
            ->label('Rentang Tanggal')
            ->form([
                Forms\Components\DatePicker::make('from')
                    ->label('Dari Tanggal')
                    ->native(false),
                Forms\Components\DatePicker::make('until')
                    ->label('Sampai Tanggal')
                    ->native(false),
            ])
            ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                return $query
                    ->when(
                        $data['from'],
                        fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder =>
                            $query->whereDate('exam_date', '>=', $date)
                    )
                    ->when(
                        $data['until'],
                        fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder =>
                            $query->whereDate('exam_date', '<=', $date)
                    );
            });
    }
}
```

**Estimasi:** 1 jam

---

## Fase E: Type Safety & Strictness

**Estimasi:** 2-3 jam
**Prinsip:** *"Be explicit. Use type declarations everywhere."*

---

### E.1 Tambah Return Types di Semua Methods

**SEBELUM:**

```php
// RegistrationController.php
public function index()           // Missing return type
public function store(StoreRegistrationRequest $request)  // Missing
public function show(Registration $registration)          // Missing
```

**SESUDAH:**

```php
<?php

namespace App\Http\Controllers\Mahasiswa;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(): View
    {
        // ...
    }

    public function create(string $schedule_id): View|RedirectResponse
    {
        // ...
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        // ...
    }

    public function show(Registration $registration): View
    {
        // ...
    }

    public function uploadPayment(Registration $registration): View
    {
        // ...
    }

    public function storePayment(StorePaymentRequest $request, Registration $registration): JsonResponse|RedirectResponse
    {
        // ...
    }

    public function cancel(CancelRegistrationRequest $request, Registration $registration): RedirectResponse
    {
        // ...
    }

    public function card(Registration $registration): \Illuminate\Http\Response
    {
        // ...
    }
}
```

**Estimasi:** 1 jam

---

### E.2 Tambah Parameter Types

**SEBELUM:**

```php
// RegistrationService.php
private function sendNotification(Registration $registration, User $user): void
{
    // Method private, tapi parameter type sudah OK
}
```

**Pastikan semua parameter typed:**

```php
// FileStorageService.php
public function storePayment(UploadedFile $file): string
public function storeAvatar(UploadedFile $file): string

// ResponseService.php
public function success(Request $request, string $message, string $redirect): JsonResponse|RedirectResponse
public function error(Request $request, string $message, int $status = 500): JsonResponse|RedirectResponse

// ScoringService.php
public function inputScores(
    Registration $registration,
    int $listeningScore,
    int $structureScore,
    int $readingScore,
    User $gradedBy
): Registration

public function calculateAverage(int $listening, int $structure, int $reading): float
```

**Estimasi:** 1 jam

---

### E.3 Gunakan Nullable Type Correctly

**SEBELUM:**

```php
// Registration.php
public function getTotalPaymentAttribute(): int
{
    $price = $this->examSchedule->price ?? 0;
    return $price + ($this->unique_code ?? 0);
}
```

**SESUDAH — Lebih defensive:**

```php
public function getTotalPaymentAttribute(): int
{
    $price = $this->examSchedule->price ?? 0;
    $uniqueCode = $this->unique_code ?? 0;

    return (int) ($price + $uniqueCode);
}

// Tambahkan null safety di tempat lain:
public function isExpired(): bool
{
    return $this->expires_at instanceof \Carbon\Carbon
        && $this->expires_at->isPast();
}
```

**Estimasi:** 30 menit

---

## Fase F: Error Handling Consistency

**Estimasi:** 2-3 jam
**Prinsip:** *"Be consistent. Use exceptions, not error codes."*

---

### F.1 Buat Custom Exceptions

**File baru:** `app/Exceptions/RegistrationException.php`

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class RegistrationException extends RuntimeException
{
    public static function quotaFull(): self
    {
        return new self('Kuota untuk jadwal ini sudah penuh.');
    }

    public static function alreadyRegistered(): self
    {
        return new self('Anda sudah memiliki pendaftaran aktif.');
    }

    public static function scheduleUnavailable(): self
    {
        return new self('Jadwal ini tidak tersedia untuk pendaftaran.');
    }

    public static function paymentExpired(): self
    {
        return new self('Batas waktu pembayaran telah habis.');
    }

    public static function cannotBeCancelled(): self
    {
        return new self('Pendaftaran tidak dapat dibatalkan.');
    }

    public static function invalidPaymentStatus(): self
    {
        return new self('Pembayaran sudah dilakukan atau status tidak valid.');
    }

    public static function examCardNotAvailable(): self
    {
        return new self('Kartu ujian hanya tersedia untuk pendaftaran yang telah terverifikasi.');
    }
}
```

**File baru:** `app/Exceptions/ScoringException.php`

```php
<?php

namespace App\Exceptions;

use InvalidArgumentException;

class ScoringException extends InvalidArgumentException
{
    public static function scoreOutOfRange(string $component, int $min, int $max): self
    {
        return new self("Score {$component} must be between {$min} and {$max}.");
    }

    public static function notReadyForScoring(): self
    {
        return new self('Registration is not ready for scoring.');
    }
}
```

**Update semua file untuk gunakan Custom Exceptions:**

```php
// RegistrationService.php
public function createRegistration(User $user, ExamSchedule $schedule): Registration
{
    return DB::transaction(function () use ($user, $schedule) {
        $lockedSchedule = ExamSchedule::where('id', $schedule->id)
            ->lockForUpdate()
            ->first();

        if ($lockedSchedule->availableQuota() <= 0) {
            throw RegistrationException::quotaFull();  // ← Custom exception
        }

        // ...
    });
}

// RegistrationController.php
public function card(Registration $registration): \Illuminate\Http\Response
{
    $this->authorize('viewCard', $registration);

    if ($registration->status !== RegistrationStatus::VERIFIED) {
        throw RegistrationException::examCardNotAvailable();
    }

    // ...
}
```

**Estimasi:** 2 jam

---

### F.2 Consistent Try-Catch Pattern

**SEBELUM — Inconsistent:**

```php
// storePayment() — ada try-catch
// show() — tidak ada try-catch
// cancel() — ada try-catch
// card() — tidak ada try-catch
```

**SESUDAH — Consistent:**

```php
// Gunakan Exception Handler untuk handle semua exceptions
// app/Exceptions/Handler.php (Laravel 11+)

// Atau gunakan middleware untuk handle specific exceptions
// app/Http/Middleware/HandleRegistrationExceptions.php

// Yang lebih baik: biarkan exceptions propagate ke Laravel exception handler
// dan handle di sana:

// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->renderable(function (RegistrationException $e, $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return redirect()->back()->with('error', $e->getMessage());
    });

    $exceptions->renderable(function (ScoringException $e, $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return redirect()->back()->with('error', $e->getMessage());
    });
})
```

**Estimasi:** 1 jam

---

## Fase G: Code Organization

**Estimasi:** 3-4 jam
**Prinsip:** *"Group related code. Follow Laravel conventions."*

---

### G.1 Feature-Based Directory Structure

**SEBELUM (Flat):**

```
app/
├── Console/Commands/
├── Enums/
├── Filament/
├── Http/
├── Models/
├── Notifications/
├── Policies/
├── Services/
```

**SESUDAH (Feature-Based):**

```
app/
├── Features/
│   ├── Auth/
│   │   ├── Http/Controllers/Auth/
│   │   └── Http/Requests/Auth/
│   ├── Registration/
│   │   ├── Models/Registration.php
│   │   ├── Services/
│   │   │   ├── RegistrationService.php
│   │   │   ├── PaymentVerificationService.php
│   │   │   └── FileStorageService.php
│   │   ├── Events/RegistrationStatusChanged.php
│   │   ├── Listeners/
│   │   │   ├── SendRegistrationNotification.php
│   │   │   └── LogRegistrationActivity.php
│   │   ├── Notifications/
│   │   │   ├── RegistrationSuccessNotification.php
│   │   │   ├── PaymentVerifiedNotification.php
│   │   │   ├── PaymentRejectedNotification.php
│   │   │   ├── RegistrationExpiredNotification.php
│   │   │   └── PaymentReminderNotification.php
│   │   ├── Policies/RegistrationPolicy.php
│   │   ├── Exceptions/RegistrationException.php
│   │   └── Filament/
│   │       ├── Resources/RegistrationResource.php
│   │       ├── Actions/VerifyPaymentAction.php
│   │       ├── Actions/RejectPaymentAction.php
│   │       ├── Columns.php
│   │       └── Filters.php
│   ├── ExamSchedule/
│   │   ├── Models/ExamSchedule.php
│   │   ├── Policies/ExamSchedulePolicy.php
│   │   └── Filament/Resources/ExamScheduleResource.php
│   ├── Scoring/
│   │   ├── Models/Inputnilai.php (DIHAPUS)
│   │   ├── Services/ScoringService.php
│   │   ├── Exceptions/ScoringException.php
│   │   └── Filament/Resources/ScoringResource.php
│   ├── User/
│   │   ├── Models/User.php
│   │   ├── Policies/UserPolicy.php
│   │   └── Filament/Resources/UserResource.php
│   ├── MasterData/
│   │   ├── Models/Bank.php
│   │   ├── Models/Faculty.php
│   │   ├── Models/Major.php
│   │   ├── Models/ExamSession.php
│   │   └── Filament/Resources/
│   ├── Dashboard/
│   │   ├── Filament/Pages/Dashboard.php
│   │   ├── Filament/Widgets/
│   │   │   ├── StatsOverviewWidget.php
│   │   │   ├── RegistrationChartWidget.php
│   │   │   └── ExamScheduleQuotaWidget.php
│   │   └── Http/Controllers/DashboardController.php
│   └── Announcements/
│       ├── Models/Announcement.php
│       ├── Notifications/AnnouncementNotification.php
│       └── Filament/Resources/AnnouncementResource.php
├── Shared/
│   ├── Enums/RegistrationStatus.php
│   ├── Constants/AppConstants.php
│   ├── Services/ResponseService.php
│   └── Exceptions/Handler.php
└── Console/
    ├── Commands/CheckExpiredRegistrations.php
    ├── Commands/EnableExamScoring.php
    └── Commands/SendPaymentReminders.php
```

**Catatan:** Ini adalah rekomendasi struktur ideal. Implementasi bertahap — bisa mulai dengan refactor yang paling krusial dulu.

**Estimasi:** 3 jam

---

## Fase H: Dead Code Removal

**Estimasi:** 1-2 jam
**Prinsip:** *"Dead code is the biggest source of technical debt."*

---

### H.1 Daftar Dead Code yang Harus Dihapus

| # | File | Item | Alasan |
|---|------|------|--------|
| 1 | `app/Models/Inputnilai.php` | Seluruh file | Redundant, sudah dikonsolidasi |
| 2 | `database/migrations/2026_02_27_020312_create_inputnilais_table.php` | Seluruh file | Tabel dihapus |
| 3 | `Registration.php` | `getStatusColorAttribute()` | Pindah ke Enum |
| 4 | `Registration.php` | `getStatusLabelAttribute()` | Pindah ke Enum |
| 5 | `Registration.php` | `getHistoryNoteAttribute()` | Pindah ke helper/view |
| 6 | `Registration.php` | `calculateAverageScore()` | Pindah ke ScoringService |
| 7 | `Inputnilai.php` | `calculateAverageScore()` | Duplikat |
| 8 | `ExamSchedule.php` | `getSessionLabelAttribute()` | Pindah ke Enum/Helper |
| 9 | `Participants.php` | `exit` calls | Refactor ke proper response |
| 10 | `RegistrationController.php` | `$registration` param di `storePaymentFile()` | Tidak digunakan |

---

### H.2 Remove Unused Imports

**Check semua file untuk unused imports:**

```php
// SEBELUM:
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// ... beberapa mungkin tidak terpakai

// SESUDAH — Hapus semua unused imports
```

**Buat script untuk cek:**

```bash
# Jalankan Pint untuk auto-fix
./vendor/bin/pint --test

# Atau gunakan PHPStan
./vendor/bin/phpstan analyse --level=5 app/
```

**Estimasi:** 1 jam

---

### H.3 Remove Commented-Out Code

**Check dan hapus semua commented-out code:**

```php
// SEBELUM:
// public function someOldMethod()
// {
//     // old implementation
// }

// $oldVariable = 'something';
// if ($oldCondition) {
//     // old logic
// }

// SESUDAH — Hapus semua, gunakan git history untuk recover jika perlu
```

**Estimasi:** 30 menit

---

## Ringkasan Transformasi

### File yang Diubah

| # | File | Perubahan Utama |
|---|------|-----------------|
| 1 | `app/Models/Registration.php` | Hapus UI logic, tambah scopes, typed |
| 2 | `app/Models/ExamSchedule.php` | Fix time casting, simplify |
| 3 | `app/Models/User.php` | Fix scope casing |
| 4 | `app/Enums/RegistrationStatus.php` | Tambah label(), color(), tailwindClasses() |
| 5 | `app/Filament/Resources/RegistrationResource.php` | Extract columns, actions, filters |
| 6 | `app/Filament/Resources/InputnilaiResource.php` | Rename ke ScoringResource |
| 7 | `app/Filament/Pages/Participants.php` | Hapus exit, simplify export |
| 8 | `app/Http/Controllers/Mahasiswa/RegistrationController.php` | Delegate ke services |
| 9 | `app/Services/RegistrationService.php` | Gunakan custom exceptions |
| 10 | `app/Providers/AppServiceProvider.php` | Register event listeners |

### File yang Dibuat

| # | File | Deskripsi |
|---|------|-----------|
| 1 | `app/Services/ScoringService.php` | Business logic input nilai |
| 2 | `app/Services/PaymentVerificationService.php` | Verify & reject logic |
| 3 | `app/Services/FileStorageService.php` | File upload logic |
| 4 | `app/Services/ResponseService.php` | JSON/redirect response |
| 5 | `app/Filament/Actions/VerifyPaymentAction.php` | Reusable verify action |
| 6 | `app/Filament/Actions/RejectPaymentAction.php` | Reusable reject action |
| 7 | `app/Filament/Columns.php` | Reusable column definitions |
| 8 | `app/Filament/Filters.php` | Reusable filter definitions |
| 9 | `app/Events/RegistrationStatusChanged.php` | Event state change |
| 10 | `app/Listeners/SendRegistrationNotification.php` | Handle notifications |
| 11 | `app/Listeners/LogRegistrationActivity.php` | Activity log |
| 12 | `app/Exceptions/RegistrationException.php` | Custom exceptions |
| 13 | `app/Exceptions/ScoringException.php` | Custom exceptions |
| 14 | `app/Constants/AppConstants.php` | Magic numbers → constants |
| 15 | `app/Exports/ParticipantsExport.php` | Excel export |
| 16 | `app/Filament/Resources/ScoringResource.php` | Renamed from InputnilaiResource |
| 17 | `app/Filament/Resources/ScoringResource/Pages/ListScorings.php` | Renamed page |

### File yang Dihapus

| # | File | Alasan |
|---|------|--------|
| 1 | `app/Models/Inputnilai.php` | Redundant |
| 2 | `database/migrations/2026_02_27_020312_create_inputnilais_table.php` | Tabel dihapus |
| 3 | `app/Filament/Resources/InputnilaiResource.php` | Renamed |
| 4 | `app/Filament/Resources/InputnilaiResource/Pages/ListInputnilais.php` | Renamed |

---

## Before/After Comparison

| Aspek | SEBELUM | SESUDAH | Improvement |
|-------|---------|---------|-------------|
| **Model Lines** | Registration: 160+ baris | Registration: 120 baris | -25% |
| **Resource Lines** | RegistrationResource: 200+ baris | RegistrationResource: 80 baris | -60% |
| **Duplication** | 3x calculateAverageScore() | 1x di ScoringService | -66% |
| **Magic Numbers** | 10+ | 0 (semua di Constants) | -100% |
| **Error Handling** | Inconsistent try-catch | Custom Exceptions + Handler | Consistent |
| **Type Safety** | 60% typed | 100% typed | +40% |
| **Naming** | Mixed ID/EN | All English, consistent | Consistent |
| **UI in Model** | CSS classes, labels | Hanya data & business | Separated |
| **Testability** | Hard to mock | Services are injectable | Easy to test |
| **Code Duplication** | Multiple query patterns | Shared scopes & columns | DRY |

---

## Checklist

### Fase A: Naming & Identifiers
- [ ] A.1 Rename `Inputnilai` → `Scoring` (model, migration, resource)
- [ ] A.2 Hapus UI logic dari Model (`getStatusColorAttribute`, `getStatusLabelAttribute`)
- [ ] A.3 Extract magic numbers ke `AppConstants`

### Fase B: Separation of Concerns
- [ ] B.1 Buat `ScoringService`
- [ ] B.2 Buat `PaymentVerificationService`
- [ ] B.3 Buat `VerifyPaymentAction` & `RejectPaymentAction`
- [ ] B.4 Pindahkan query logic ke Query Scopes
- [ ] B.5 Pindahkan export logic ke `ParticipantsExport`

### Fase C: Function & Method Refactoring
- [ ] C.1 Break down `RegistrationResource::table()` ke smaller methods
- [ ] C.2 Buat `Columns.php` & `Filters.php` reusable
- [ ] C.3 Simplify `Registration` model — pindahkan ke tempat yang tepat
- [ ] C.4 Extract `RegistrationController` ke services

### Fase D: DRY
- [ ] D.1 Hapus `calculateAverageScore()` duplikat
- [ ] D.2 Consolidate notification dispatch via events
- [ ] D.3 Consolidate repeated Filament filter definitions

### Fase E: Type Safety
- [ ] E.1 Tambah return types di semua methods
- [ ] E.2 Tambah parameter types di semua methods
- [ ] E.3 Gunakan nullable type correctly

### Fase F: Error Handling
- [ ] F.1 Buat custom exceptions (`RegistrationException`, `ScoringException`)
- [ ] F.2 Consistent try-catch pattern via Exception Handler

### Fase G: Code Organization
- [ ] G.1 Feature-based directory structure (optional, gradual)

### Fase H: Dead Code Removal
- [ ] H.1 Hapus semua dead code yang terdaftar
- [ ] H.2 Hapus unused imports
- [ ] H.3 Hapus commented-out code

---

**Total Estimasi:** 25-30 jam
**Target Clean Code Score:** 8.5/10
**Status:** Siap Implementasi
