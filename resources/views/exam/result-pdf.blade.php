<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }

        .header { background: #4f46e5; color: white; padding: 20px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .header .meta { font-size: 10px; opacity: 0.85; }

        .score-bar { display: table; width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 8px; }
        .score-box { display: table-cell; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; text-align: center; }
        .score-box .label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .score-box .value { font-size: 20px; font-weight: bold; color: #1e293b; }

        .disqualified { background: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; color: #dc2626; font-size: 10px; }

        .question-block { border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 16px; overflow: hidden; }
        .question-header { background: #f1f5f9; padding: 10px 14px; border-bottom: 1px solid #e2e8f0; }
        .question-header .title { font-size: 12px; font-weight: bold; color: #1e293b; }
        .question-header .meta { font-size: 9px; color: #64748b; margin-top: 2px; }
        .question-header .score { float: right; font-size: 13px; font-weight: bold; }
        .score-pass { color: #16a34a; }
        .score-fail { color: #dc2626; }
        .score-manual { color: #6366f1; }

        .section { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
        .section:last-child { border-bottom: none; }
        .section-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 5px; }
        .description { color: #475569; }

        .code-block { background: #1e293b; color: #e2e8f0; padding: 10px 12px; border-radius: 4px; font-family: 'DejaVu Sans Mono', monospace; font-size: 9.5px; white-space: pre-wrap; word-break: break-all; }
        .code-empty { color: #94a3b8; font-style: italic; }

        .answer-text { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 12px; color: #1e293b; white-space: pre-wrap; word-break: break-word; }

        /* MC / MS options */
        .option-row { display: table; width: 100%; margin-bottom: 4px; }
        .option-marker { display: table-cell; width: 20px; }
        .option-text { display: table-cell; color: #475569; }
        .option-correct { color: #16a34a; font-weight: bold; }
        .option-wrong { color: #dc2626; }
        .option-missed { color: #94a3b8; }

        /* TF */
        .tf-row { display: table; width: 100%; }
        .tf-cell { display: table-cell; width: 50%; padding: 6px 10px; border-radius: 4px; text-align: center; font-weight: bold; font-size: 11px; }
        .tf-selected { background: #ede9fe; color: #4f46e5; border: 1px solid #a5b4fc; }
        .tf-unselected { background: #f8fafc; color: #cbd5e1; border: 1px solid #e2e8f0; }
        .tf-correct-mark { color: #16a34a; }

        /* FITB */
        .fitb-row { margin-bottom: 4px; }
        .fitb-label { font-size: 9px; color: #94a3b8; }
        .fitb-value { font-family: 'DejaVu Sans Mono', monospace; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px 8px; display: inline-block; margin-top: 2px; }
        .fitb-correct { border-color: #86efac; background: #f0fdf4; color: #16a34a; }
        .fitb-wrong { border-color: #fca5a5; background: #fef2f2; color: #dc2626; }

        /* Test cases */
        .testcase-row { display: table; width: 100%; margin-bottom: 3px; }
        .testcase-num { display: table-cell; width: 60px; color: #64748b; font-size: 9px; }
        .testcase-badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-pass { background: #dcfce7; color: #16a34a; }
        .badge-fail { background: #fee2e2; color: #dc2626; }

        /* Manual grading */
        .manual-block { background: #eef2ff; border: 1px solid #a5b4fc; border-radius: 4px; padding: 8px 12px; margin-top: 6px; }
        .manual-block .manual-label { font-size: 9px; font-weight: bold; color: #4f46e5; text-transform: uppercase; margin-bottom: 3px; }
        .manual-feedback { color: #3730a3; font-style: italic; margin-top: 3px; }

        .hint-block { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 4px; padding: 8px 12px; color: #92400e; font-size: 10px; }
        .hint-block .hint-label { font-weight: bold; margin-bottom: 3px; }

        .type-badge { display: inline-block; background: #ede9fe; color: #4f46e5; border-radius: 10px; padding: 1px 7px; font-size: 8.5px; font-weight: bold; margin-left: 6px; vertical-align: middle; }

        .footer { text-align: center; font-size: 9px; color: #94a3b8; margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $exam->title }}</h1>
        <div class="meta">
            Student: <strong>{{ $attempt->student->name }}</strong> &nbsp;|&nbsp;
            NIM: <strong>{{ $attempt->student->nim }}</strong> &nbsp;|&nbsp;
            Date: <strong>{{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y, H:i') : '-' }}</strong>
        </div>
    </div>

    @if($attempt->is_disqualified)
    <div class="disqualified">
        <strong>Attempt Disqualified:</strong> {{ $attempt->disqualification_reason ?? 'Exam policy violation.' }}
    </div>
    @endif

    <div class="score-bar">
        <div class="score-box">
            <div class="label">Total Score</div>
            <div class="value">{{ number_format((float) $attempt->total_score, 2) }}</div>
        </div>
        <div class="score-box">
            <div class="label">Max Score</div>
            <div class="value">{{ number_format((float) $attempt->max_score, 2) }}</div>
        </div>
        <div class="score-box">
            <div class="label">Percentage</div>
            <div class="value">{{ number_format((float) $attempt->percentage, 2) }}%</div>
        </div>
        <div class="score-box">
            <div class="label">Questions</div>
            <div class="value">{{ $attempt->attemptQuestions->count() }}</div>
        </div>
    </div>

    @foreach($attempt->attemptQuestions as $i => $aq)
    @php
        $q             = $aq->question;
        $typeLabel     = \App\Models\Question::TYPES[$q->type] ?? $q->type;
        $effectiveScore = $aq->effectiveScore();
        $passed        = (int) ($aq->passed_tests ?? 0);
        $total         = (int) ($aq->total_tests ?? 0);
        $isPass        = $aq->is_correct;
        $isManual      = $q->isManualGraded();
        $scoreClass    = $isManual ? 'score-manual' : ($isPass ? 'score-pass' : 'score-fail');
    @endphp
    <div class="question-block">
        <div class="question-header clearfix">
            <span class="score {{ $scoreClass }}">
                {{ number_format((float) $effectiveScore, 2) }} / {{ number_format((float) $aq->weight, 2) }} pts
                @if($isManual) <span style="font-size:8px;font-weight:normal;">(manual)</span> @endif
            </span>
            <div class="title">
                {{ $i + 1 }}. {{ $q->title }}
                <span class="type-badge">{{ $typeLabel }}</span>
            </div>
            <div class="meta">
                {{ ucfirst($q->difficulty) }}
                @if($total > 0) &nbsp;·&nbsp; Test cases: {{ $passed }}/{{ $total }} passed @endif
            </div>
        </div>

        {{-- Question description --}}
        <div class="section">
            <div class="section-label">Question</div>
            <div class="description">{{ strip_tags($q->description) }}</div>
        </div>

        {{-- Student answer (type-aware) --}}
        <div class="section">
            <div class="section-label">Your Answer</div>

            @if($q->isCoding())
                {{-- Coding --}}
                @if($aq->code)
                    <div class="code-block">{{ $aq->code }}</div>
                @else
                    <div class="code-empty">No code submitted.</div>
                @endif

            @elseif(in_array($q->type, ['multiple_choice', 'multiple_select']))
                {{-- MC / MS --}}
                @php
                    $selectedIds = $aq->student_answer
                        ? array_map('trim', explode(',', $aq->student_answer))
                        : [];
                @endphp
                @foreach($q->options as $opt)
                    @php
                        $isSelected = in_array((string) $opt->id, $selectedIds);
                        $cls = $isSelected
                            ? ($opt->is_correct ? 'option-correct' : 'option-wrong')
                            : ($opt->is_correct ? 'option-missed' : '');
                    @endphp
                    <div class="option-row">
                        <span class="option-marker">
                            @if($isSelected && $opt->is_correct) ✓
                            @elseif($isSelected && !$opt->is_correct) ✗
                            @elseif(!$isSelected && $opt->is_correct) ○
                            @else &nbsp;
                            @endif
                        </span>
                        <span class="option-text {{ $cls }}">{{ $opt->text }}
                            @if($opt->is_correct) <em style="font-size:9px;">(correct)</em> @endif
                        </span>
                    </div>
                @endforeach
                @if(empty($selectedIds))
                    <div class="code-empty">No answer selected.</div>
                @endif

            @elseif($q->type === 'true_false')
                {{-- True / False --}}
                @php
                    $studentTF = $aq->student_answer;
                    $correctTF = $q->true_false_answer; // boolean
                @endphp
                <div class="tf-row">
                    <div class="tf-cell {{ $studentTF === '1' ? 'tf-selected' : 'tf-unselected' }}">
                        True @if($correctTF === true) <span class="tf-correct-mark">✓</span> @endif
                    </div>
                    <div style="display:table-cell;width:12px;"></div>
                    <div class="tf-cell {{ $studentTF === '0' ? 'tf-selected' : 'tf-unselected' }}">
                        False @if($correctTF === false) <span class="tf-correct-mark">✓</span> @endif
                    </div>
                </div>
                @if($studentTF === null || $studentTF === '')
                    <div class="code-empty" style="margin-top:6px;">No answer selected.</div>
                @endif

            @elseif($q->type === 'fill_in_blank')
                {{-- Fill in the Blank --}}
                @php
                    $studentAns = trim((string) ($aq->student_answer ?? ''));
                    $correctAns = trim((string) ($q->fill_blank_answer ?? ''));
                    $fitbOk     = $studentAns !== '' && mb_strtolower($studentAns) === mb_strtolower($correctAns);
                @endphp
                <div class="fitb-row">
                    <div class="fitb-label">Your answer:</div>
                    <div class="fitb-value {{ $studentAns !== '' ? ($fitbOk ? 'fitb-correct' : 'fitb-wrong') : '' }}">
                        {{ $studentAns ?: '(no answer)' }}
                    </div>
                </div>
                <div class="fitb-row">
                    <div class="fitb-label">Correct answer:</div>
                    <div class="fitb-value fitb-correct">{{ $correctAns }}</div>
                </div>

            @elseif($q->type === 'essay')
                {{-- Essay --}}
                @if($aq->student_answer)
                    <div class="answer-text">{{ $aq->student_answer }}</div>
                @else
                    <div class="code-empty">No answer submitted.</div>
                @endif

                @if($aq->manual_score !== null)
                    <div class="manual-block">
                        <div class="manual-label">Grader's Assessment</div>
                        <div>Score: <strong>{{ $aq->manual_score }} / {{ $aq->weight }} pts</strong></div>
                        @if($aq->manual_feedback)
                            <div class="manual-feedback">"{{ $aq->manual_feedback }}"</div>
                        @endif
                    </div>
                @else
                    <div class="manual-block">
                        <div class="manual-label">Pending Manual Grading</div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Test cases (coding only) --}}
        @if($total > 0)
        <div class="section">
            <div class="section-label">Test Cases</div>
            @foreach(range(1, $total) as $tcNum)
            <div class="testcase-row">
                <span class="testcase-num">Test #{{ $tcNum }}</span>
                @if($tcNum <= $passed)
                    <span class="testcase-badge badge-pass">PASSED</span>
                @else
                    <span class="testcase-badge badge-fail">FAILED</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Hint used --}}
        @if($aq->hint_used_count > 0 && $q->hint)
        <div class="section">
            <div class="hint-block">
                <div class="hint-label">Hint used ({{ $aq->hint_used_count }}x):</div>
                {{ $q->hint }}
            </div>
        </div>
        @endif
    </div>
    @endforeach

    <div class="footer">
        Auto-generated &mdash; {{ $exam->title }} &mdash; {{ now()->format('d M Y H:i') }}
    </div>

</body>
</html>
