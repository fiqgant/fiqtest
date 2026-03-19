@extends('admin.layouts.app')

@section('content')
    @php
        $valid   = array_filter($rows, fn($r) => empty($r['errors']));
        $invalid = array_filter($rows, fn($r) => !empty($r['errors']));
        $typeLabels = \App\Models\Question::TYPES;
    @endphp

    <x-admin.page-header title="Preview Import" subtitle="{{ count($valid) }} valid · {{ count($invalid) }} with errors">
        <a href="{{ route('admin.questions.bulk.import') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </x-admin.page-header>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Rows</div>
            <div class="text-2xl font-extrabold text-slate-800">{{ count($rows) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-emerald-500 uppercase tracking-wider mb-1">Will Import</div>
            <div class="text-2xl font-extrabold text-emerald-600">{{ count($valid) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-rose-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-rose-400 uppercase tracking-wider mb-1">Will Skip</div>
            <div class="text-2xl font-extrabold text-rose-500">{{ count($invalid) }}</div>
        </div>
    </div>

    {{-- Invalid rows --}}
    @if(count($invalid) > 0)
        <div class="mb-5">
            <div class="form-section-title text-rose-600 mb-3"><i class="fas fa-times-circle mr-1.5"></i> Rows with errors (will be skipped)</div>
            @foreach($invalid as $row)
                <div class="mb-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-mono text-rose-400">Row {{ $row['row'] }}</span>
                        <span class="font-semibold text-rose-700">{{ $row['title'] ?: '(no title)' }}</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-600 space-y-0.5">
                        @foreach($row['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Valid rows preview --}}
    @if(count($valid) > 0)
        <div class="form-section-title mb-3"><i class="fas fa-check-circle text-emerald-500 mr-1.5"></i> Questions to import</div>
        <div class="space-y-3 mb-6">
            @foreach($valid as $row)
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider
                                {{ $row['difficulty'] === 'easy' ? 'text-emerald-600' : ($row['difficulty'] === 'medium' ? 'text-amber-500' : 'text-rose-500') }}">
                                {{ ucfirst($row['difficulty']) }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                {{ $typeLabels[$row['type']] ?? $row['type'] }}
                            </span>
                            <span class="font-semibold text-slate-800">{{ $row['title'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            @if($row['hint']) <span class="text-amber-500"><i class="fas fa-lightbulb"></i> Hint</span> @endif
                            @if($row['reference_solution']) <span class="text-indigo-500"><i class="fas fa-code"></i> Solution</span> @endif
                            @if($row['type'] === 'coding')
                                <span>{{ count($row['test_cases']) }} test case(s)</span>
                            @elseif(in_array($row['type'], ['multiple_choice', 'multiple_select']))
                                <span>{{ count($row['options']) }} option(s)</span>
                            @endif
                            <span class="font-semibold text-slate-600">{{ $row['default_weight'] }} pts</span>
                        </div>
                    </div>
                    <div class="px-4 py-3 text-sm text-slate-600 line-clamp-2">{{ Str::limit(strip_tags($row['description']), 200) }}</div>
                </div>
            @endforeach
        </div>

        {{-- Confirm --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5 flex items-center justify-between">
            <div>
                <div class="font-semibold text-indigo-800">Ready to import {{ count($valid) }} question(s)</div>
                @if(count($invalid) > 0)
                    <div class="text-sm text-indigo-600 mt-0.5">{{ count($invalid) }} row(s) with errors will be skipped.</div>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.questions.bulk.import') }}" class="btn-secondary">Cancel</a>
                <form method="POST" action="{{ route('admin.questions.bulk.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="course_offering_id" value="{{ $offering->id }}">
                    <input type="hidden" name="parsed_rows" value="{{ base64_encode(json_encode($valid)) }}">
                    <button type="submit" class="btn-primary"><i class="fas fa-file-import mr-1.5"></i> Confirm Import</button>
                </form>
            </div>
        </div>
    @else
        <div class="text-center py-12 text-slate-400">
            <i class="fas fa-exclamation-circle text-3xl mb-3"></i>
            <div>No valid rows to import. Fix the errors and try again.</div>
            <a href="{{ route('admin.questions.bulk.import') }}" class="btn-secondary mt-4 inline-block">Try Again</a>
        </div>
    @endif
@endsection
