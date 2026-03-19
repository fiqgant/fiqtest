<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\AttemptQuestion;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Services\GradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with('courseOffering.course', 'courseOffering.academicPeriod')
            ->latest()
            ->paginate(20);

        return view('admin.exams.index', compact('exams'));
    }

    public function create(Request $request): View
    {
        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $allTags = QuestionTag::orderBy('name')->get();

        return view('admin.exams.form', [
            'exam' => new Exam(),
            'offerings' => $offerings,
            'allTags' => $allTags,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $data['slug'] = Str::slug($data['title']) . '-' . Str::lower(Str::random(6));

        Exam::create($data);

        return redirect()->route('admin.exams.index')->with('success', 'Exam created.');
    }

    public function edit(Exam $exam): View
    {
        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $allTags = QuestionTag::orderBy('name')->get();

        return view('admin.exams.form', compact('exam', 'offerings', 'allTags'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $exam->update($data);

        return redirect()->route('admin.exams.index')->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted.');
    }

    public function publish(Exam $exam): RedirectResponse
    {
        if (!$this->hasEnoughQuestionsByDifficulty($exam)) {
            return back()->withErrors(['publish' => 'Question bank per difficulty does not satisfy this exam distribution.']);
        }

        $exam->update(['status' => 'published']);

        return back()->with('success', 'Exam published.');
    }

    public function close(Exam $exam): RedirectResponse
    {
        $exam->update(['status' => 'closed']);

        return back()->with('success', 'Exam closed.');
    }

    public function attempts(Exam $exam): View
    {
        $attempts = $exam->attempts()->with('student')->latest()->paginate(30);

        return view('admin.exams.attempts', compact('exam', 'attempts'));
    }

    public function attemptDetail(Exam $exam, Attempt $attempt): View
    {
        $attempt->load([
            'student',
            'attemptQuestions.question.options',
            'attemptQuestions.submissions' => fn($q) => $q->where('is_final', true)->latest()->limit(1),
        ]);

        return view('admin.exams.attempt-detail', compact('exam', 'attempt'));
    }

    public function gradeEssay(Request $request, Exam $exam, Attempt $attempt, AttemptQuestion $attemptQuestion): RedirectResponse
    {
        $request->validate([
            'manual_score'    => ['required', 'numeric', 'min:0'],
            'manual_feedback' => ['nullable', 'string'],
        ]);

        $maxScore = (float) $attemptQuestion->weight;
        $score    = min((float) $request->manual_score, $maxScore);

        $attemptQuestion->update([
            'manual_score'    => $score,
            'manual_feedback' => $request->manual_feedback,
        ]);

        // Recompute attempt total
        app(GradingService::class)->updateAttemptTotal($attempt);

        return back()->with('success', 'Essay graded successfully.');
    }

    public function exportExcel(Exam $exam): Response
    {
        $attempts = $exam->attempts()
            ->with(['student', 'attemptQuestions.question'])
            ->whereIn('status', ['submitted', 'graded'])
            ->orderBy('submitted_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Results');

        // Header row
        $headers = ['NIM', 'Name', 'Started', 'Submitted', 'Duration (min)', 'Score', 'Max Score', '%', 'Tab Switches', 'Disqualified', 'Disqualification Reason'];
        $cols = ['A','B','C','D','E','F','G','H','I','J','K'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '1', $h);
        }

        $row = 2;
        foreach ($attempts as $attempt) {
            $duration = $attempt->started_at && $attempt->submitted_at
                ? round($attempt->started_at->diffInSeconds($attempt->submitted_at) / 60, 1)
                : '';
            $pct = $attempt->max_score > 0
                ? round($attempt->total_score / $attempt->max_score * 100, 1)
                : 0;

            $sheet->setCellValue('A' . $row, $attempt->student->nim);
            $sheet->setCellValue('B' . $row, $attempt->student->name);
            $sheet->setCellValue('C' . $row, optional($attempt->started_at)->format('d/m/Y H:i'));
            $sheet->setCellValue('D' . $row, optional($attempt->submitted_at)->format('d/m/Y H:i'));
            $sheet->setCellValue('E' . $row, $duration);
            $sheet->setCellValue('F' . $row, $attempt->total_score);
            $sheet->setCellValue('G' . $row, $attempt->max_score);
            $sheet->setCellValue('H' . $row, $pct . '%');
            $sheet->setCellValue('I' . $row, $attempt->tab_switch_count);
            $sheet->setCellValue('J' . $row, $attempt->is_disqualified ? 'Yes' : 'No');
            $sheet->setCellValue('K' . $row, $attempt->disqualification_reason ?? '');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'exam-results-' . Str::slug($exam->title) . '.xlsx';

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function questionPool(Exam $exam): View
    {
        $filterTags = $exam->question_filter_tags ?? [];

        $questions = Question::where('course_offering_id', $exam->course_offering_id)
            ->with('tags')
            ->when(!empty($filterTags), fn($q) => $q->whereHas('tags', fn($q2) => $q2->whereIn('question_tags.id', $filterTags)))
            ->orderBy('difficulty')
            ->orderBy('title')
            ->get();

        $counts = $questions->groupBy('difficulty')->map->count();

        return view('admin.exams.question-pool', compact('exam', 'questions', 'counts'));
    }

    public function monitor(Exam $exam): View
    {
        return view('admin.exams.monitor', compact('exam'));
    }

    public function monitorData(Exam $exam): JsonResponse
    {
        $activeAttempts = $exam->attempts()
            ->with('student')
            ->where('status', 'in_progress')
            ->get()
            ->map(function (Attempt $attempt) {
                $answeredCount = $attempt->attemptQuestions()
                    ->where(fn($q) => $q->whereNotNull('code')->orWhereNotNull('student_answer'))
                    ->count();

                return [
                    'id'              => $attempt->id,
                    'nim'             => $attempt->student->nim,
                    'name'            => $attempt->student->name,
                    'started_at'      => optional($attempt->started_at)->format('H:i:s'),
                    'remaining_sec'   => $attempt->getRemainingSeconds(),
                    'answered'        => $answeredCount,
                    'total_questions' => $attempt->attemptQuestions()->count(),
                    'tab_switches'    => $attempt->tab_switch_count,
                    'last_activity'   => optional($attempt->last_activity_at)->diffForHumans() ?? 'Unknown',
                    'is_disqualified' => $attempt->is_disqualified,
                    'ip_address'      => $attempt->ip_address,
                ];
            });

        $submittedCount = $exam->attempts()->whereIn('status', ['submitted', 'graded'])->count();
        $totalEnrolled  = $exam->courseOffering->students()->count();

        return response()->json([
            'active'    => $activeAttempts,
            'submitted' => $submittedCount,
            'enrolled'  => $totalEnrolled,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'course_offering_id' => ['required', 'exists:course_offerings,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after:opens_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'status' => ['required', 'in:draft,published,closed'],
            'show_score_immediately' => ['nullable', 'boolean'],
            'hints_enabled' => ['nullable', 'boolean'],
            'max_hints_per_question' => ['required', 'integer', 'min:0', 'max:10'],
            'max_tab_switches' => ['required', 'integer', 'min:0', 'max:20'],
            'tab_switch_warning_count' => ['required', 'integer', 'min:0', 'max:20'],
            'inactivity_limit_seconds' => ['required', 'integer', 'min:0', 'max:3600'],
            'inactivity_warning_seconds' => ['required', 'integer', 'min:0', 'max:3600'],
            'disable_inspect' => ['nullable', 'boolean'],
            'easy_count' => ['required', 'integer', 'min:0'],
            'medium_count' => ['required', 'integer', 'min:0'],
            'hard_count' => ['required', 'integer', 'min:0'],
            'easy_weight' => ['required', 'numeric', 'min:0'],
            'medium_weight' => ['required', 'numeric', 'min:0'],
            'hard_weight' => ['required', 'numeric', 'min:0'],
            'question_filter_tags' => ['nullable', 'array'],
            'question_filter_tags.*' => ['integer', 'exists:question_tags,id'],
        ]);

        $data['show_score_immediately'] = (bool) ($data['show_score_immediately'] ?? false);
        $data['hints_enabled'] = (bool) ($data['hints_enabled'] ?? false);
        $data['disable_inspect'] = (bool) ($data['disable_inspect'] ?? false);

        if ((int) $data['max_tab_switches'] === 0) {
            $data['tab_switch_warning_count'] = 0;
        } else {
            $data['tab_switch_warning_count'] = max(1, min((int) $data['tab_switch_warning_count'], (int) $data['max_tab_switches']));
        }

        if ((int) $data['inactivity_limit_seconds'] === 0) {
            $data['inactivity_warning_seconds'] = 0;
        } else {
            $data['inactivity_warning_seconds'] = max(1, min((int) $data['inactivity_warning_seconds'], (int) $data['inactivity_limit_seconds']));
        }

        return $data;
    }

    private function hasEnoughQuestionsByDifficulty(Exam $exam): bool
    {
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

        return $easyAvailable >= $exam->easy_count
            && $mediumAvailable >= $exam->medium_count
            && $hardAvailable >= $exam->hard_count;
    }
}
