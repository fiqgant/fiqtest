@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="$period->exists ? 'Edit Academic Period' : 'Create Academic Period'" subtitle="Set date boundaries and active-term behavior for this academic cycle." />

    <form method="POST" action="{{ $period->exists ? route('admin.academic-periods.update', $period) : route('admin.academic-periods.store') }}">
        @csrf
        @if($period->exists)
            @method('PUT')
        @endif

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-calendar-alt mr-1.5"></i> Period Details</div>
            <div class="mb-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name', $period->name) }}" class="form-input" placeholder="e.g. Semester Ganjil 2024/2025" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', optional($period->start_date)->format('Y-m-d')) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" value="{{ old('end_date', optional($period->end_date)->format('Y-m-d')) }}" class="form-input" required>
                </div>
            </div>

            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors">
                <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded accent-indigo-600" {{ old('is_active', $period->is_active) ? 'checked' : '' }}>
                <div>
                    <div class="text-sm font-semibold text-slate-700">Set as active period</div>
                    <div class="text-xs text-slate-400">Only one period can be active at a time.</div>
                </div>
            </label>
        </div>

        <x-admin.form-actions :cancel="route('admin.academic-periods.index')" />
    </form>
@endsection
