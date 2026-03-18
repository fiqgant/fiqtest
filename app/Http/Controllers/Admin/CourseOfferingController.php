<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseOfferingController extends Controller
{
    public function index(): View
    {
        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->paginate(20);

        return view('admin.course_offerings.index', compact('offerings'));
    }

    public function create(): View
    {
        $courses = Course::orderBy('name')->get();
        $periods = AcademicPeriod::orderByDesc('start_date')->get();

        return view('admin.course_offerings.form', [
            'offering' => new CourseOffering(),
            'courses' => $courses,
            'periods' => $periods,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'academic_period_id' => ['required', 'exists:academic_periods,id'],
            'class_name' => ['required', 'string', 'max:50'],
        ]);

        CourseOffering::create($data);

        return redirect()->route('admin.course-offerings.index')->with('success', 'Course offering created.');
    }

    public function edit(CourseOffering $courseOffering): View
    {
        $courses = Course::orderBy('name')->get();
        $periods = AcademicPeriod::orderByDesc('start_date')->get();

        return view('admin.course_offerings.form', [
            'offering' => $courseOffering,
            'courses' => $courses,
            'periods' => $periods,
        ]);
    }

    public function update(Request $request, CourseOffering $courseOffering): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'academic_period_id' => ['required', 'exists:academic_periods,id'],
            'class_name' => ['required', 'string', 'max:50'],
        ]);

        $courseOffering->update($data);

        return redirect()->route('admin.course-offerings.index')->with('success', 'Course offering updated.');
    }

    public function destroy(CourseOffering $courseOffering): RedirectResponse
    {
        $courseOffering->delete();

        return redirect()->route('admin.course-offerings.index')->with('success', 'Course offering deleted.');
    }

    public function enrollment(CourseOffering $courseOffering): View
    {
        $courseOffering->load('students', 'course', 'academicPeriod');
        $students = Student::orderBy('nim')->paginate(30);

        return view('admin.course_offerings.enrollment', compact('courseOffering', 'students'));
    }

    public function syncEnrollment(Request $request, CourseOffering $courseOffering): RedirectResponse
    {
        $studentIds = $request->validate([
            'student_ids' => ['array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ])['student_ids'] ?? [];

        $courseOffering->students()->sync($studentIds);

        return redirect()->route('admin.course-offerings.enrollment', $courseOffering)->with('success', 'Enrollment updated.');
    }
}
