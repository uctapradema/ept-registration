<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'payment_uploaded_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'expires_at' => 'datetime',
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

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            'pending_payment',
            'awaiting_verification',
            'verified',
        ]);
    }

    public function getTotalPaymentAttribute(): int
    {
        $price = $this->examSchedule->price ?? 0;
        return $price + ($this->unique_code ?? 0);
    }
}
