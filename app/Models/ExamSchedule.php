<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'session',
        'exam_date',
        'start_time',
        'end_time',
        'quota',
        'registered_count',
        'registration_deadline',
        'payment_deadline_hours',
        'price',
        'bank_name',
        'bank_account',
        'account_holder',
        'description',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'registration_deadline' => 'datetime',
            'payment_deadline_hours' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'registered_count' => 'integer',
            'quota' => 'integer',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function verifiedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)->where('status', 'verified');
    }

    public function registeredCount(): int
    {
        return $this->registrations()
            ->whereIn('status', ['pending_payment', 'awaiting_verification', 'verified'])
            ->count();
    }

    public function availableQuota(): int
    {
        return $this->quota - $this->registeredCount();
    }

    public static function getSessionOptions(): array
    {
        return [
            '01' => '01 - Pagi (09:00 - 11:00)',
            '02' => '02 - Siang (13:00 - 15:00)',
            '03' => '03 - Sore (15:30 - 17:30)',
        ];
    }

    public static function getSessionTimes(string $session): array
    {
        return match($session) {
            '01' => ['start' => '09:00', 'end' => '11:00'],
            '02' => ['start' => '13:00', 'end' => '15:00'],
            '03' => ['start' => '15:30', 'end' => '17:30'],
            default => ['start' => '09:00', 'end' => '11:00'],
        };
    }

    public function getSessionLabelAttribute(): string
    {
        return match($this->session) {
            '01' => 'Pagi',
            '02' => 'Siang',
            '03' => 'Sore',
            default => '-',
        };
    }

    public function isAvailable(): bool
    {
        return $this->is_active
            && $this->registration_deadline->isFuture()
            && $this->availableQuota() > 0;
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'closed';
        }
        if ($this->registration_deadline->isPast()) {
            return 'closed';
        }
        $available = $this->availableQuota();
        if ($available <= 0) {
            return 'full';
        }
        if ($available <= 10) {
            return 'limited';
        }
        return 'available';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'full' => 'Penuh',
            'limited' => 'Terbatas',
            'closed' => 'Ditutup',
            default => 'Tersedia',
        };
    }
}
