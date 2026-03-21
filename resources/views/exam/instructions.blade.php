<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} - Exam Instructions</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .gradient-bg {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);
        }
        .card-glow {
            box-shadow: 0 0 0 1px rgba(99,102,241,0.15), 0 25px 50px -12px rgba(0,0,0,0.6);
        }
        .nim-input:focus {
            box-shadow: 0 0 0 3px rgba(99,102,241,0.35);
        }
        .countdown-digit {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(4px);
        }
.start-btn {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            transition: all 0.2s ease;
        }
        .start-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(99,102,241,0.4);
        }
        .start-btn:active:not(:disabled) {
            transform: translateY(0);
        }
        .pulse-dot {
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .info-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            transition: border-color 0.2s;
        }
        .info-card:hover {
            border-color: rgba(255,255,255,0.15);
        }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center p-4">

    <!-- Background decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full opacity-10" style="background: radial-gradient(circle, #6366f1, transparent)"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full opacity-10" style="background: radial-gradient(circle, #4f46e5, transparent)"></div>
    </div>

    <div class="max-w-5xl w-full relative z-10" x-data="examEntry()">

        <!-- Exam Header -->
        <div class="gradient-bg rounded-2xl px-8 py-7 mb-6 relative overflow-hidden card-glow">
            <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent); transform: translate(30%, -30%)"></div>
            <div class="relative">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-white/15 text-indigo-100 px-3 py-1 rounded-full border border-white/20">
                        <i class="fas fa-graduation-cap text-[10px]"></i>
                        {{ $exam->courseOffering->academicPeriod->name }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-white/15 text-indigo-100 px-3 py-1 rounded-full border border-white/20">
                        <i class="fas fa-users text-[10px]"></i>
                        {{ $exam->courseOffering->class_name }}
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-white leading-snug">{{ $exam->title }}</h1>
                <p class="text-indigo-200 mt-1 text-sm">{{ $exam->courseOffering->course->name }}</p>
            </div>
        </div>

        <!-- Two Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Card Kiri: Exam Info -->
                    <div class="rounded-2xl bg-gray-900 card-glow p-6">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-700/50">
                            <div class="w-7 h-7 rounded-lg bg-indigo-900/60 flex items-center justify-center">
                                <i class="fas fa-info-circle text-indigo-400 text-xs"></i>
                            </div>
                            <h2 class="text-sm font-semibold text-gray-200 uppercase tracking-wider">Exam Info</h2>
                        </div>

                        <div class="space-y-3">
                            <div class="info-card rounded-lg p-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-indigo-400 text-xs w-4"></i>
                                    <span class="text-gray-400 text-sm">Duration</span>
                                </div>
                                <span class="font-semibold text-white text-sm">{{ $exam->duration_minutes }} min</span>
                            </div>
                            <div class="info-card rounded-lg p-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-question-circle text-indigo-400 text-xs w-4"></i>
                                    <span class="text-gray-400 text-sm">Questions</span>
                                </div>
                                <span class="font-semibold text-white text-sm">{{ $exam->easy_count + $exam->medium_count + $exam->hard_count }}</span>
                            </div>
                            <div class="info-card rounded-lg p-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-check text-green-400 text-xs w-4"></i>
                                    <span class="text-gray-400 text-sm">Opens</span>
                                </div>
                                <span class="font-semibold text-white text-sm">{{ $exam->opens_at->format('M d, H:i') }}</span>
                            </div>
                            <div class="info-card rounded-lg p-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-times text-red-400 text-xs w-4"></i>
                                    <span class="text-gray-400 text-sm">Closes</span>
                                </div>
                                <span class="font-semibold text-white text-sm">{{ $exam->closes_at->format('M d, H:i') }}</span>
                            </div>
                            <div class="info-card rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-chart-bar text-purple-400 text-xs w-4"></i>
                                    <span class="text-gray-400 text-sm">Difficulty</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-400"></span><span class="text-gray-300">Easy {{ $exam->easy_count }}</span></span>
                                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span><span class="text-gray-300">Medium {{ $exam->medium_count }}</span></span>
                                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span><span class="text-gray-300">Hard {{ $exam->hard_count }}</span></span>
                                </div>
                            </div>
                            @if($exam->description)
                            <div class="info-card rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <i class="fas fa-align-left text-gray-500 text-xs w-4"></i>
                                    <span class="text-gray-400 text-sm">Description</span>
                                </div>
                                <p class="text-gray-300 text-xs leading-relaxed">{{ $exam->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Kanan: Exam Rules + NIM Input -->
                    <div class="flex flex-col gap-5">

                        <!-- Panel: Exam Rules -->
                        <div class="rounded-2xl bg-gray-900 card-glow p-6">
                            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-amber-700/30">
                                <div class="w-7 h-7 rounded-lg bg-amber-900/60 flex items-center justify-center">
                                    <i class="fas fa-shield-alt text-amber-400 text-xs"></i>
                                </div>
                                <h2 class="text-sm font-semibold text-amber-200 uppercase tracking-wider">Exam Rules</h2>
                            </div>

                            <ul class="space-y-3 text-sm">
                                @if((int) $exam->max_tab_switches > 0)
                                    <li class="flex items-start gap-3 p-3 rounded-lg bg-amber-900/20 border border-amber-800/30">
                                        <div class="w-7 h-7 rounded-lg bg-amber-900/60 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <i class="fas fa-window-restore text-amber-400 text-xs"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-amber-200">Tab/App Switch Limit</span>
                                            <p class="text-gray-300 mt-0.5 text-xs">Maximum <strong class="text-white">{{ $exam->max_tab_switches }}</strong> switches allowed. Warning starts at {{ $exam->tab_switch_warning_count ?? 1 }} switch(es).</p>
                                        </div>
                                    </li>
                                @else
                                    <li class="flex items-start gap-3 p-3 rounded-lg bg-emerald-900/20 border border-emerald-800/30">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-900/60 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <i class="fas fa-check text-emerald-400 text-xs"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-emerald-200">Tab Switching Allowed</span>
                                            <p class="text-gray-300 mt-0.5 text-xs">Tab switching is allowed without restrictions.</p>
                                        </div>
                                    </li>
                                @endif

                                @if((int) $exam->inactivity_limit_seconds > 0)
                                    <li class="flex items-start gap-3 p-3 rounded-lg bg-amber-900/20 border border-amber-800/30">
                                        <div class="w-7 h-7 rounded-lg bg-amber-900/60 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <i class="fas fa-hourglass-half text-amber-400 text-xs"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-amber-200">Inactivity Limit</span>
                                            <p class="text-gray-300 mt-0.5 text-xs">
                                                Maximum <strong class="text-white">{{ $exam->inactivity_limit_seconds >= 60
                                                    ? number_format($exam->inactivity_limit_seconds / 60, 1) . ' minutes'
                                                    : $exam->inactivity_limit_seconds . ' seconds' }}</strong>
                                                of inactivity. Warning at {{ $exam->inactivity_warning_seconds ?? 15 }}s remaining.
                                            </p>
                                        </div>
                                    </li>
                                @else
                                    <li class="flex items-start gap-3 p-3 rounded-lg bg-emerald-900/20 border border-emerald-800/30">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-900/60 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <i class="fas fa-check text-emerald-400 text-xs"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-emerald-200">No Inactivity Limit</span>
                                            <p class="text-gray-300 mt-0.5 text-xs">No inactivity limit enforced.</p>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <!-- Panel: NIM Input -->
                        <div class="rounded-2xl bg-gray-900 card-glow p-6">
                            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-700/50">
                                <div class="w-7 h-7 rounded-lg bg-indigo-900/60 flex items-center justify-center">
                                    <i class="fas fa-id-card text-indigo-400 text-xs"></i>
                                </div>
                                <h2 class="text-sm font-semibold text-gray-200 uppercase tracking-wider">Enter Your NIM</h2>
                            </div>

                            <!-- Status: Upcoming -->
                            <div x-show="status === 'upcoming'" x-transition class="mb-4">
                                <div class="rounded-xl border border-indigo-700/40 bg-indigo-950/30 p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-2 h-2 rounded-full bg-amber-400 pulse-dot flex-shrink-0"></div>
                                        <span class="text-sm font-semibold text-indigo-200">Exam starts in</span>
                                    </div>
                                    <div class="flex items-center justify-center gap-2">
                                        <template x-for="(segment, i) in countdownSegments" :key="i">
                                            <div class="flex items-center gap-2">
                                                <div class="text-center">
                                                    <div class="countdown-digit rounded-lg px-3 py-2 min-w-[52px]">
                                                        <span class="text-2xl font-bold font-mono text-white" x-text="segment.value"></span>
                                                    </div>
                                                    <div class="text-gray-500 text-xs mt-1" x-text="segment.label"></div>
                                                </div>
                                                <span x-show="i < countdownSegments.length - 1" class="text-xl font-bold text-indigo-400/60 mb-4">:</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Status: Closed -->
                            <div x-show="status === 'closed'" x-transition class="mb-4">
                                <div class="rounded-xl border border-red-700/40 bg-red-950/20 p-3 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-red-900/60 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-lock text-red-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-red-300 text-sm">Exam window has ended</div>
                                        <div class="text-red-400/70 text-xs mt-0.5">NIM entry is disabled. The exam schedule is closed.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- NIM Form -->
                            <form @submit.prevent="verifyAndStart()" x-show="status === 'open'" x-transition>
                                <input
                                    type="text"
                                    x-model="nim"
                                    :disabled="loading"
                                    class="nim-input w-full px-4 py-3.5 bg-gray-900 border border-gray-600 rounded-xl focus:outline-none focus:border-indigo-500 text-white text-lg font-mono tracking-widest placeholder:text-gray-600 placeholder:font-sans placeholder:tracking-normal transition-all duration-200 mb-3"
                                    placeholder="e.g. 123456789"
                                    autocomplete="off"
                                    required>

                                <!-- Error -->
                                <div x-show="error" x-transition class="mb-3 p-3 bg-red-950/40 border border-red-700/50 rounded-xl flex items-center gap-2.5">
                                    <i class="fas fa-circle-exclamation text-red-400 flex-shrink-0 text-sm"></i>
                                    <p class="text-red-300 text-sm" x-text="error"></p>
                                </div>

                                <!-- Success -->
                                <div x-show="studentData" x-transition class="mb-3 p-3 bg-emerald-950/40 border border-emerald-700/50 rounded-xl flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-emerald-900/60 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-check text-emerald-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-emerald-300 text-sm font-medium">Identity verified</p>
                                        <p class="text-emerald-400/70 text-xs">Welcome, <span class="font-semibold text-emerald-300" x-text="studentData?.name"></span>! Redirecting...</p>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="loading || !nim"
                                    class="start-btn w-full py-3.5 rounded-xl font-semibold text-white disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none">
                                    <span x-show="!loading" class="flex items-center justify-center gap-2">
                                        <i class="fas fa-rocket text-sm"></i>
                                        Start Exam
                                    </span>
                                    <span x-show="loading" class="flex items-center justify-center gap-2">
                                        <i class="fas fa-spinner fa-spin text-sm"></i>
                                        Verifying NIM...
                                    </span>
                                </button>
                            </form>

                            <!-- CTA when not open yet -->
                            <div x-show="status !== 'open'" x-transition>
                                <button disabled class="w-full py-3.5 rounded-xl font-semibold text-gray-500 bg-gray-800/50 border border-gray-700/50 cursor-not-allowed flex items-center justify-center gap-2">
                                    <i class="fas fa-lock text-xs"></i>
                                    <span x-text="status === 'upcoming' ? 'Waiting for exam to start...' : 'Exam is closed'"></span>
                                </button>
                            </div>
                        </div>

                    </div><!-- end kolom kanan -->

        </div><!-- end two cards -->

        <!-- Footer note -->
        <p class="text-center text-gray-600 text-xs mt-5 flex items-center justify-center gap-2">
            <i class="fas fa-wifi"></i>
            Make sure you have a stable internet connection before starting
        </p>
    </div>

    <script>
        function examEntry() {
            return {
                nim: '',
                loading: false,
                error: '',
                studentData: null,
                startsAtMs: Date.parse('{{ $exam->opens_at->toIso8601String() }}'),
                closesAtMs: Date.parse('{{ $exam->closes_at->toIso8601String() }}'),
                status: 'upcoming',
                countdownSegments: [
                    { value: '00', label: 'Hours' },
                    { value: '00', label: 'Minutes' },
                    { value: '00', label: 'Seconds' },
                ],
                timerHandle: null,

                init() {
                    this.evaluateWindowStatus();
                    this.timerHandle = setInterval(() => {
                        this.evaluateWindowStatus();
                    }, 1000);
                },

                evaluateWindowStatus() {
                    const nowMs = Date.now();

                    if (nowMs < this.startsAtMs) {
                        this.status = 'upcoming';
                        const secondsLeft = Math.max(0, Math.floor((this.startsAtMs - nowMs) / 1000));
                        this.updateCountdownSegments(secondsLeft);
                        return;
                    }

                    if (nowMs > this.closesAtMs) {
                        this.status = 'closed';
                        return;
                    }

                    this.status = 'open';
                },

                updateCountdownSegments(totalSeconds) {
                    const hrs = Math.floor(totalSeconds / 3600);
                    const mins = Math.floor((totalSeconds % 3600) / 60);
                    const secs = totalSeconds % 60;
                    this.countdownSegments = [
                        { value: hrs.toString().padStart(2, '0'), label: 'Hours' },
                        { value: mins.toString().padStart(2, '0'), label: 'Minutes' },
                        { value: secs.toString().padStart(2, '0'), label: 'Seconds' },
                    ];
                },

                async verifyAndStart() {
                    this.evaluateWindowStatus();

                    if (this.status !== 'open') {
                        this.error = this.status === 'upcoming'
                            ? 'Exam has not started yet. Please wait for the countdown.'
                            : 'Exam window has already ended.';
                        return;
                    }

                    if (!this.nim) return;

                    this.loading = true;
                    this.error = '';
                    this.studentData = null;

                    try {
                        const response = await fetch('{{ route("exam.verify", $exam->slug) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ nim: this.nim })
                        });

                        const data = await response.json();

                        if (!data.valid) {
                            this.error = data.message;
                            return;
                        }

                        this.studentData = data.student;

                        setTimeout(() => {
                            this.startExam();
                        }, 800);

                    } catch (e) {
                        this.error = 'An error occurred. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async startExam() {
                    try {
                        const response = await fetch('{{ route("exam.start", $exam->slug) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ nim: this.nim })
                        });

                        const data = await response.json();

                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } catch (e) {
                        this.error = 'Failed to start exam. Please try again.';
                    }
                }
            };
        }
    </script>
</body>
</html>
