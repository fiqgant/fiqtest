@extends('admin.layouts.app')

@section('content')
    @php
        $pct = $attempt->max_score > 0 ? round($attempt->total_score / $attempt->max_score * 100) : 0;
    @endphp

    <x-admin.page-header
        :title="$attempt->student->name . ' — ' . $exam->title"
        :subtitle="$attempt->student->nim . ' · Submitted ' . (optional($attempt->submitted_at)->format('d M Y H:i') ?? 'Not submitted')"
    >
        <a href="{{ route('admin.exams.attempts', $exam) }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Attempts</a>
    </x-admin.page-header>

    {{-- Summary bar --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Status</div>
            <x-admin.status-badge :value="$attempt->status" />
            @if($attempt->is_disqualified)
                <div class="mt-1 text-xs text-rose-600 font-medium">{{ $attempt->disqualification_reason }}</div>
            @endif
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Score</div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $attempt->total_score ?? '—' }}</div>
            <div class="text-xs text-slate-400">/ {{ $attempt->max_score ?? '—' }} pts</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Percentage</div>
            <div class="text-2xl font-extrabold {{ $pct >= 75 ? 'text-emerald-600' : ($pct >= 50 ? 'text-amber-500' : 'text-rose-500') }}">{{ $pct }}%</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Questions</div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $attempt->attemptQuestions->count() }}</div>
        </div>
    </div>

    {{-- Per-question review --}}
    @foreach($attempt->attemptQuestions as $aq)
        @php
            $question = $aq->question;
            $qPct = $aq->max_score > 0 ? round($aq->score / $aq->max_score * 100) : 0;
            $finalSubmission = $aq->submissions->first();
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-5 overflow-hidden">

            {{-- Question header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase tracking-widest
                        {{ $question->difficulty === 'easy' ? 'text-emerald-600' : ($question->difficulty === 'medium' ? 'text-amber-500' : 'text-rose-500') }}">
                        {{ ucfirst($question->difficulty) }}
                    </span>
                    <span class="font-semibold text-slate-800">{{ $question->title }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    @if($aq->hint_used_count > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">
                            <i class="fas fa-lightbulb text-[10px]"></i>
                            Hint used {{ $aq->hint_used_count }}×
                        </span>
                    @endif
                    <span class="font-bold {{ $qPct >= 75 ? 'text-emerald-600' : ($qPct >= 50 ? 'text-amber-500' : 'text-rose-500') }}">
                        {{ $aq->score ?? 0 }} / {{ $aq->max_score }} pts
                    </span>
                </div>
            </div>

            <div class="p-5 space-y-5">

                {{-- Hint (shown if student used it) --}}
                @if($aq->hint_used_count > 0 && !empty($question->hint))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <div class="flex items-center gap-2 text-amber-700 font-semibold text-sm mb-2">
                            <i class="fas fa-lightbulb"></i>
                            Hint (used {{ $aq->hint_used_count }}×)
                        </div>
                        <div class="text-sm text-amber-900 prose prose-sm max-w-none hint-content">{{ $question->hint }}</div>
                    </div>
                @endif

                {{-- Reference solution --}}
                @if(!empty($question->reference_solution))
                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Reference Solution</div>
                        <pre class="bg-indigo-950 text-indigo-100 rounded-xl p-4 text-sm font-mono overflow-x-auto whitespace-pre-wrap break-all">{{ $question->reference_solution }}</pre>
                    </div>
                @endif

                {{-- Student's code --}}
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Student's Answer</div>
                    @if($aq->code)
                        <pre class="bg-gray-900 text-gray-100 rounded-xl p-4 text-sm font-mono overflow-x-auto whitespace-pre-wrap break-all">{{ $aq->code }}</pre>
                    @else
                        <div class="text-sm text-slate-400 italic">No code submitted.</div>
                    @endif
                </div>

                {{-- Final submission output --}}
                @if($finalSubmission)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($finalSubmission->output)
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Output</div>
                                <pre class="bg-slate-900 text-green-300 rounded-xl p-3 text-xs font-mono overflow-x-auto whitespace-pre-wrap">{{ $finalSubmission->output }}</pre>
                            </div>
                        @endif
                        @if($finalSubmission->error)
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Error</div>
                                <pre class="bg-slate-900 text-rose-300 rounded-xl p-3 text-xs font-mono overflow-x-auto whitespace-pre-wrap">{{ $finalSubmission->error }}</pre>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-400">
                        <span><i class="fas fa-check-circle mr-1 {{ $aq->passed_tests === $aq->total_tests ? 'text-emerald-500' : 'text-rose-400' }}"></i>{{ $aq->passed_tests ?? 0 }}/{{ $aq->total_tests ?? 0 }} test cases passed</span>
                        @if($finalSubmission->execution_time_ms)
                            <span><i class="fas fa-clock mr-1"></i>{{ $finalSubmission->execution_time_ms }}ms</span>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    @endforeach
@endsection
