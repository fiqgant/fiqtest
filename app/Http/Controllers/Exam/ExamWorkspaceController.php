<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\AttemptQuestion;
use App\Models\Submission;
use App\Services\GradingService;
use App\Services\Judge0Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamWorkspaceController extends Controller
{
    public function workspace(Attempt $attempt)
    {
        $this->ensureAttemptActive($attempt);
        
        $exam = $attempt->exam;
        $attempt->load(['attemptQuestions.question', 'student']);
        
        $currentQuestionId = request()->query('question', $attempt->attemptQuestions->first()?->question_id);
        $currentAttemptQuestion = $attempt->attemptQuestions()
            ->where('question_id', $currentQuestionId)
            ->first();
        
        $remainingSeconds = $attempt->getRemainingSeconds();

        return view('exam.workspace', compact(
            'attempt',
            'exam',
            'currentAttemptQuestion',
            'remainingSeconds'
        ));
    }

    public function runCode(Request $request, Attempt $attempt)
    {
        $this->ensureAttemptActive($attempt);
        
        $request->validate([
            'attempt_question_id' => 'required|exists:attempt_questions,id',
            'code' => 'required|string',
        ]);
        
        $attemptQuestion = AttemptQuestion::where('attempt_id', $attempt->id)
            ->findOrFail($request->attempt_question_id);
        
        $question = $attemptQuestion->question;
        
        $judge0 = app(Judge0Service::class);
        
        $testCases = $question->getVisibleTestCases();
        $testInput = $testCases[0]['input'] ?? '';
        $stdin = is_array($testInput) ? $testInput : explode("\n", $testInput);
        
        $result = $judge0->execute($request->code, $question->language, $stdin);
        
        $submission = Submission::create([
            'attempt_question_id' => $attemptQuestion->id,
            'code' => $request->code,
            'output' => $result->output,
            'error' => $result->error,
            'execution_time_ms' => $result->executionTime,
            'status' => $result->status,
            'is_final' => false,
        ]);
        
        return response()->json([
            'output' => $result->output,
            'error' => $result->error,
            'status' => $result->status,
            'execution_time_ms' => $result->executionTime,
            'submission_id' => $submission->id,
        ]);
    }

    public function autosave(Request $request, Attempt $attempt)
    {
        $this->ensureAttemptActive($attempt);
        
        $request->validate([
            'attempt_question_id' => 'required|exists:attempt_questions,id',
            'code' => 'required|string',
        ]);
        
        $attemptQuestion = AttemptQuestion::where('attempt_id', $attempt->id)
            ->findOrFail($request->attempt_question_id);
        
        $attemptQuestion->update(['code' => $request->code]);
        
        return response()->json([
            'saved' => true,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function trackActivity(Request $request, Attempt $attempt): JsonResponse
    {
        $request->validate([
            'event' => ['required', 'in:activity,tab_switch,inactivity_timeout'],
        ]);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'ok' => false,
                'disqualified' => (bool) $attempt->is_disqualified,
                'message' => $attempt->is_disqualified
                    ? ($attempt->disqualification_reason ?: 'Attempt disqualified.')
                    : 'Attempt already submitted.',
                'redirect_url' => $this->getPostSubmitRedirect($attempt),
            ], 409);
        }

        $event = (string) $request->input('event');
        $exam = $attempt->exam;

        if ($event === 'activity') {
            $attempt->update([
                'last_activity_at' => now(),
            ]);

            return response()->json([
                'ok' => true,
                'disqualified' => false,
                'tab_switch_count' => (int) $attempt->tab_switch_count,
            ]);
        }

        if ($event === 'tab_switch') {
            $attempt->tab_switch_count = (int) $attempt->tab_switch_count + 1;
            $attempt->last_activity_at = now();
            $attempt->save();

            $maxTabSwitches = (int) $exam->max_tab_switches;
            if ($maxTabSwitches > 0 && (int) $attempt->tab_switch_count >= $maxTabSwitches) {
                $reason = "Disqualified: tab switched {$attempt->tab_switch_count} times (limit {$maxTabSwitches}).";
                $this->disqualifyAttempt($attempt, $reason);

                return response()->json([
                    'ok' => false,
                    'disqualified' => true,
                    'message' => $reason,
                    'tab_switch_count' => (int) $attempt->tab_switch_count,
                    'redirect_url' => $this->getPostSubmitRedirect($attempt),
                ]);
            }

            return response()->json([
                'ok' => true,
                'disqualified' => false,
                'tab_switch_count' => (int) $attempt->tab_switch_count,
                'tab_switches_remaining' => $maxTabSwitches > 0
                    ? max(0, $maxTabSwitches - (int) $attempt->tab_switch_count)
                    : null,
            ]);
        }

        $reason = 'Disqualified: no mouse or keyboard activity within allowed inactivity limit.';
        $this->disqualifyAttempt($attempt, $reason);

        return response()->json([
            'ok' => false,
            'disqualified' => true,
            'message' => $reason,
            'redirect_url' => $this->getPostSubmitRedirect($attempt),
        ]);
    }

    public function finalSubmit(Attempt $attempt)
    {
        $exam = $attempt->exam;

        if ($attempt->status !== 'in_progress') {
            if ($exam->show_score_immediately) {
                return redirect()->route('exam.result', $attempt->id);
            }

            return redirect()->route('exam.submitted', $attempt->id);
        }

        $attempt->update(['submitted_at' => now(), 'status' => 'submitted']);
        
        $gradingService = app(GradingService::class);
        $gradingService->gradeAllQuestions($attempt);
        
        $attempt->refresh();
        
        if ($exam->show_score_immediately) {
            return redirect()->route('exam.result', $attempt->id);
        }

        return redirect()->route('exam.submitted', $attempt->id);
    }

    public function submitted(Attempt $attempt)
    {
        $exam = $attempt->exam;

        if ($attempt->status === 'in_progress') {
            return redirect()->route('exam.workspace', $attempt->id);
        }

        return view('exam.submitted', compact('attempt', 'exam'));
    }

    public function result(Attempt $attempt)
    {
        $exam = $attempt->exam;
        
        if (!$exam->show_score_immediately) {
            abort(403, 'Scores are not shown for this exam.');
        }
        
        $attempt->load(['attemptQuestions.question', 'student']);
        
        return view('exam.result', compact('attempt', 'exam'));
    }

    private function ensureAttemptActive(Attempt $attempt): void
    {
        if ($attempt->is_disqualified) {
            abort(403, $attempt->disqualification_reason ?: 'This attempt has been disqualified.');
        }

        if ($attempt->status !== 'in_progress') {
            abort(403, 'This exam has already been submitted.');
        }

        $now = now();
        if ($now->lt($attempt->exam->opens_at) || $now->gt($attempt->exam->closes_at)) {
            $attempt->update([
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);

            $gradingService = app(GradingService::class);
            $gradingService->gradeAllQuestions($attempt);

            abort(403, 'Exam access window has ended. Exam has been auto-submitted.');
        }

        if ($attempt->getRemainingSeconds() <= 0) {
            $attempt->update([
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);

            $gradingService = app(GradingService::class);
            $gradingService->gradeAllQuestions($attempt);

            abort(403, 'Time expired. Exam has been auto-submitted.');
        }

        $inactivityLimitSeconds = (int) $attempt->exam->inactivity_limit_seconds;
        if (
            $inactivityLimitSeconds > 0
            && $attempt->last_activity_at
            && now()->diffInSeconds($attempt->last_activity_at) >= $inactivityLimitSeconds
        ) {
            $this->disqualifyAttempt($attempt, 'Disqualified: inactivity exceeded configured limit.');
            abort(403, $attempt->disqualification_reason ?: 'Attempt disqualified due to inactivity.');
        }
    }

    private function disqualifyAttempt(Attempt $attempt, string $reason): void
    {
        if ($attempt->is_disqualified) {
            return;
        }

        $maxScore = (float) $attempt->attemptQuestions()->sum('weight');
        $attempt->attemptQuestions()->update([
            'score' => 0,
            'is_correct' => false,
        ]);

        $attempt->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_disqualified' => true,
            'disqualified_at' => now(),
            'disqualification_reason' => $reason,
            'total_score' => 0,
            'max_score' => $maxScore,
        ]);
    }

    private function getPostSubmitRedirect(Attempt $attempt): string
    {
        if ($attempt->exam->show_score_immediately) {
            return route('exam.result', $attempt->id);
        }

        return route('exam.submitted', $attempt->id);
    }
}
