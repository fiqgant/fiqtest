@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Students" subtitle="Manage student identity records used for NIM-based exam access.">
        <a href="{{ route('admin.students.bulk.import') }}" class="btn-secondary"><i class="fas fa-file-import"></i> Bulk Import</a>
        <a href="{{ route('admin.students.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Student</a>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td><span class="font-mono font-semibold text-slate-700">{{ $student->nim }}</span></td>
                        <td class="font-medium text-slate-800">{{ $student->name }}</td>
                        <td class="text-slate-500">{{ $student->email ?: '—' }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.reports.student', $student) }}" class="action-btn action-btn-neutral"><i class="fas fa-chart-line"></i> History</a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                <form class="inline" method="POST" action="{{ route('admin.students.destroy', $student) }}">
                                    @csrf @method('DELETE')
                                    <button class="action-btn action-btn-danger" onclick="return confirm('Delete this student?')"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-users text-2xl"></i><span>No students yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>
@endsection
