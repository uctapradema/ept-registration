<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const DEFAULT_PAYMENT_DEADLINE_HOURS = 24;
    public const MIN_CANCEL_REASON_LENGTH = 10;
    public const MAX_CANCEL_REASON_LENGTH = 500;
    public const DEFAULT_UNIQUE_CODE_MIN = 100;
    public const DEFAULT_UNIQUE_CODE_MAX = 999;

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

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeReadyForScoring($query)
    {
        return $query->where('ready_for_scoring', true)
            ->whereNull('graded_at');
    }

    public function scopeGraded($query)
    {
        return $query->whereNotNull('graded_at');
    }

    public function calculateAverageScore(): ?float
    {
        if ($this->listening_score !== null && $this->structure_score !== null && $this->reading_score !== null) {
            return round(($this->listening_score + $this->structure_score + $this->reading_score) / 3, 2);
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            RegistrationStatus::PENDING_PAYMENT->value,
            RegistrationStatus::AWAITING_VERIFICATION->value,
            RegistrationStatus::VERIFIED->value,
        ]);
    }

    public function scopeHistory($query)
    {
        return $query->whereIn('status', [
            RegistrationStatus::VERIFIED->value,
            RegistrationStatus::REJECTED->value,
            RegistrationStatus::CANCELLED->value,
            RegistrationStatus::EXPIRED->value,
        ]);
    }

    public function getTotalPaymentAttribute(): int
    {
        $price = $this->examSchedule->price ?? 0;
        return $price + ($this->unique_code ?? 0);
    }

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

    public function getHistoryNoteAttribute(): ?string
    {
        return match($this->status) {
            'verified' => 'Terverifikasi - Kartu ujian sudah tersedia',
            'rejected' => $this->rejection_reason,
            'expired' => 'Melebihi ' . ($this->examSchedule->payment_deadline_hours ?? self::DEFAULT_PAYMENT_DEADLINE_HOURS) . ' jam',
            'cancelled' => $this->rejection_reason,
            default => null,
        };
    }

    public static function generateRegistrationNumber(ExamSchedule $schedule): string
    {
        $session = $schedule->session ?? '01';
        $count = self::where('exam_schedule_id', $schedule->id)->count() + 1;
        
        return 'EPT/' . $session . '/' . $schedule->exam_date->format('dmY') . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public static function generateUniqueCode(ExamSchedule $schedule): int
    {
        $min = $schedule->unique_code_min ?? self::DEFAULT_UNIQUE_CODE_MIN;
        $max = $schedule->unique_code_max ?? self::DEFAULT_UNIQUE_CODE_MAX;
        
        return rand($min, $max);
    }
}
