@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Question Bank" subtitle="Build a balanced pool of easy, medium, and hard coding problems.">
        <a href="{{ route('admin.questions.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Question</a>
    </x-admin.page-header>

    <div class="card mb-5 p-5">
        <form method="GET" class="flex items-end gap-3">
            <div class="flex-1">
                <label class="form-label">Filter by Course Offering</label>
                <select name="course_offering_id" class="form-select">
                    <option value="">All Offerings</option>
                    @foreach($offerings as $offering)
                        <option value="{{ $offering->id }}" {{ (string) $offeringId === (string) $offering->id ? 'selected' : '' }}>
                            {{ $offering->course->name }} — {{ $offering->academicPeriod->name }} · {{ $offering->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="btn-secondary"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Difficulty</th>
                    <th>Offering</th>
                    <th>Language</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $question)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $question->title }}</td>
                        <td><x-admin.status-badge :value="$question->difficulty" /></td>
                        <td class="text-slate-500 text-xs">{{ $question->courseOffering->course->name }}<br><span class="text-slate-400">{{ $question->courseOffering->academicPeriod->name }} · {{ $question->courseOffering->class_name }}</span></td>
                        <td><span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-mono font-semibold">{{ $question->language }}</span></td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.questions.edit', $question) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                <form class="inline" method="POST" action="{{ route('admin.questions.destroy', $question) }}">
                                    @csrf @method('DELETE')
                                    <button class="action-btn action-btn-danger" onclick="return confirm('Delete question?')"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-database text-2xl"></i><span>No questions yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $questions->links() }}</div>
@endsection
