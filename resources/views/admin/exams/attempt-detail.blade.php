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

    {{-- Anti-cheat log --}}
    @if($attempt->tab_switch_count > 0 || $attempt->is_disqualified || $attempt->ip_address)
    @php
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($attempt->user_agent);
        $browser    = $agent->browser() ?: '—';
        $browserVer = $agent->version($browser) ?: '';
        $os         = $agent->platform() ?: '—';
        $osVer      = $agent->version($os) ?: '';
        $deviceType = $agent->isTablet() ? 'Tablet' : ($agent->isMobile() ? 'Mobile' : ($agent->isDesktop() ? 'Desktop' : '—'));
        $deviceIcon = $agent->isTablet() ? 'fa-tablet-alt' : ($agent->isMobile() ? 'fa-mobile-alt' : 'fa-desktop');
    @endphp
    <div class="mb-6 bg-white dark:bg-slate-800 rounded-2xl border {{ $attempt->is_disqualified ? 'border-rose-300' : 'border-amber-200' }} p-5 shadow-sm">
        <div class="flex items-center gap-2 mb-4 {{ $attempt->is_disqualified ? 'text-rose-700' : 'text-amber-700' }} font-semibold text-sm">
            <i class="fas fa-shield-alt"></i> Proctoring Log
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">Tab Switches</div>
                <div class="font-bold {{ $attempt->tab_switch_count >= 3 ? 'text-rose-600' : ($attempt->tab_switch_count > 0 ? 'text-amber-600' : 'text-slate-600 dark:text-slate-300') }}">
                    {{ $attempt->tab_switch_count }}×
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">Disqualified</div>
                <div class="font-bold {{ $attempt->is_disqualified ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $attempt->is_disqualified ? 'Yes' : 'No' }}
                </div>
                @if($attempt->disqualification_reason)
                    <div class="text-xs text-rose-600 mt-0.5">{{ $attempt->disqualification_reason }}</div>
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">IP Address</div>
                <div class="font-mono text-slate-700 dark:text-slate-300">{{ $attempt->ip_address ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">Last Activity</div>
                <div class="text-slate-700 dark:text-slate-300">{{ optional($attempt->last_activity_at)->format('d M H:i:s') ?? '—' }}</div>
            </div>
        </div>

        @if($attempt->user_agent)
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">Device</div>
                <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                    <i class="fas {{ $deviceIcon }} text-slate-400 dark:text-slate-500 text-xs"></i>
                    {{ $deviceType }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">Browser</div>
                <div class="text-slate-700 dark:text-slate-300">
                    {{ $browser }}{{ $browserVer ? ' ' . $browserVer : '' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">OS</div>
                <div class="text-slate-700 dark:text-slate-300">
                    {{ $os }}{{ $osVer ? ' ' . $osVer : '' }}
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Copy-paste log --}}
    @if($exam->detect_copy_paste)
    @php $cpLogs = $attempt->copyPasteLogs()->orderBy('logged_at')->get(); @endphp
    @if($cpLogs->isNotEmpty())
    <div class="mb-6 bg-white dark:bg-slate-800 rounded-2xl border border-rose-200 dark:border-rose-800/50 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400 font-semibold text-sm">
                <i class="fas fa-clipboard-list"></i> Copy-Paste Activity Log
            </div>
            <span class="text-xs text-slate-400">{{ $cpLogs->count() }} event(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 dark:text-slate-500 uppercase border-b border-slate-100 dark:border-slate-700">
                        <th class="text-left pb-2 pr-4">Time</th>
                        <th class="text-left pb-2 pr-4">Event</th>
                        <th class="text-left pb-2">Content</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($cpLogs as $log)
                    <tr>
                        <td class="py-2 pr-4 font-mono text-xs text-slate-400 whitespace-nowrap">
                            {{ $log->logged_at->format('H:i:s') }}
                        </td>
                        <td class="py-2 pr-4">
                            @if($log->event_type === 'paste')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 text-xs font-medium">
                                    <i class="fas fa-paste"></i> Paste
                                </span>
                            @elseif($log->event_type === 'copy')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-medium">
                                    <i class="fas fa-copy"></i> Copy
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-medium">
                                    <i class="fas fa-cut"></i> Cut
                                </span>
                            @endif
                        </td>
                        <td class="py-2 text-slate-600 dark:text-slate-300 text-xs font-mono break-all">
                            @if($log->content)
                                <span class="bg-slate-50 dark:bg-slate-700/50 rounded px-1.5 py-0.5 block max-h-16 overflow-hidden">{{ $log->content }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="mb-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 px-5 py-3 shadow-sm text-sm text-slate-400 flex items-center gap-2">
        <i class="fas fa-clipboard-check text-emerald-500"></i> No copy-paste activity detected.
    </div>
    @endif
    @endif

    {{-- Summary bar --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Status</div>
            <x-admin.status-badge :value="$attempt->status" />
            @if($attempt->is_disqualified)
                <div class="mt-1 text-xs text-rose-600 font-medium">{{ $attempt->disqualification_reason }}</div>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Score</div>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ $attempt->total_score ?? '—' }}</div>
            <div class="text-xs text-slate-400 dark:text-slate-500">/ {{ $attempt->max_score ?? '—' }} pts</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Percentage</div>
            <div class="text-2xl font-extrabold {{ $pct >= 75 ? 'text-emerald-600' : ($pct >= 50 ? 'text-amber-500' : 'text-rose-500') }}">{{ $pct }}%</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Questions</div>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ $attempt->attemptQuestions->count() }}</div>
        </div>
    </div>

    {{-- Per-question review --}}
    @foreach($attempt->attemptQuestions as $aq)
        @php
            $question    = $aq->question;
            $typeLabel   = \App\Models\Question::TYPES[$question->type] ?? $question->type;
            $effectiveScore = $aq->effectiveScore();
            $qPct        = $aq->max_score > 0 ? round($effectiveScore / $aq->max_score * 100) : 0;
            $finalSubmission = $aq->submissions->first();
        @endphp

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-5 overflow-hidden">

            {{-- Question header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase tracking-widest
                        {{ $question->difficulty === 'easy' ? 'text-emerald-600' : ($question->difficulty === 'medium' ? 'text-amber-500' : 'text-rose-500') }}">
                        {{ ucfirst($question->difficulty) }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">{{ $typeLabel }}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $question->title }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    @if($aq->hint_used_count > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">
                            <i class="fas fa-lightbulb text-[10px]"></i> Hint used {{ $aq->hint_used_count }}×
                        </span>
                    @endif
                    <span class="font-bold {{ $qPct >= 75 ? 'text-emerald-600' : ($qPct >= 50 ? 'text-amber-500' : 'text-rose-500') }}">
                        {{ $effectiveScore }} / {{ $aq->max_score }} pts
                        @if($question->isManualGraded())
                            <span class="text-xs font-normal text-slate-400">(manual)</span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="p-5 space-y-5">

                {{-- Hint (shown if student used it) --}}
                @if($aq->hint_used_count > 0 && !empty($question->hint))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <div class="flex items-center gap-2 text-amber-700 font-semibold text-sm mb-2">
                            <i class="fas fa-lightbulb"></i> Hint (used {{ $aq->hint_used_count }}×)
                        </div>
                        <div class="text-sm text-amber-900">{{ $question->hint }}</div>
                    </div>
                @endif

                {{-- Reference solution --}}
                @if(!empty($question->reference_solution))
                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Reference Solution</div>
                        <pre class="bg-indigo-950 text-indigo-100 rounded-xl p-4 text-sm font-mono overflow-x-auto whitespace-pre-wrap break-all">{{ $question->reference_solution }}</pre>
                    </div>
                @endif

                {{-- Student answer (type-aware) --}}
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Student's Answer</div>

                    @if($question->isCoding())
                        {{-- Coding --}}
                        @if($aq->code)
                            <pre class="bg-slate-900 text-slate-100 rounded-xl p-4 text-sm font-mono overflow-x-auto whitespace-pre-wrap break-all">{{ $aq->code }}</pre>
                        @else
                            <div class="text-sm text-slate-400 italic">No code submitted.</div>
                        @endif

                        {{-- Final submission output --}}
                        @if($finalSubmission)
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($finalSubmission->output)
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Output</div>
                                        <pre class="bg-slate-900 text-green-300 rounded-xl p-3 text-xs font-mono overflow-x-auto whitespace-pre-wrap">{{ $finalSubmission->output }}</pre>
                                    </div>
                                @endif
                                @if($finalSubmission->error)
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Error</div>
                                        <pre class="bg-slate-900 text-rose-300 rounded-xl p-3 text-xs font-mono overflow-x-auto whitespace-pre-wrap">{{ $finalSubmission->error }}</pre>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2 flex items-center gap-4 text-xs text-slate-400">
                                <span><i class="fas fa-check-circle mr-1 {{ $aq->passed_tests === $aq->total_tests ? 'text-emerald-500' : 'text-rose-400' }}"></i>{{ $aq->passed_tests ?? 0 }}/{{ $aq->total_tests ?? 0 }} test cases passed</span>
                                @if($finalSubmission->execution_time_ms)
                                    <span><i class="fas fa-clock mr-1"></i>{{ $finalSubmission->execution_time_ms }}ms</span>
                                @endif
                            </div>
                        @endif

                    @elseif(in_array($question->type, ['multiple_choice', 'multiple_select']))
                        {{-- MC / MS --}}
                        @php
                            $selectedIds = $aq->student_answer
                                ? array_map('trim', explode(',', $aq->student_answer))
                                : [];
                        @endphp
                        @if($question->options->isEmpty())
                            <div class="text-sm text-slate-400 italic">No options defined.</div>
                        @else
                            <div class="space-y-2">
                                @foreach($question->options as $option)
                                    @php
                                        $isSelected = in_array((string) $option->id, $selectedIds);
                                    @endphp
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                                        {{ $option->is_correct ? 'bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300' : ($isSelected && !$option->is_correct ? 'bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-700 text-rose-800 dark:text-rose-300' : 'bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300') }}">
                                        @if($isSelected)
                                            <i class="fas {{ $option->is_correct ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-rose-500' }} w-4"></i>
                                        @elseif($option->is_correct)
                                            <i class="fas fa-check-circle text-emerald-400 opacity-40 w-4"></i>
                                        @else
                                            <i class="far fa-circle text-slate-400 w-4"></i>
                                        @endif
                                        {{ $option->text }}
                                        @if($option->is_correct)
                                            <span class="ml-auto text-xs font-semibold text-emerald-600">Correct</span>
                                        @endif
                                        @if($isSelected)
                                            <span class="ml-auto text-xs font-semibold {{ $option->is_correct ? 'text-emerald-600' : 'text-rose-500' }}">Student's choice</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    @elseif($question->type === 'true_false')
                        {{-- TF --}}
                        @php
                            $studentTF = $aq->student_answer;
                            $correctTF = $question->true_false_answer;
                        @endphp
                        <div class="flex gap-4 text-sm">
                            <div class="px-4 py-2 rounded-lg border {{ $studentTF === '1' ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 font-semibold text-indigo-800 dark:text-indigo-300' : 'bg-slate-50 dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-slate-400 dark:text-slate-500' }}">
                                True
                                @if($correctTF === true) <i class="fas fa-check text-emerald-500 ml-1"></i> @endif
                                @if($studentTF === '1') <span class="text-xs text-indigo-600 ml-1">(student)</span> @endif
                            </div>
                            <div class="px-4 py-2 rounded-lg border {{ $studentTF === '0' ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 font-semibold text-indigo-800 dark:text-indigo-300' : 'bg-slate-50 dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-slate-400 dark:text-slate-500' }}">
                                False
                                @if($correctTF === false) <i class="fas fa-check text-emerald-500 ml-1"></i> @endif
                                @if($studentTF === '0') <span class="text-xs text-indigo-600 ml-1">(student)</span> @endif
                            </div>
                        </div>
                        @php $tfCorrect = ($studentTF === '1' && $correctTF === true) || ($studentTF === '0' && $correctTF === false); @endphp
                        <div class="mt-2 text-sm {{ $tfCorrect ? 'text-emerald-600' : 'text-rose-500' }}">
                            <i class="fas {{ $tfCorrect ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                            {{ $tfCorrect ? 'Correct' : 'Incorrect' }}
                        </div>

                    @elseif($question->type === 'fill_in_blank')
                        {{-- FITB --}}
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-slate-500">Student answered: </span>
                                <code class="bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-slate-800 dark:text-slate-200">{{ $aq->student_answer ?: '(no answer)' }}</code>
                            </div>
                            <div>
                                <span class="text-slate-500">Correct answer: </span>
                                <code class="bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded">{{ $question->fill_blank_answer }}</code>
                            </div>
                            @php $fitbCorrect = mb_strtolower(trim((string)$aq->student_answer)) === mb_strtolower(trim((string)$question->fill_blank_answer)); @endphp
                            <div class="{{ $fitbCorrect ? 'text-emerald-600' : 'text-rose-500' }}">
                                <i class="fas {{ $fitbCorrect ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                {{ $fitbCorrect ? 'Correct' : 'Incorrect' }}
                            </div>
                        </div>

                    @elseif($question->type === 'essay')
                        {{-- Essay --}}
                        @if($aq->student_answer)
                            <div class="bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl p-4 text-sm text-slate-800 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">{{ $aq->student_answer }}</div>
                        @else
                            <div class="text-sm text-slate-400 italic">No answer submitted.</div>
                        @endif
                    @endif
                </div>

                {{-- Essay: manual grading form --}}
                @if($question->type === 'essay')
                    <div class="rounded-xl border border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 p-4">
                        <div class="text-sm font-semibold text-indigo-700 dark:text-indigo-300 mb-3"><i class="fas fa-edit mr-1.5"></i> Manual Grading</div>
                        @if($aq->manual_score !== null)
                            <div class="text-xs text-indigo-600 mb-3">
                                Currently: <strong>{{ $aq->manual_score }}</strong> / {{ $aq->max_score }} pts
                                @if($aq->manual_feedback)
                                    — Feedback: "{{ Str::limit($aq->manual_feedback, 80) }}"
                                @endif
                            </div>
                        @endif
                        <form method="POST"
                              action="{{ route('admin.exams.attempts.grade-essay', [$exam, $attempt, $aq]) }}">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="form-label text-xs">Score (max {{ $aq->max_score }} pts)</label>
                                    <input type="number" name="manual_score" min="0" max="{{ $aq->max_score }}" step="0.5"
                                           value="{{ old('manual_score', $aq->manual_score) }}"
                                           class="form-input" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="form-label text-xs">Feedback to student <span class="font-normal text-slate-400">(optional)</span></label>
                                    <input type="text" name="manual_feedback"
                                           value="{{ old('manual_feedback', $aq->manual_feedback) }}"
                                           class="form-input" placeholder="Brief feedback…">
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="btn-primary text-sm">
                                    <i class="fas fa-save mr-1.5"></i> Save Grade
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    @endforeach
@endsection
