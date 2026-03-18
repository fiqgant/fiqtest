@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="$offering->exists ? 'Edit Offering' : 'Create Offering'" subtitle="Assign one course to one academic period and one class section." />

    <form method="POST" action="{{ $offering->exists ? route('admin.course-offerings.update', $offering) : route('admin.course-offerings.store') }}">
        @csrf
        @if($offering->exists)
            @method('PUT')
        @endif

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-layer-group mr-1.5"></i> Offering Configuration</div>
            <div class="mb-4">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="">Select course…</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) old('course_id', $offering->course_id) === (string) $course->id ? 'selected' : '' }}>{{ $course->code }} — {{ $course->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Academic Period</label>
                <select name="academic_period_id" class="form-select" required>
                    <option value="">Select period…</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ (string) old('academic_period_id', $offering->academic_period_id) === (string) $period->id ? 'selected' : '' }}>{{ $period->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Class Name</label>
                <input type="text" name="class_name" value="{{ old('class_name', $offering->class_name) }}" class="form-input" placeholder="e.g. A, B, C or TI-2A" required>
                <p class="text-xs text-slate-400 mt-1">A short identifier for the class group.</p>
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.course-offerings.index')" />
    </form>
@endsection
