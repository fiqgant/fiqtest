<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\AttemptQuestion;
use Illuminate\Http\JsonResponse;

class HintController extends Controller
{
    public function use(Attempt $attempt, AttemptQuestion $attemptQuestion): JsonResponse
    {
        // Validate the attemptQuestion belongs to this attempt
        if ($attemptQuestion->attempt_id !== $attempt->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Attempt must be in progress
        if ($attempt->status !== 'in_progress') {
            return response()->json(['error' => 'Exam already submitted.'], 403);
        }

        $exam = $attempt->exam;

        // Hints must be enabled for this exam
        if (!$exam->hints_enabled) {
            return response()->json(['error' => 'Hints are not enabled for this exam.'], 403);
        }

        $question = $attemptQuestion->question;

        // Question must have a hint
        if (empty($question->hint)) {
            return response()->json(['error' => 'No hint available for this question.'], 404);
        }

        $maxHints = (int) $exam->max_hints_per_question;
        $usedCount = (int) $attemptQuestion->hint_used_count;

        // Check limit (0 = unlimited)
        if ($maxHints > 0 && $usedCount >= $maxHints) {
            return response()->json([
                'error' => 'Hint limit reached.',
                'used' => $usedCount,
                'max' => $maxHints,
            ], 429);
        }

        // Increment counter and return hint
        $attemptQuestion->increment('hint_used_count');

        return response()->json([
            'hint' => $question->hint,
            'used' => $usedCount + 1,
            'max' => $maxHints,
        ]);
    }
}
