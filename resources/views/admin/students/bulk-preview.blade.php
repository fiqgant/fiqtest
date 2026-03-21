@extends('admin.layouts.app')

@section('content')
    @php
        $valid   = array_filter($rows, fn($r) => empty($r['errors']));
        $invalid = array_filter($rows, fn($r) => !empty($r['errors']));
    @endphp

    <x-admin.page-header title="Preview Import Students" subtitle="{{ count($valid) }} valid · {{ count($invalid) }} with errors">
        <a href="{{ route('admin.students.bulk.import') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </x-admin.page-header>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Rows</div>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ count($rows) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-emerald-200 dark:border-emerald-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-emerald-500 uppercase tracking-wider mb-1">Will Import</div>
            <div class="text-2xl font-extrabold text-emerald-600">{{ count($valid) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-rose-200 dark:border-rose-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-rose-400 uppercase tracking-wider mb-1">Will Skip</div>
            <div class="text-2xl font-extrabold text-rose-500">{{ count($invalid) }}</div>
        </div>
    </div>

    {{-- Invalid rows --}}
    @if(count($invalid) > 0)
        <div class="mb-5">
            <div class="form-section-title text-rose-600 mb-3"><i class="fas fa-times-circle mr-1.5"></i> Rows with errors</div>
            @foreach($invalid as $row)
                <div class="mb-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-mono text-rose-400">Row {{ $row['row'] }}</span>
                        <span class="font-semibold text-rose-700">{{ $row['name'] ?: '(no name)' }}</span>
                        <span class="text-xs text-rose-500">{{ $row['nim'] ?: '(no nim)' }}</span>
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

    {{-- Valid rows --}}
    @if(count($valid) > 0)
        <div class="form-section-title mb-3"><i class="fas fa-check-circle text-emerald-500 mr-1.5"></i> Students to import</div>
        <div class="card overflow-hidden mb-6">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>NIM</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($valid as $row)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $row['name'] }}</td>
                        <td class="font-mono text-slate-600">{{ $row['nim'] }}</td>
                        <td class="text-slate-400 text-sm">{{ $row['email'] ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-2xl p-5 flex items-center justify-between">
            <div>
                <div class="font-semibold text-indigo-800 dark:text-indigo-300">Ready to import {{ count($valid) }} student(s)</div>
                @if(count($invalid) > 0)
                    <div class="text-sm text-indigo-600 dark:text-indigo-400 mt-0.5">{{ count($invalid) }} row(s) with errors will be skipped.</div>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.students.bulk.import') }}" class="btn-secondary">Cancel</a>
                <form method="POST" action="{{ route('admin.students.bulk.store') }}">
                    @csrf
                    <input type="hidden" name="parsed_rows" value="{{ base64_encode(json_encode($valid)) }}">
                    <button type="submit" class="btn-primary"><i class="fas fa-user-plus mr-1.5"></i> Confirm Import</button>
                </form>
            </div>
        </div>
    @else
        <div class="text-center py-12 text-slate-400">
            <i class="fas fa-exclamation-circle text-3xl mb-3"></i>
            <div>No valid rows to import. Fix the errors and try again.</div>
            <a href="{{ route('admin.students.bulk.import') }}" class="btn-secondary mt-4 inline-block">Try Again</a>
        </div>
    @endif
@endsection
