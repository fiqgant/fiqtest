<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptQuestion;

class GradingService
{
    public function gradeQuestion(AttemptQuestion $attemptQuestion): void
    {
        $question = $attemptQuestion->question;

        match ($question->type) {
            'coding'          => $this->gradeCoding($attemptQuestion),
            'multiple_choice' => $this->gradeMultipleChoice($attemptQuestion),
            'multiple_select' => $this->gradeMultipleSelect($attemptQuestion),
            'true_false'      => $this->gradeTrueFalse($attemptQuestion),
            'fill_in_blank'   => $this->gradeFillInBlank($attemptQuestion),
            'essay'           => null, // Manual grading — leave score unchanged
            default           => null,
        };
    }

    public function updateAttemptTotal(Attempt $attempt): void
    {
        // For total score: sum effective scores (manual_score for essay, score for others)
        $attemptQuestions = $attempt->attemptQuestions()->with('question')->get();
        $totalScore = $attemptQuestions->sum(fn($aq) => $aq->effectiveScore());
        $maxScore   = $attempt->attemptQuestions()->sum('weight');

        $attempt->update([
            'total_score' => $totalScore,
            'max_score'   => $maxScore,
            'status'      => 'graded',
        ]);
    }

    public function gradeAllQuestions(Attempt $attempt): void
    {
        $attempt->load('attemptQuestions.question');

        foreach ($attempt->attemptQuestions as $attemptQuestion) {
            $this->gradeQuestion($attemptQuestion);
        }

        $this->updateAttemptTotal($attempt);
    }

    // ── Coding ────────────────────────────────────────────────────────

    private function gradeCoding(AttemptQuestion $aq): void
    {
        $question  = $aq->question;
        $testCases = is_array($question->test_cases) ? $question->test_cases : [];
        $judge0    = app(Judge0Service::class);

        $passedTests = 0;
        $totalTests  = count($testCases);

        foreach ($testCases as $tc) {
            $input  = is_array($tc['input']) ? $tc['input'] : explode("\n", $tc['input'] ?? '');
            $result = $judge0->execute($aq->code, $question->language, $input);

            if (! $result->isSuccess()) {
                continue;
            }

            $expected = trim($tc['expected_output'] ?? '');
            $actual   = trim($result->output ?? '');

            if ($expected === $actual) {
                $passedTests++;
            }
        }

        $isCorrect = $passedTests === $totalTests && $totalTests > 0;
        $score     = $totalTests > 0
            ? round(((float) $aq->weight * $passedTests) / $totalTests, 2)
            : 0.0;

        $aq->update([
            'score'        => $score,
            'is_correct'   => $isCorrect,
            'passed_tests' => $passedTests,
            'total_tests'  => $totalTests,
        ]);
    }

    // ── Multiple Choice ───────────────────────────────────────────────

    private function gradeMultipleChoice(AttemptQuestion $aq): void
    {
        $studentAnswer = trim((string) ($aq->student_answer ?? ''));

        if ($studentAnswer === '') {
            $aq->update(['score' => 0, 'is_correct' => false]);
            return;
        }

        $correctOption = $aq->question->options()->where('is_correct', true)->first();
        $isCorrect     = $correctOption && (string) $correctOption->id === $studentAnswer;

        $aq->update([
            'score'      => $isCorrect ? (float) $aq->weight : 0.0,
            'is_correct' => $isCorrect,
        ]);
    }

    // ── Multiple Select ───────────────────────────────────────────────

    private function gradeMultipleSelect(AttemptQuestion $aq): void
    {
        $studentAnswer = trim((string) ($aq->student_answer ?? ''));

        if ($studentAnswer === '') {
            $aq->update(['score' => 0, 'is_correct' => false]);
            return;
        }

        // Cast to int for consistent numeric comparison (avoids '9' vs '10' lexicographic mismatch)
        $selectedIds = array_values(array_unique(array_filter(
            array_map('intval', explode(',', $studentAnswer))
        )));
        sort($selectedIds, SORT_NUMERIC);

        $correctIds = $aq->question->options()
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->sort()
            ->values()
            ->toArray();

        $allOptions   = $aq->question->options()->get();
        $totalCorrect = $allOptions->where('is_correct', true)->count();
        $totalWrong   = $allOptions->where('is_correct', false)->count();

        $correctSelected = count(array_intersect($selectedIds, $correctIds));
        $wrongSelected   = count(array_diff($selectedIds, $correctIds));

        $isCorrect = $selectedIds === $correctIds;

        // Partial credit: subtract wrong-selection penalty
        $rawScore = $totalCorrect > 0
            ? (($correctSelected / $totalCorrect) - ($totalWrong > 0 ? $wrongSelected / $totalWrong : 0))
            : 0.0;

        $score = max(0.0, round($rawScore * (float) $aq->weight, 2));

        $aq->update([
            'score'      => $score,
            'is_correct' => $isCorrect,
        ]);
    }

    // ── True / False ──────────────────────────────────────────────────

    private function gradeTrueFalse(AttemptQuestion $aq): void
    {
        $studentAnswer = trim((string) ($aq->student_answer ?? ''));

        if ($studentAnswer === '') {
            $aq->update(['score' => 0, 'is_correct' => false]);
            return;
        }

        $correctAnswer = $aq->question->true_false_answer; // boolean
        $studentBool   = $studentAnswer === '1';
        $isCorrect     = $correctAnswer === $studentBool;

        $aq->update([
            'score'      => $isCorrect ? (float) $aq->weight : 0.0,
            'is_correct' => $isCorrect,
        ]);
    }

    // ── Fill in the Blank ─────────────────────────────────────────────

    private function gradeFillInBlank(AttemptQuestion $aq): void
    {
        $studentAnswer  = trim((string) ($aq->student_answer ?? ''));
        $correctAnswer  = trim((string) ($aq->question->fill_blank_answer ?? ''));

        if ($studentAnswer === '' || $correctAnswer === '') {
            $aq->update(['score' => 0, 'is_correct' => false]);
            return;
        }

        $isCorrect = mb_strtolower($studentAnswer) === mb_strtolower($correctAnswer);

        $aq->update([
            'score'      => $isCorrect ? (float) $aq->weight : 0.0,
            'is_correct' => $isCorrect,
        ]);
    }
}
