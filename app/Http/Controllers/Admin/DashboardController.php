<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'periods' => AcademicPeriod::count(),
            'courses' => Course::count(),
            'offerings' => CourseOffering::count(),
            'students' => Student::count(),
            'exams' => Exam::count(),
            'questions' => Question::count(),
            'attempts' => Attempt::count(),
            'submitted_attempts' => Attempt::whereIn('status', ['submitted', 'graded'])->count(),
        ];

        $recentExams = Exam::with('courseOffering.course', 'courseOffering.academicPeriod')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentExams'));
    }
}
