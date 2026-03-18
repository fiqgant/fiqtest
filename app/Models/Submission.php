<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'attempt_question_id',
        'code',
        'output',
        'error',
        'execution_time_ms',
        'status',
        'is_final',
    ];

    protected $casts = [
        'is_final' => 'boolean',
    ];

    public function attemptQuestion(): BelongsTo
    {
        return $this->belongsTo(AttemptQuestion::class);
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isError(): bool
    {
        return in_array($this->status, ['error', 'timeout']);
    }
}
