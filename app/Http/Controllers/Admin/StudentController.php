<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        $students = Student::orderBy('nim')->paginate(20);

        return view('admin.students.index', compact('students'));
    }

    public function create(): View
    {
        return view('admin.students.form', ['student' => new Student()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nim' => ['required', 'string', 'max:50', 'unique:students,nim'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        Student::create($data);

        return redirect()->route('admin.students.index')->with('success', 'Student created.');
    }

    public function edit(Student $student): View
    {
        return view('admin.students.form', compact('student'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'nim' => ['required', 'string', 'max:50', 'unique:students,nim,' . $student->id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $student->update($data);

        return redirect()->route('admin.students.index')->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $count = Student::whereIn('id', $request->input('ids', []))->delete();

        return redirect()->route('admin.students.index')->with('success', "{$count} student(s) deleted.");
    }
}
