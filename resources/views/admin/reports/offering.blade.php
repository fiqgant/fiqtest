@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header
        title="Offering Grade Report"
        :subtitle="$courseOffering->course->name . ' — ' . $courseOffering->academicPeriod->name . ' · Class ' . $courseOffering->class_name"
    >
        <a href="{{ route('admin.reports.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Reports</a>
        <a href="{{ route('admin.reports.offering.export', $courseOffering) }}" class="btn-primary"><i class="fas fa-download"></i> Export CSV</a>
    </x-admin.page-header>

    <div class="mb-5 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800 flex items-start gap-2.5">
        <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
        <span>Each row is a student. Each <strong>Exam N</strong> column shows the average score (%). Click any score cell to view the full exam result detail.</span>
    </div>

    @php
        $statItems = [
            ['label' => 'Total Students', 'value' => $grades->count(),                                                        'color' => 'from-rose-400 to-rose-500',     'icon' => 'fa-users'],
            ['label' => 'Total Exams',    'value' => $exams->count(),                                                         'color' => 'from-purple-400 to-purple-500', 'icon' => 'fa-file-alt'],
            ['label' => 'Average Overall','value' => number_format((float) ($grades->avg('overall_average') ?? 0), 2) . '%',  'color' => 'from-emerald-400 to-emerald-500','icon' => 'fa-chart-line'],
        ];
    @endphp

    <div class="grid grid-cols-3 gap-4 mb-5">
        @foreach($statItems as $s)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $s['color'] }} flex items-center justify-center shadow-md mb-3">
                    <i class="fas {{ $s['icon'] }} text-white text-sm"></i>
                </div>
                <div class="text-2xl font-extrabold text-slate-800 leading-none">{{ $s['value'] }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1.5">{{ $s['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="card overflow-x-auto">
        <table class="data-table min-w-[980px]">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Name</th>
                    @foreach($exams as $exam)
                        <th>
                            <div>Exam {{ $loop->iteration }}</div>
                            <div class="text-[10px] normal-case tracking-normal font-normal text-slate-400">{{ $exam->title }}</div>
                        </th>
                    @endforeach
                    <th>Total</th>
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grades as $row)
                    <tr>
                        <td class="font-mono text-xs text-slate-500">{{ $row['student']['nim'] }}</td>
                        <td class="font-semibold text-slate-800">{{ $row['student']['name'] }}</td>

                        @foreach($exams as $index => $exam)
                            @php $examRow = $row['exams'][$index] ?? null; @endphp
                            <td>
                                @if($examRow && $examRow['status'] !== 'not_started')
                                    @php
                                        $scoreDetailPayload = [
                                            'student_nim' => $row['student']['nim'],
                                            'student_name' => $row['student']['name'],
                                            'exam_title' => $examRow['exam_title'],
                                            'status' => $examRow['status'],
                                            'score' => $examRow['score'],
                                            'max_score' => $examRow['max_score'],
                                            'percentage' => $examRow['percentage'],
                                            'submitted_at' => $examRow['submitted_at'],
                                            'is_disqualified' => $examRow['is_disqualified'],
                                            'disqualification_reason' => $examRow['disqualification_reason'],
                                            'questions' => $examRow['questions'] ?? [],
                                        ];
                                        $pct = (float) $examRow['percentage'];
                                    @endphp
                                    <button
                                        type="button"
                                        class="cursor-pointer rounded-lg border px-2.5 py-1.5 text-left hover:shadow-sm transition-all w-full {{ $pct >= 75 ? 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100' : ($pct >= 50 ? 'border-amber-200 bg-amber-50 hover:bg-amber-100' : 'border-rose-200 bg-rose-50 hover:bg-rose-100') }}"
                                        data-score-detail="{{ base64_encode(json_encode($scoreDetailPayload, JSON_UNESCAPED_UNICODE)) }}"
                                    >
                                        <div class="font-bold {{ $pct >= 75 ? 'text-emerald-700' : ($pct >= 50 ? 'text-amber-700' : 'text-rose-700') }} text-sm">{{ number_format($pct, 2) }}%</div>
                                        <div class="text-xs text-slate-400">{{ number_format((float) $examRow['score'], 2) }} / {{ number_format((float) $examRow['max_score'], 2) }}</div>
                                    </button>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                        @endforeach

                        <td class="font-semibold text-slate-700">
                            {{ number_format((float) $row['total_score'], 2) }}
                            <span class="text-slate-400 font-normal text-xs">/ {{ number_format((float) $row['total_max'], 2) }}</span>
                        </td>
                        <td>
                            @php $oa = (float) $row['overall_average']; @endphp
                            <div class="font-bold {{ $oa >= 75 ? 'text-emerald-600' : ($oa >= 50 ? 'text-amber-600' : 'text-rose-600') }}">
                                {{ number_format($oa, 2) }}%
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 4 + $exams->count() }}" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-inbox text-2xl"></i><span>No grade report data yet for this offering.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Score Detail Modal -->
    <div id="score-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-file-alt text-indigo-600 text-xs"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Exam Score Detail</h3>
                </div>
                <button id="score-detail-close" type="button" class="action-btn action-btn-neutral"><i class="fas fa-times"></i> Close</button>
            </div>
            <div class="space-y-3 px-5 py-5 text-sm text-slate-700 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <div><span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block mb-0.5">Student</span><span id="detail-student" class="font-semibold text-slate-800"></span></div>
                    <div><span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block mb-0.5">Exam</span><span id="detail-exam" class="font-semibold text-slate-800"></span></div>
                    <div><span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block mb-0.5">Status</span><span id="detail-status" class="font-semibold"></span></div>
                    <div><span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block mb-0.5">Average Score</span><span id="detail-percentage" class="font-semibold"></span></div>
                    <div><span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block mb-0.5">Raw Score</span><span id="detail-score" class="font-semibold"></span></div>
                    <div><span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block mb-0.5">Submitted At</span><span id="detail-submitted" class="font-semibold font-mono text-xs"></span></div>
                </div>
                <div id="detail-disqualified-wrap" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                    <div class="font-semibold text-sm mb-0.5"><i class="fas fa-ban mr-1.5"></i>Disqualification Reason</div>
                    <div id="detail-disqualified-reason" class="text-sm"></div>
                </div>

                <hr class="my-1 border-slate-100">

                <div id="questions-container" class="space-y-4"></div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.getElementById('score-detail-modal');
            const closeButton = document.getElementById('score-detail-close');
            const detailStudent = document.getElementById('detail-student');
            const detailExam = document.getElementById('detail-exam');
            const detailStatus = document.getElementById('detail-status');
            const detailPercentage = document.getElementById('detail-percentage');
            const detailScore = document.getElementById('detail-score');
            const detailSubmitted = document.getElementById('detail-submitted');
            const disqualifiedWrap = document.getElementById('detail-disqualified-wrap');
            const disqualifiedReason = document.getElementById('detail-disqualified-reason');
            const questionsContainer = document.getElementById('questions-container');

            if (!modal) {
                return;
            }

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            closeButton?.addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            const renderQuestions = (questions) => {
                if (!questions || questions.length === 0) {
                    return '<div class="text-slate-500 text-sm p-4 flex flex-col items-center gap-1"><i class="fas fa-inbox text-xl text-slate-300"></i><span>No question data available.</span></div>';
                }

                return questions.map((q, idx) => {
                    const difficultyColor = q.difficulty === 'easy' ? 'text-emerald-600 bg-emerald-50' : (q.difficulty === 'medium' ? 'text-amber-600 bg-amber-50' : 'text-rose-600 bg-rose-50');
                    const scoreColor = q.is_correct ? 'text-emerald-600' : 'text-rose-600';
                    const type = q.type || 'coding';

                    const hintHtml = (q.hint_used_count > 0 && q.hint) ? `
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <div class="text-xs font-semibold text-amber-700 uppercase tracking-wider mb-1.5"><i class="fas fa-lightbulb mr-1"></i> Hint (used ${q.hint_used_count}×)</div>
                            <div class="text-sm text-amber-900 whitespace-pre-wrap">${escapeHtml(q.hint)}</div>
                        </div>` : '';

                    const refHtml = q.reference_solution ? `
                        <div>
                            <div class="text-xs text-indigo-600 font-semibold uppercase tracking-wider mb-1.5">Reference Solution</div>
                            <pre class="bg-indigo-950 text-indigo-100 p-3 rounded-lg text-xs overflow-x-auto max-h-48 leading-relaxed">${escapeHtml(q.reference_solution)}</pre>
                        </div>` : '';

                    let answerHtml = '';

                    if (type === 'coding') {
                        let testResultsHtml = '';
                        if (q.visible_test_cases && q.visible_test_cases.length > 0) {
                            testResultsHtml = q.visible_test_cases.map((tc, tcIdx) => `
                                <div class="mt-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
                                    <div class="text-xs text-slate-500 font-semibold mb-1.5">Test Case ${tcIdx + 1}</div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-xs text-slate-400 block mb-0.5">Input</span><code class="text-xs bg-white border border-slate-200 px-2 py-1 rounded block whitespace-pre">${escapeHtml(tc.input || '(none)')}</code></div>
                                        <div><span class="text-xs text-slate-400 block mb-0.5">Expected</span><code class="text-xs bg-white border border-slate-200 px-2 py-1 rounded block whitespace-pre">${escapeHtml(tc.expected_output)}</code></div>
                                    </div>
                                </div>`).join('');
                        }
                        answerHtml = `
                            <div class="text-xs text-slate-400 font-semibold">Language: <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">${escapeHtml(q.language || '-')}</span></div>
                            ${testResultsHtml}
                            ${refHtml}
                            <div>
                                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1.5">Student's Code</div>
                                <pre class="bg-slate-800 text-slate-100 p-3 rounded-lg text-xs overflow-x-auto max-h-48 leading-relaxed">${escapeHtml(q.student_code || '(No code submitted)')}</pre>
                            </div>`;

                    } else if (type === 'multiple_choice' || type === 'multiple_select') {
                        const selectedIds = (q.student_answer || '').split(',').map(s => String(s.trim())).filter(Boolean);
                        const optionsHtml = (q.options || []).map(opt => {
                            const selected = selectedIds.includes(String(opt.id));
                            let cls = 'border-slate-200 bg-white text-slate-700';
                            let badge = '';
                            if (opt.is_correct && selected) { cls = 'border-emerald-400 bg-emerald-50 text-emerald-800'; badge = '<span class="ml-auto text-xs text-emerald-600 font-semibold">✓ Correct</span>'; }
                            else if (opt.is_correct && !selected) { cls = 'border-emerald-200 bg-emerald-50/50 text-emerald-700'; badge = '<span class="ml-auto text-xs text-emerald-500 font-semibold">✓ Key</span>'; }
                            else if (!opt.is_correct && selected) { cls = 'border-rose-400 bg-rose-50 text-rose-800'; badge = '<span class="ml-auto text-xs text-rose-600 font-semibold">✗ Wrong</span>'; }
                            return `<div class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm ${cls}">
                                ${selected ? '<i class="fas fa-check-circle text-xs flex-shrink-0"></i>' : '<i class="far fa-circle text-xs text-slate-300 flex-shrink-0"></i>'}
                                <span>${escapeHtml(opt.text)}</span>${badge}
                            </div>`;
                        }).join('');
                        answerHtml = `
                            ${refHtml}
                            <div>
                                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1.5">Student's Answer</div>
                                ${q.student_answer ? `<div class="space-y-1.5">${optionsHtml}</div>` : '<div class="text-slate-400 text-sm italic">(No answer submitted)</div>'}
                            </div>`;

                    } else if (type === 'true_false') {
                        const ans = q.student_answer;
                        const correct = q.true_false_answer; // true/false/null
                        const renderBtn = (val, label, icon) => {
                            const isStudent = ans === String(val ? 1 : 0);
                            const isCorrect = correct === val;
                            let cls = 'border-slate-200 bg-white text-slate-500';
                            if (isStudent && isCorrect) cls = 'border-emerald-400 bg-emerald-50 text-emerald-700 font-semibold';
                            else if (isStudent && !isCorrect) cls = 'border-rose-400 bg-rose-50 text-rose-700 font-semibold';
                            else if (isCorrect) cls = 'border-emerald-200 bg-emerald-50/50 text-emerald-600';
                            return `<div class="flex items-center gap-2 px-4 py-2.5 rounded-lg border text-sm ${cls}">
                                ${icon} <span>${label}</span>
                                ${isStudent ? '<span class="ml-2 text-xs">(student)</span>' : ''}
                                ${isCorrect ? '<span class="ml-auto text-xs font-semibold">✓ Key</span>' : ''}
                            </div>`;
                        };
                        answerHtml = `
                            ${refHtml}
                            <div>
                                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1.5">Student's Answer</div>
                                ${ans !== '' && ans !== null ? `<div class="space-y-1.5">
                                    ${renderBtn(true, 'True', '<i class="fas fa-check text-xs"></i>')}
                                    ${renderBtn(false, 'False', '<i class="fas fa-times text-xs"></i>')}
                                </div>` : '<div class="text-slate-400 text-sm italic">(No answer submitted)</div>'}
                            </div>`;

                    } else if (type === 'fill_in_blank') {
                        const ans = q.student_answer || '';
                        const correct = q.fill_blank_answer || '';
                        const isRight = ans.trim().toLowerCase() === correct.trim().toLowerCase();
                        answerHtml = `
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1.5">Student's Answer</div>
                                    <div class="px-3 py-2 rounded-lg border text-sm font-mono ${ans ? (isRight ? 'border-emerald-400 bg-emerald-50 text-emerald-800' : 'border-rose-400 bg-rose-50 text-rose-800') : 'border-slate-200 text-slate-400 italic'}">
                                        ${ans ? escapeHtml(ans) : '(No answer submitted)'}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-emerald-600 font-semibold uppercase tracking-wider mb-1.5">Correct Answer</div>
                                    <div class="px-3 py-2 rounded-lg border border-emerald-300 bg-emerald-50 text-sm font-mono text-emerald-800">${escapeHtml(correct || '—')}</div>
                                </div>
                            </div>
                            ${refHtml}`;

                    } else if (type === 'essay') {
                        answerHtml = `
                            ${refHtml}
                            <div>
                                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1.5">Student's Answer</div>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-wrap max-h-48 overflow-y-auto leading-relaxed">
                                    ${q.student_answer ? escapeHtml(q.student_answer) : '<span class="text-slate-400 italic">(No answer submitted)</span>'}
                                </div>
                            </div>`;
                    }

                    const scoreLabel = type === 'coding'
                        ? `${q.passed_tests}/${q.total_tests} passed · ${q.score}/${q.weight} pts`
                        : `${q.score}/${q.weight} pts`;

                    return `
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-700">Q${idx + 1}:</span>
                                    <span class="font-semibold text-slate-800">${escapeHtml(q.title)}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${difficultyColor}">${q.difficulty}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-200 text-slate-600 uppercase tracking-wider">${type.replace('_', ' ')}</span>
                                </div>
                                <div class="text-sm flex items-center gap-2">
                                    ${q.hint_used_count > 0 ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-medium"><i class="fas fa-lightbulb"></i> Hint ${q.hint_used_count}×</span>` : ''}
                                    <span class="${scoreColor} font-semibold text-xs">${scoreLabel}</span>
                                </div>
                            </div>
                            <div class="p-4 space-y-3">
                                ${hintHtml}
                                ${answerHtml}
                            </div>
                        </div>`;
                }).join('');
            };

            const escapeHtml = (str) => {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            };

            document.querySelectorAll('[data-score-detail]').forEach((node) => {
                node.addEventListener('click', () => {
                    const payloadBase64 = node.getAttribute('data-score-detail');
                    if (!payloadBase64) {
                        return;
                    }

                    let payload;
                    try {
                        const payloadText = atob(payloadBase64);
                        payload = JSON.parse(payloadText);
                    } catch (_) {
                        return;
                    }

                    detailStudent.textContent = `${payload.student_nim} — ${payload.student_name}`;
                    detailExam.textContent = payload.exam_title;
                    detailStatus.textContent = payload.status;
                    detailPercentage.textContent = `${Number(payload.percentage || 0).toFixed(2)}%`;
                    detailScore.textContent = `${Number(payload.score || 0).toFixed(2)} / ${Number(payload.max_score || 0).toFixed(2)}`;
                    detailSubmitted.textContent = payload.submitted_at || '—';

                    if (payload.is_disqualified) {
                        disqualifiedWrap.classList.remove('hidden');
                        disqualifiedReason.textContent = payload.disqualification_reason || 'Disqualified by exam policy.';
                    } else {
                        disqualifiedWrap.classList.add('hidden');
                        disqualifiedReason.textContent = '';
                    }

                    questionsContainer.innerHTML = renderQuestions(payload.questions || []);

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });
        })();
    </script>
@endsection
