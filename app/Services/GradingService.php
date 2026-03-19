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

        $selectedIds = array_filter(array_map('trim', explode(',', $studentAnswer)));
        sort($selectedIds);

        $correctIds = $aq->question->options()
            ->where('is_correct', true)
            ->pluck('id')
            ->map('strval')
            ->sort()
            ->values()
            ->toArray();

        $isCorrect = $selectedIds === $correctIds;

        $aq->update([
            'score'      => $isCorrect ? (float) $aq->weight : 0.0,
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
