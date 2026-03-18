<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademicPeriodController extends Controller
{
    public function index(): View
    {
        $periods = AcademicPeriod::latest()->paginate(15);

        return view('admin.academic_periods.index', compact('periods'));
    }

    public function create(): View
    {
        return view('admin.academic_periods.form', ['period' => new AcademicPeriod()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . now()->timestamp;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if ($data['is_active']) {
            AcademicPeriod::query()->update(['is_active' => false]);
        }

        AcademicPeriod::create($data);

        return redirect()->route('admin.academic-periods.index')->with('success', 'Academic period created.');
    }

    public function edit(AcademicPeriod $academicPeriod): View
    {
        return view('admin.academic_periods.form', ['period' => $academicPeriod]);
    }

    public function update(Request $request, AcademicPeriod $academicPeriod): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if ($data['is_active']) {
            AcademicPeriod::where('id', '!=', $academicPeriod->id)->update(['is_active' => false]);
        }

        $academicPeriod->update($data);

        return redirect()->route('admin.academic-periods.index')->with('success', 'Academic period updated.');
    }

    public function destroy(AcademicPeriod $academicPeriod): RedirectResponse
    {
        $academicPeriod->delete();

        return redirect()->route('admin.academic-periods.index')->with('success', 'Academic period deleted.');
    }
}
