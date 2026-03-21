<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result – {{ $exam->title }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/js/all.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        body {
            background: #0a0a0a;
            min-height: 100vh;
        }

        .orb { position: fixed; border-radius: 50%; pointer-events: none; filter: blur(90px); will-change: transform; }
        .orb-1 { top: -10%; right: -5%; width: 55vw; height: 55vw; background: radial-gradient(circle, rgba(255,255,255,0.03), transparent 70%); animation: orbFloat 10s ease-in-out infinite; }
        .orb-2 { bottom: -15%; left: -5%; width: 45vw; height: 45vw; background: radial-gradient(circle, rgba(255,255,255,0.02), transparent 70%); animation: orbFloat 13s ease-in-out infinite reverse; }
        .orb-3 { top: 40%; left: 35%; width: 35vw; height: 35vw; background: radial-gradient(circle, rgba(255,255,255,0.015), transparent 70%); animation: orbFloat 17s ease-in-out infinite 2s; }
        @keyframes orbFloat {
            0%,100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(25px,-20px) scale(1.04); }
            66% { transform: translate(-15px,15px) scale(0.97); }
        }

        .glass-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(24px) saturate(160%);
            -webkit-backdrop-filter: blur(24px) saturate(160%);
            border: 1px solid rgba(255,255,255,0.09);
            box-shadow: 0 8px 40px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .glass-banner {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(24px) saturate(160%);
            -webkit-backdrop-filter: blur(24px) saturate(160%);
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 8px 40px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .glass-row {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            transition: background 0.15s, border-color 0.15s;
        }
        .glass-row:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.12); }
        .glass-score {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .pdf-btn {
            background: rgba(99,102,241,0.2);
            border: 1px solid rgba(99,102,241,0.35);
            transition: background 0.2s, border-color 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .pdf-btn:hover {
            background: rgba(99,102,241,0.35);
            border-color: rgba(99,102,241,0.55);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99,102,241,0.3);
        }

        .disq-box {
            background: rgba(239,68,68,0.07);
            border: 1px solid rgba(239,68,68,0.22);
        }

        .diff-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
        }
        .diff-easy { background: rgba(16,185,129,0.15); color: #6ee7b7; }
        .diff-medium { background: rgba(245,158,11,0.15); color: #fcd34d; }
        .diff-hard { background: rgba(239,68,68,0.15); color: #fca5a5; }
    </style>
</head>
<body class="text-white min-h-screen p-4 md:p-8">

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="max-w-2xl mx-auto relative z-10">

        <!-- Header Banner -->
        <div class="glass-banner rounded-2xl px-7 py-6 mb-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10 pointer-events-none" style="background: radial-gradient(circle, white, transparent); transform: translate(30%,-30%)"></div>
            <div class="relative flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 mb-2">
                        <i class="fas fa-chart-bar text-indigo-300 text-xs"></i>
                        <span class="text-xs font-medium text-indigo-200/70 uppercase tracking-widest">Exam Result</span>
                    </div>
                    <h1 class="text-xl font-bold text-white leading-snug truncate">{{ $exam->title }}</h1>
                    @if(isset($attempt->student))
                        <p class="text-sm mt-1" style="color: rgba(199,210,254,0.7)">
                            <i class="fas fa-user text-xs mr-1"></i>
                            {{ $attempt->student->name }}
                            <span class="mx-1.5 opacity-40">&middot;</span>
                            <span class="font-mono">{{ $attempt->student->nim }}</span>
                        </p>
                    @endif
                </div>
                <a href="{{ route('exam.result.pdf', $attempt) }}" target="_blank"
                   class="pdf-btn flex items-center gap-2 text-white text-sm font-medium px-4 py-2.5 rounded-xl flex-shrink-0">
                    <i class="fas fa-download text-xs"></i>
                    Download PDF
                </a>
            </div>
        </div>

        <!-- Disqualification Warning -->
        @if($attempt->is_disqualified)
            <div class="disq-box rounded-xl px-4 py-3 mb-5 flex items-start gap-3">
                <i class="fas fa-triangle-exclamation text-red-400 text-sm mt-0.5 flex-shrink-0"></i>
                <div>
                    <div class="font-semibold text-red-300 text-sm">Attempt failed by exam policy</div>
                    <div class="text-xs mt-0.5" style="color: rgba(252,165,165,0.7)">{{ $attempt->disqualification_reason ?? 'Disqualified due to violation policy.' }}</div>
                </div>
            </div>
        @endif

        <!-- Score Grid -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <!-- Total Score -->
            <div class="glass-score rounded-2xl p-4 text-center">
                <div class="text-xs font-medium uppercase tracking-wider mb-2" style="color: rgba(255,255,255,0.4)">Total Score</div>
                <div class="text-3xl font-bold text-white">{{ number_format((float) $attempt->total_score, 2) }}</div>
            </div>

            <!-- Max Score -->
            <div class="glass-score rounded-2xl p-4 text-center">
                <div class="text-xs font-medium uppercase tracking-wider mb-2" style="color: rgba(255,255,255,0.4)">Max Score</div>
                <div class="text-3xl font-bold text-white">{{ number_format((float) $attempt->max_score, 2) }}</div>
            </div>

            <!-- Percentage -->
            <div class="glass-score rounded-2xl p-4 text-center">
                <div class="text-xs font-medium uppercase tracking-wider mb-2" style="color: rgba(255,255,255,0.4)">Percentage</div>
                @php
                    $pct = (float) $attempt->percentage;
                    $pctColor = $pct >= 60 ? '#34d399' : ($pct >= 40 ? '#fbbf24' : '#f87171');
                @endphp
                <div class="text-3xl font-bold" style="color: {{ $pctColor }}">{{ number_format($pct, 2) }}%</div>
            </div>
        </div>

        <!-- Question List -->
        <div class="glass-card rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-4 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.07)">
                <div class="w-6 h-6 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                    <i class="fas fa-list text-indigo-400 text-xs"></i>
                </div>
                <h2 class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.45)">Question Breakdown</h2>
            </div>

            <div class="space-y-2.5">
                @foreach($attempt->attemptQuestions as $aq)
                    <div class="glass-row rounded-xl p-4 flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-white text-sm truncate">{{ $aq->question->title }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                @php
                                    $diff = strtolower($aq->question->difficulty);
                                    $diffClass = $diff === 'easy' ? 'diff-easy' : ($diff === 'medium' ? 'diff-medium' : 'diff-hard');
                                @endphp
                                <span class="diff-badge {{ $diffClass }}">{{ ucfirst($aq->question->difficulty) }}</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="font-semibold text-white text-sm">
                                {{ number_format((float) $aq->score, 2) }}
                                <span style="color: rgba(255,255,255,0.3)">/</span>
                                {{ number_format((float) $aq->weight, 2) }}
                            </div>
                            <div class="text-xs mt-0.5" style="color: rgba(255,255,255,0.35)">
                                Tests: {{ $aq->passed_tests ?? 0 }} / {{ $aq->total_tests ?? 0 }}
                            </div>
                            <div class="text-xs font-medium mt-0.5 {{ $aq->is_correct ? 'text-emerald-400' : 'text-red-400' }}">
                                <i class="fas {{ $aq->is_correct ? 'fa-check' : 'fa-xmark' }} mr-1"></i>{{ $aq->is_correct ? 'Correct' : 'Incorrect' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="text-center text-xs mt-6" style="color: rgba(255,255,255,0.22)">
            fiqtest &middot; Coding Assessment Platform
        </p>

    </div>
</body>
</html>
