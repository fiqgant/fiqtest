@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Courses" subtitle="Maintain the master course catalog used across all academic periods.">
        <a href="{{ route('admin.courses.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Course</a>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td><span class="font-mono font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md text-xs">{{ $course->code }}</span></td>
                        <td class="font-semibold text-slate-800">{{ $course->name }}</td>
                        <td class="text-slate-500 text-sm max-w-xs truncate">{{ $course->description ?: '—' }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.courses.edit', $course) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                <form class="inline" method="POST" action="{{ route('admin.courses.destroy', $course) }}">
                                    @csrf @method('DELETE')
                                    <button class="action-btn action-btn-danger" onclick="return confirm('Delete course?')"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-book text-2xl"></i><span>No courses yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $courses->links() }}</div>
@endsection
