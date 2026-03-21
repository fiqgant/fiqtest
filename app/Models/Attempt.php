<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'submitted_at',
        'status',
        'tab_switch_count',
        'last_activity_at',
        'is_disqualified',
        'disqualified_at',
        'disqualification_reason',
        'total_score',
        'max_score',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'disqualified_at' => 'datetime',
        'is_disqualified' => 'boolean',
        'tab_switch_count' => 'integer',
        'total_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(AttemptQuestion::class);
    }

    public function copyPasteLogs(): HasMany
    {
        return $this->hasMany(CopyPasteLog::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status !== 'in_progress';
    }

    public function getRemainingSeconds(): int
    {
        $durationExpiresAt = $this->started_at->copy()->addMinutes($this->exam->duration_minutes);
        $windowExpiresAt = $this->exam->closes_at;
        $effectiveExpiresAt = $durationExpiresAt->lessThan($windowExpiresAt) ? $durationExpiresAt : $windowExpiresAt;

        $remaining = now()->diffInSeconds($effectiveExpiresAt, false);
        return max(0, $remaining);
    }

    public function getPercentageAttribute(): ?float
    {
        if (!$this->max_score || $this->max_score == 0) {
            return null;
        }
        return round(($this->total_score / $this->max_score) * 100, 2);
    }
}
