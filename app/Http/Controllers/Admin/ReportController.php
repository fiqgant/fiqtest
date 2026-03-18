<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Attempt;
use App\Models\CourseOffering;
use App\Models\Student;
use App\Services\GradeReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly GradeReportService $gradeReportService)
    {
    }

    public function index(Request $request): View
    {
        $periods = AcademicPeriod::orderByDesc('start_date')->get();

        $offeringsQuery = CourseOffering::with('course', 'academicPeriod')
            ->orderByDesc('id');

        if ($request->filled('academic_period_id')) {
            $offeringsQuery->where('academic_period_id', (int) $request->integer('academic_period_id'));
        }

        $offerings = $offeringsQuery->get();
        $students = Student::orderBy('nim')->get();

        $attemptsBaseQuery = Attempt::query()->whereIn('status', ['submitted', 'graded']);

        $reportStats = [
            'total_attempts' => (clone $attemptsBaseQuery)->count(),
            'graded_attempts' => Attempt::where('status', 'graded')->count(),
            'disqualified_attempts' => Attempt::where('is_disqualified', true)->count(),
            'average_percentage' => round(
                (clone $attemptsBaseQuery)
                    ->where('max_score', '>', 0)
                    ->get()
                    ->avg(fn(Attempt $attempt): float => ((float) $attempt->total_score / (float) $attempt->max_score) * 100)
                    ?? 0,
                2
            ),
        ];

        $recentAttempts = Attempt::with(['student', 'exam.courseOffering.course'])
            ->whereIn('status', ['submitted', 'graded'])
            ->latest('submitted_at')
            ->limit(12)
            ->get();

        return view('admin.reports.index', compact('periods', 'offerings', 'students', 'reportStats', 'recentAttempts'));
    }

    public function offering(CourseOffering $courseOffering): View
    {
        $grades = $this->gradeReportService->getOfferingGrades($courseOffering);
        $exams = $courseOffering->exams()->orderBy('opens_at')->get();

        return view('admin.reports.offering', compact('courseOffering', 'grades', 'exams'));
    }

    public function period(AcademicPeriod $academicPeriod): View
    {
        $periodReports = $this->gradeReportService->getPeriodGrades($academicPeriod);

        return view('admin.reports.period', compact('academicPeriod', 'periodReports'));
    }

    public function student(Student $student): View
    {
        $history = $this->gradeReportService->getStudentHistory($student);

        return view('admin.reports.student', compact('student', 'history'));
    }

    public function exportOffering(CourseOffering $courseOffering): Response
    {
        $csv = $this->gradeReportService->exportToCsv($courseOffering);
        $filename = 'grades-offering-' . $courseOffering->id . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
