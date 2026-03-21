@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header
        :title="'Stats: ' . $question->title"
        subtitle="{{ \App\Models\Question::TYPES[$question->type] ?? $question->type }} · {{ ucfirst($question->difficulty) }}"
    >
        <a href="{{ route('admin.questions.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </x-admin.page-header>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Attempts</div>
            <div class="text-3xl font-extrabold text-slate-800 dark:text-slate-100">{{ $total }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Answered Correctly</div>
            <div class="text-3xl font-extrabold text-emerald-600">{{ $correct }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">% Correct</div>
            <div class="text-3xl font-extrabold {{ $pctCorrect >= 70 ? 'text-emerald-600' : ($pctCorrect >= 40 ? 'text-amber-500' : 'text-rose-500') }}">
                {{ $pctCorrect }}%
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Avg Score</div>
            <div class="text-3xl font-extrabold text-slate-800 dark:text-slate-100">{{ $avgScore }}</div>
            <div class="text-xs text-slate-400 dark:text-slate-500">/ {{ $question->default_weight }} pts</div>
        </div>
    </div>

    @if($total === 0)
        <div class="card p-10 text-center text-slate-400">
            <i class="fas fa-chart-bar text-3xl mb-2"></i>
            <p>No attempts have used this question yet.</p>
        </div>
    @else
        {{-- Per-student table --}}
        <div class="card overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Exam</th>
                        <th>Score</th>
                        <th>Result</th>
                        @if($question->isCoding())
                        <th>Tests Passed</th>
                        @endif
                        <th>Hint Used</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $aq)
                    <tr>
                        <td>
                            <div class="font-semibold text-slate-800">{{ $aq->attempt->student->name }}</div>
                            <div class="font-mono text-xs text-slate-400">{{ $aq->attempt->student->nim }}</div>
                        </td>
                        <td class="text-xs text-slate-500">{{ $aq->attempt->exam->title ?? '—' }}</td>
                        <td class="font-bold text-slate-800">
                            {{ $aq->effectiveScore() }} <span class="text-slate-400 font-normal text-xs">/ {{ $aq->max_score }}</span>
                        </td>
                        <td>
                            @if($aq->is_correct === null)
                                <span class="text-xs text-slate-400">—</span>
                            @elseif($aq->is_correct)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium"><i class="fas fa-check"></i> Correct</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-xs font-medium"><i class="fas fa-times"></i> Wrong</span>
                            @endif
                        </td>
                        @if($question->isCoding())
                        <td class="text-sm text-slate-600">{{ $aq->passed_tests ?? 0 }} / {{ $aq->total_tests ?? 0 }}</td>
                        @endif
                        <td class="text-sm text-slate-600">{{ $aq->hint_used_count > 0 ? $aq->hint_used_count . '×' : '—' }}</td>
                        <td class="font-mono text-xs text-slate-400">{{ optional($aq->attempt->submitted_at)->format('d M H:i') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
