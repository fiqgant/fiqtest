<!DOCTYPE html>
<html lang="id">
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

        .section { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
        .section:last-child { border-bottom: none; }
        .section-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 5px; }
        .description { color: #475569; }

        .code-block { background: #1e293b; color: #e2e8f0; padding: 10px 12px; border-radius: 4px; font-family: 'DejaVu Sans Mono', monospace; font-size: 9.5px; white-space: pre-wrap; word-break: break-all; }
        .code-empty { color: #94a3b8; font-style: italic; }

        .testcase-row { display: table; width: 100%; margin-bottom: 3px; }
        .testcase-num { display: table-cell; width: 60px; color: #64748b; font-size: 9px; }
        .testcase-badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-pass { background: #dcfce7; color: #16a34a; }
        .badge-fail { background: #fee2e2; color: #dc2626; }
        .badge-hidden { background: #f1f5f9; color: #64748b; }

        .hint-block { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 4px; padding: 8px 12px; color: #92400e; font-size: 10px; }
        .hint-block .hint-label { font-weight: bold; margin-bottom: 3px; }

        .footer { text-align: center; font-size: 9px; color: #94a3b8; margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; }

        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $exam->title }}</h1>
        <div class="meta">
            Mahasiswa: <strong>{{ $attempt->student->name }}</strong> &nbsp;|&nbsp;
            NIM: <strong>{{ $attempt->student->nim }}</strong> &nbsp;|&nbsp;
            Tanggal: <strong>{{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y, H:i') : '-' }}</strong>
        </div>
    </div>

    @if($attempt->is_disqualified)
    <div class="disqualified">
        <strong>Attempt Didiskualifikasi:</strong> {{ $attempt->disqualification_reason ?? 'Pelanggaran kebijakan ujian.' }}
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
            <div class="label">Persentase</div>
            <div class="value">{{ number_format((float) $attempt->percentage, 2) }}%</div>
        </div>
        <div class="score-box">
            <div class="label">Jumlah Soal</div>
            <div class="value">{{ $attempt->attemptQuestions->count() }}</div>
        </div>
    </div>

    @foreach($attempt->attemptQuestions as $i => $aq)
    @php
        $q = $aq->question;
        $passed = (int) ($aq->passed_tests ?? 0);
        $total = (int) ($aq->total_tests ?? 0);
        $isPass = $aq->is_correct;
    @endphp
    <div class="question-block">
        <div class="question-header clearfix">
            <span class="score {{ $isPass ? 'score-pass' : 'score-fail' }}">
                {{ number_format((float) $aq->score, 2) }} / {{ number_format((float) $aq->weight, 2) }} pts
            </span>
            <div class="title">{{ $i + 1 }}. {{ $q->title }}</div>
            <div class="meta">
                {{ ucfirst($q->difficulty) }}
                @if($total > 0) &nbsp;·&nbsp; Test cases: {{ $passed }}/{{ $total }} passed @endif
            </div>
        </div>

        <div class="section">
            <div class="section-label">Soal</div>
            <div class="description">{{ strip_tags($q->description) }}</div>
        </div>

        <div class="section">
            <div class="section-label">Jawaban Kamu</div>
            @if($aq->code)
                <div class="code-block">{{ $aq->code }}</div>
            @else
                <div class="code-empty">Tidak ada kode yang ditulis.</div>
            @endif
        </div>

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

        @if($aq->hint_used_count > 0 && $q->hint)
        <div class="section">
            <div class="hint-block">
                <div class="hint-label">💡 Hint yang kamu gunakan:</div>
                {{ $q->hint }}
            </div>
        </div>
        @endif
    </div>
    @endforeach

    <div class="footer">
        Dokumen ini digenerate otomatis &mdash; {{ $exam->title }} &mdash; {{ now()->format('d M Y H:i') }}
    </div>

</body>
</html>
