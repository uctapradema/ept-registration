<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inputnilai extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'listening_score',
        'structure_score',
        'reading_score',
        'average_score',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'graded_at' => 'datetime',
            'listening_score' => 'integer',
            'structure_score' => 'integer',
            'reading_score' => 'integer',
            'average_score' => 'decimal:2',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function calculateAverageScore(): ?float
    {
        if ($this->listening_score !== null && $this->structure_score !== null && $this->reading_score !== null) {
            return round(($this->listening_score + $this->structure_score + $this->reading_score) / 3, 2);
        }
        return null;
    }
}
