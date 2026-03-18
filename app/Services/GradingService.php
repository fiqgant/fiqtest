<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptQuestion;

class GradingService
{
    public function gradeQuestion(AttemptQuestion $attemptQuestion): void
    {
        $question = $attemptQuestion->question;
        $testCases = is_array($question->test_cases) ? $question->test_cases : [];
        
        $code = $attemptQuestion->code;
        $language = $question->language;
        
        $judge0 = app(Judge0Service::class);
        
        $passedTests = 0;
        $totalTests = count($testCases);
        
        foreach ($testCases as $testCase) {
            $input = is_array($testCase['input']) 
                ? $testCase['input'] 
                : explode("\n", $testCase['input'] ?? '');
            
            $result = $judge0->execute($code, $language, $input);
            
            if (!$result->isSuccess()) {
                continue;
            }
            
            $expected = trim($testCase['expected_output'] ?? '');
            $actual = trim($result->output ?? '');
            
            if ($expected === $actual) {
                $passedTests++;
            }
        }

        $isCorrect = $passedTests === $totalTests && $totalTests > 0;
        $score = $totalTests > 0
            ? round(((float) $attemptQuestion->weight * $passedTests) / $totalTests, 2)
            : 0.0;

        $attemptQuestion->update([
            'score' => $score,
            'is_correct' => $isCorrect,
            'passed_tests' => $passedTests,
            'total_tests' => $totalTests,
        ]);
    }

    public function updateAttemptTotal(Attempt $attempt): void
    {
        $totalScore = $attempt->attemptQuestions()->sum('score');
        $maxScore = $attempt->attemptQuestions()->sum('weight');
        
        $attempt->update([
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'status' => 'graded',
        ]);
    }

    public function gradeAllQuestions(Attempt $attempt): void
    {
        foreach ($attempt->attemptQuestions as $attemptQuestion) {
            $this->gradeQuestion($attemptQuestion);
        }

        $this->updateAttemptTotal($attempt);
    }
}
