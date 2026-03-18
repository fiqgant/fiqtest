<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'course_offering_id',
        'title',
        'slug',
        'difficulty',
        'description',
        'default_weight',
        'test_cases',
        'starter_code',
        'reference_solution',
        'hint',
        'language',
    ];

    protected $casts = [
        'test_cases' => 'array',
        'default_weight' => 'decimal:2',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(AttemptQuestion::class);
    }

    public function getVisibleTestCases(): array
    {
        return array_filter($this->test_cases, fn($tc) => !($tc['is_hidden'] ?? false));
    }

    public function getHiddenTestCases(): array
    {
        return array_filter($this->test_cases, fn($tc) => $tc['is_hidden'] ?? false);
    }
}
