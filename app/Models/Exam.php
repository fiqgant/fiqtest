<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Exam extends Model
{
    protected $fillable = [
        'course_offering_id',
        'title',
        'slug',
        'description',
        'opens_at',
        'closes_at',
        'duration_minutes',
        'status',
        'show_score_immediately',
        'hints_enabled',
        'max_hints_per_question',
        'max_tab_switches',
        'tab_switch_warning_count',
        'inactivity_limit_seconds',
        'inactivity_warning_seconds',
        'disable_inspect',
        'shuffle_options',
        'easy_count',
        'medium_count',
        'hard_count',
        'easy_weight',
        'medium_weight',
        'hard_weight',
        'question_filter_tags',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'show_score_immediately' => 'boolean',
        'hints_enabled' => 'boolean',
        'max_hints_per_question' => 'integer',
        'max_tab_switches' => 'integer',
        'tab_switch_warning_count' => 'integer',
        'inactivity_limit_seconds' => 'integer',
        'inactivity_warning_seconds' => 'integer',
        'disable_inspect'  => 'boolean',
        'shuffle_options'  => 'boolean',
        'question_filter_tags' => 'array',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function isOpen(): bool
    {
        $now = now();
        return $this->status === 'published' 
            && $now->greaterThanOrEqualTo($this->opens_at) 
            && $now->lessThanOrEqualTo($this->closes_at);
    }

    public function getTotalQuestionsNeeded(): int
    {
        return $this->easy_count + $this->medium_count + $this->hard_count;
    }

    public function getAvailableQuestionsCount(): int
    {
        $filterTags = $this->question_filter_tags ?? [];
        $query = Question::where('course_offering_id', $this->course_offering_id);

        if (!empty($filterTags)) {
            $query->whereHas('tags', fn($q) => $q->whereIn('question_tags.id', $filterTags));
        }

        return $query->count();
    }

    public function canPublish(): bool
    {
        $filterTags = $this->question_filter_tags ?? [];

        $counts = collect(['easy', 'medium', 'hard'])->mapWithKeys(function (string $diff) use ($filterTags) {
            $query = Question::where('course_offering_id', $this->course_offering_id)
                ->where('difficulty', $diff);

            if (!empty($filterTags)) {
                $query->whereHas('tags', fn($q) => $q->whereIn('question_tags.id', $filterTags));
            }

            return [$diff => $query->count()];
        });

        return (int) ($counts['easy'] ?? 0) >= $this->easy_count
            && (int) ($counts['medium'] ?? 0) >= $this->medium_count
            && (int) ($counts['hard'] ?? 0) >= $this->hard_count;
    }
}
