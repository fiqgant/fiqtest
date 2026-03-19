<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Attempt;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'students'           => Student::count(),
            'questions'          => Question::count(),
            'exams'              => Exam::count(),
            'submitted_attempts' => Attempt::whereIn('status', ['submitted', 'graded'])->count(),
        ];

        // Active exams right now
        $activeExams = Exam::with('courseOffering.course')
            ->where('status', 'published')
            ->where('opens_at', '<=', now())
            ->where('closes_at', '>=', now())
            ->withCount([
                'attempts as active_count'    => fn($q) => $q->where('status', 'in_progress'),
                'attempts as submitted_count' => fn($q) => $q->whereIn('status', ['submitted', 'graded']),
            ])
            ->get();

        // Students currently in an exam
        $liveCount = Attempt::where('status', 'in_progress')->count();

        // Average score across all graded attempts
        $avgScore = Attempt::where('status', 'graded')
            ->where('max_score', '>', 0)
            ->get()
            ->avg(fn($a) => (float) $a->max_score > 0 ? round((float) $a->total_score / (float) $a->max_score * 100, 1) : 0);

        // Disqualified count
        $disqualifiedCount = Attempt::where('is_disqualified', true)->count();

        // Pending essay grading
        $pendingEssays = AttemptQuestion::whereHas('question', fn($q) => $q->where('type', 'essay'))
            ->whereHas('attempt', fn($q) => $q->whereIn('status', ['submitted', 'graded']))
            ->whereNull('manual_score')
            ->whereNotNull('student_answer')
            ->count();

        // Recent submissions
        $recentAttempts = Attempt::with(['student', 'exam.courseOffering.course'])
            ->whereIn('status', ['submitted', 'graded'])
            ->latest('submitted_at')
            ->limit(8)
            ->get();

        // Upcoming exams (not yet open)
        $upcomingExams = Exam::with('courseOffering.course')
            ->where('status', 'published')
            ->where('opens_at', '>', now())
            ->orderBy('opens_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'activeExams', 'liveCount', 'avgScore',
            'disqualifiedCount', 'pendingEssays', 'recentAttempts', 'upcomingExams'
        ));
    }

    public function liveFeed(): JsonResponse
    {
        $active = Attempt::with(['student', 'exam', 'attemptQuestions.question'])
            ->where('status', 'in_progress')
            ->get()
            ->map(function (Attempt $attempt) {
                $lastAq = $attempt->attemptQuestions
                    ->sortByDesc('updated_at')
                    ->first();

                $answeredCount = $attempt->attemptQuestions->filter(
                    fn($aq) => filled($aq->code) || filled($aq->student_answer)
                )->count();

                $total = $attempt->attemptQuestions->count();

                return [
                    'id'             => $attempt->id,
                    'name'           => $attempt->student->name,
                    'nim'            => $attempt->student->nim,
                    'exam'           => $attempt->exam->title,
                    'current_q'      => $lastAq?->question?->title ?? '—',
                    'current_q_type' => $lastAq?->question?->type ?? '',
                    'answered'       => $answeredCount,
                    'total'          => $total,
                    'remaining_sec'  => $attempt->getRemainingSeconds(),
                    'tab_switches'   => $attempt->tab_switch_count,
                    'last_active'    => optional($attempt->last_activity_at)->diffForHumans() ?? 'Unknown',
                    'is_disqualified'=> $attempt->is_disqualified,
                ];
            });

        return response()->json([
            'students'  => $active,
            'count'     => $active->count(),
            'timestamp' => now()->format('H:i:s'),
        ]);
    }
}
