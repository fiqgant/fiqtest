<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttemptQuestion extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'weight',
        'max_score',
        'code',
        'student_answer',
        'score',
        'manual_score',
        'manual_feedback',
        'is_correct',
        'passed_tests',
        'total_tests',
        'hint_used_count',
    ];

    protected $casts = [
        'weight'       => 'decimal:2',
        'max_score'    => 'decimal:2',
        'score'        => 'decimal:2',
        'manual_score' => 'decimal:2',
        'is_correct'   => 'boolean',
        'passed_tests' => 'integer',
        'total_tests'  => 'integer',
        'hint_used_count' => 'integer',
    ];

    public function effectiveScore(): float
    {
        if ($this->question->isManualGraded()) {
            return (float) ($this->manual_score ?? 0);
        }
        return (float) ($this->score ?? 0);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function getLastSubmission(): ?Submission
    {
        return $this->submissions()->latest()->first();
    }
}
