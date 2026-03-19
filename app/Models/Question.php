<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'course_offering_id',
        'type',
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
        'fill_blank_answer',
        'true_false_answer',
    ];

    protected $casts = [
        'test_cases'        => 'array',
        'default_weight'    => 'decimal:2',
        'true_false_answer' => 'boolean',
    ];

    const TYPES = [
        'coding'          => 'Coding',
        'multiple_choice' => 'Multiple Choice',
        'multiple_select' => 'Multiple Select',
        'true_false'      => 'True / False',
        'fill_in_blank'   => 'Fill in the Blank',
        'essay'           => 'Essay',
    ];

    public function isCoding(): bool       { return $this->type === 'coding'; }
    public function isAutoGraded(): bool   { return in_array($this->type, ['coding', 'multiple_choice', 'multiple_select', 'true_false', 'fill_in_blank']); }
    public function isManualGraded(): bool { return $this->type === 'essay'; }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(QuestionTag::class, 'question_tag', 'question_id', 'question_tag_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
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
