<?php

namespace App\Models;

use App\Constants\AppConstants;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSchedule($query, int $scheduleId)
    {
        return $query->where('exam_schedule_id', $scheduleId);
    }

    public function scopeAwaitingVerification($query)
    {
        return $query->where('status', RegistrationStatus::AWAITING_VERIFICATION->value);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', RegistrationStatus::VERIFIED->value);
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

    public static function generateRegistrationNumber(ExamSchedule $schedule): string
    {
        $session = $schedule->session ?? '01';
        $count = self::where('exam_schedule_id', $schedule->id)->count() + 1;
        
        return 'EPT/' . $session . '/' . $schedule->exam_date->format('dmY') . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public static function generateUniqueCode(ExamSchedule $schedule): int
    {
        $min = $schedule->unique_code_min ?? AppConstants::DEFAULT_UNIQUE_CODE_MIN;
        $max = $schedule->unique_code_max ?? AppConstants::DEFAULT_UNIQUE_CODE_MAX;
        
        return rand($min, $max);
    }
}
