@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Question Tags" subtitle="Manage tags to categorize questions and filter exam pools." />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <form method="POST" action="{{ route('admin.question-tags.store') }}">
                @csrf
                <div class="form-section">
                    <div class="form-section-title">Add New Tag</div>
                    <input type="text" name="name" class="form-input mb-3" placeholder="e.g. array, recursion, sorting" value="{{ old('name') }}" required>
                    @error('name') <p class="text-xs text-rose-500 mb-2">{{ $message }}</p> @enderror
                    <button type="submit" class="btn-primary w-full">Add Tag</button>
                </div>
            </form>
        </div>
        <div class="md:col-span-2">
            <div class="form-section">
                <div class="form-section-title">All Tags ({{ $tags->count() }})</div>
                @if($tags->isEmpty())
                    <p class="text-sm text-slate-400 text-center py-6">No tags yet.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                        <div class="flex items-center gap-2 bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-1.5">
                            <span class="text-sm font-medium text-indigo-700">{{ $tag->name }}</span>
                            <span class="text-xs text-indigo-400">{{ $tag->questions_count }} questions</span>
                            <button type="button" class="text-rose-400 hover:text-rose-600 ml-1" onclick="confirmDelete('/admin/question-tags/{{ $tag->id }}', 'Delete tag {{ $tag->name }}?')"><i class="fas fa-times text-xs"></i></button>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
