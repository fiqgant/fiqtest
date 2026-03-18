<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;

class ExamAccessService
{
    public function canAccessExam(Exam $exam, Student $student): array
    {
        if ($exam->status !== 'published') {
            return [false, 'Exam is not published yet.'];
        }

        $now = now();
        if ($now->lt($exam->opens_at)) {
            return [false, 'Exam has not opened yet. Opens at: ' . $exam->opens_at->format('Y-m-d H:i')];
        }

        if ($now->gt($exam->closes_at)) {
            return [false, 'Exam has already closed. Closed at: ' . $exam->closes_at->format('Y-m-d H:i')];
        }

        $existingAttempt = Attempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttempt && $existingAttempt->status !== 'in_progress') {
            return [false, 'You have already submitted this exam.'];
        }

        $filterTags = $exam->question_filter_tags ?? [];

        $counts = collect(['easy', 'medium', 'hard'])->mapWithKeys(function (string $diff) use ($exam, $filterTags) {
            $query = Question::where('course_offering_id', $exam->course_offering_id)
                ->where('difficulty', $diff);

            if (!empty($filterTags)) {
                $query->whereHas('tags', fn($q) => $q->whereIn('question_tags.id', $filterTags));
            }

            return [$diff => $query->count()];
        });

        $easyAvailable = (int) ($counts['easy'] ?? 0);
        $mediumAvailable = (int) ($counts['medium'] ?? 0);
        $hardAvailable = (int) ($counts['hard'] ?? 0);

        if ($easyAvailable < $exam->easy_count || $mediumAvailable < $exam->medium_count || $hardAvailable < $exam->hard_count) {
            return [false, 'Exam is not ready because the question bank distribution is insufficient.'];
        }

        return [true, null];
    }

    public function createAttempt(Exam $exam, Student $student): Attempt
    {
        $existingAttempt = Attempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return $existingAttempt;
        }

        $attempt = Attempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'tab_switch_count' => 0,
            'last_activity_at' => now(),
            'is_disqualified' => false,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $questionAssigner = app(QuestionAssigner::class);
        $questionAssigner->assign($attempt);

        return $attempt;
    }
}
