@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="$student->exists ? 'Edit Student' : 'Add Student'" subtitle="Capture student identity details used for secure exam access." />

    <form method="POST" action="{{ $student->exists ? route('admin.students.update', $student) : route('admin.students.store') }}">
        @csrf
        @if($student->exists) @method('PUT') @endif

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-id-card mr-1.5"></i> Student Identity</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">NIM</label>
                    <input type="text" name="nim" value="{{ old('nim', $student->nim) }}" class="form-input font-mono" placeholder="e.g. 123456789" required>
                </div>
                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $student->name) }}" class="form-input" placeholder="Student full name" required>
                </div>
            </div>
            <div>
                <label class="form-label">Email <span class="font-normal text-slate-400">(optional)</span></label>
                <input type="email" name="email" value="{{ old('email', $student->email) }}" class="form-input" placeholder="student@example.com">
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.students.index')" />
    </form>
@endsection
