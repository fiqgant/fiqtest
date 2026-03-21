@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Course Offerings" subtitle="Bind each course to a specific academic period and class group.">
        <a href="{{ route('admin.course-offerings.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Offering</a>
    </x-admin.page-header>

    <div class="card overflow-x-auto">
        <table class="data-table" style="min-width:700px">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Period</th>
                    <th>Class</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($offerings as $offering)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $offering->course->name }}</td>
                        <td class="text-slate-500">{{ $offering->academicPeriod->name }}</td>
                        <td><span class="inline-flex items-center px-2 py-0.5 rounded-md bg-cyan-50 text-cyan-700 text-xs font-semibold">{{ $offering->class_name }}</span></td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.course-offerings.enrollment', $offering) }}" class="action-btn action-btn-neutral"><i class="fas fa-user-plus"></i> Enrollment</a>
                                <a href="{{ route('admin.reports.offering', $offering) }}" class="action-btn action-btn-neutral"><i class="fas fa-chart-bar"></i> Report</a>
                                <a href="{{ route('admin.course-offerings.edit', $offering) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                <button class="action-btn action-btn-danger" onclick="confirmDelete('/admin/course-offerings/{{ $offering->id }}', 'Delete offering?')"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-layer-group text-2xl"></i><span>No offerings yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $offerings->links() }}</div>
@endsection
