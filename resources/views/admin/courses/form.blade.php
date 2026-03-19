@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="$course->exists ? 'Edit Course' : 'Create Course'" subtitle="Define the course code, display name, and context notes." />

    <form method="POST" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}">
        @csrf
        @if($course->exists)
            @method('PUT')
        @endif

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-book mr-1.5"></i> Course Details</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Course Code</label>
                    <input type="text" name="code" value="{{ old('code', $course->code) }}" class="form-input font-mono" placeholder="e.g. CS101" required>
                </div>
                <div>
                    <label class="form-label">Course Name</label>
                    <input type="text" name="name" value="{{ old('name', $course->name) }}" class="form-input" placeholder="Full course name" required>
                </div>
            </div>
            <div>
                <label class="form-label">Description <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="description" rows="4" class="form-textarea" placeholder="Brief course description…">{{ old('description', $course->description) }}</textarea>
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.courses.index')" />
    </form>
@endsection
