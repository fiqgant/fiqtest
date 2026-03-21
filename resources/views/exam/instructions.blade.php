<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} – Exam Instructions</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/js/all.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.160.1/build/three.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        body {
            background: #000000;
            min-height: 100vh;
        }

        #bg-canvas { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

        /* Glass card */
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

        /* Component-specific */
        .nim-input {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .nim-input:focus {
            outline: none;
            border-color: rgba(99,102,241,0.7);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.25);
        }
        .nim-input::placeholder { color: rgba(255,255,255,0.2); font-family: 'Inter', sans-serif; letter-spacing: normal; }

        .start-btn {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            transition: all 0.2s ease;
        }
        .start-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(99,102,241,0.45);
        }
        .start-btn:active:not(:disabled) { transform: translateY(0); }

        .countdown-digit {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .pulse-dot { animation: pulse-amber 2s infinite; }
        @keyframes pulse-amber {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(251,191,36,0.5); }
            50% { opacity: 0.6; box-shadow: 0 0 0 4px rgba(251,191,36,0); }
        }

        .divider { border-color: rgba(255,255,255,0.08); }
    </style>
</head>
<body class="text-white min-h-screen flex items-center justify-center p-4">

    <canvas id="bg-canvas"></canvas>

    <div class="max-w-5xl w-full relative z-10" x-data="examEntry()">

        <!-- Exam Header Banner -->
        <div class="glass-banner rounded-2xl px-8 py-7 mb-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-72 h-72 rounded-full opacity-10 pointer-events-none" style="background: radial-gradient(circle, white, transparent); transform: translate(30%, -30%)"></div>
            <div class="relative">
                <div class="flex items-center gap-2 mb-3 flex-wrap">
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
                <p class="text-indigo-200/80 mt-1 text-sm">{{ $exam->courseOffering->course->name }}</p>
            </div>
        </div>

        <!-- Two Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Card Left: Exam Info -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b divider">
                    <div class="w-7 h-7 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                        <i class="fas fa-info-circle text-indigo-400 text-xs"></i>
                    </div>
                    <h2 class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.5)">Exam Info</h2>
                </div>

                <div class="space-y-2.5">
                    <div class="glass-row rounded-xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-clock text-indigo-400 text-xs w-4 text-center"></i>
                            <span class="text-sm" style="color: rgba(255,255,255,0.5)">Duration</span>
                        </div>
                        <span class="font-semibold text-white text-sm">{{ $exam->duration_minutes }} min</span>
                    </div>
                    <div class="glass-row rounded-xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-question-circle text-indigo-400 text-xs w-4 text-center"></i>
                            <span class="text-sm" style="color: rgba(255,255,255,0.5)">Questions</span>
                        </div>
                        <span class="font-semibold text-white text-sm">{{ $exam->easy_count + $exam->medium_count + $exam->hard_count }}</span>
                    </div>
                    <div class="glass-row rounded-xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-calendar-check text-emerald-400 text-xs w-4 text-center"></i>
                            <span class="text-sm" style="color: rgba(255,255,255,0.5)">Opens</span>
                        </div>
                        <span class="font-semibold text-white text-sm">{{ $exam->opens_at->format('M d, H:i') }}</span>
                    </div>
                    <div class="glass-row rounded-xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-calendar-times text-red-400 text-xs w-4 text-center"></i>
                            <span class="text-sm" style="color: rgba(255,255,255,0.5)">Closes</span>
                        </div>
                        <span class="font-semibold text-white text-sm">{{ $exam->closes_at->format('M d, H:i') }}</span>
                    </div>
                    <div class="glass-row rounded-xl p-3">
                        <div class="flex items-center gap-2.5 mb-2.5">
                            <i class="fas fa-chart-bar text-purple-400 text-xs w-4 text-center"></i>
                            <span class="text-sm" style="color: rgba(255,255,255,0.5)">Difficulty</span>
                        </div>
                        <div class="flex items-center gap-4 text-xs">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span style="color: rgba(255,255,255,0.65)">Easy {{ $exam->easy_count }}</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <span style="color: rgba(255,255,255,0.65)">Medium {{ $exam->medium_count }}</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                <span style="color: rgba(255,255,255,0.65)">Hard {{ $exam->hard_count }}</span>
                            </span>
                        </div>
                    </div>
                    @if($exam->description)
                    <div class="glass-row rounded-xl p-3">
                        <div class="flex items-center gap-2.5 mb-1.5">
                            <i class="fas fa-align-left text-xs w-4 text-center" style="color: rgba(255,255,255,0.3)"></i>
                            <span class="text-sm" style="color: rgba(255,255,255,0.5)">Description</span>
                        </div>
                        <p class="text-xs leading-relaxed" style="color: rgba(255,255,255,0.6)">{{ $exam->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card Right: Rules + NIM -->
            <div class="flex flex-col gap-5">

                <!-- Panel: Exam Rules -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3" style="border-bottom: 1px solid rgba(251,191,36,0.15)">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/15 flex items-center justify-center">
                            <i class="fas fa-shield-alt text-amber-400 text-xs"></i>
                        </div>
                        <h2 class="text-xs font-semibold uppercase tracking-widest text-amber-300/70">Exam Rules</h2>
                    </div>

                    <ul class="space-y-2.5 text-sm">
                        @if((int) $exam->max_tab_switches > 0)
                            <li class="flex items-start gap-3 p-3 rounded-xl" style="background: rgba(251,191,36,0.06); border: 1px solid rgba(251,191,36,0.18)">
                                <div class="w-7 h-7 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-window-restore text-amber-400 text-xs"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-amber-200">Tab/App Switch Limit</span>
                                    <p class="mt-0.5 text-xs" style="color: rgba(255,255,255,0.55)">Maximum <strong class="text-white">{{ $exam->max_tab_switches }}</strong> switches allowed. Warning starts at {{ $exam->tab_switch_warning_count ?? 1 }} switch(es).</p>
                                </div>
                            </li>
                        @else
                            <li class="flex items-start gap-3 p-3 rounded-xl" style="background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.18)">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-check text-emerald-400 text-xs"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-emerald-200">Tab Switching Allowed</span>
                                    <p class="mt-0.5 text-xs" style="color: rgba(255,255,255,0.55)">Tab switching is allowed without restrictions.</p>
                                </div>
                            </li>
                        @endif

                        @if((int) $exam->inactivity_limit_seconds > 0)
                            <li class="flex items-start gap-3 p-3 rounded-xl" style="background: rgba(251,191,36,0.06); border: 1px solid rgba(251,191,36,0.18)">
                                <div class="w-7 h-7 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-hourglass-half text-amber-400 text-xs"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-amber-200">Inactivity Limit</span>
                                    <p class="mt-0.5 text-xs" style="color: rgba(255,255,255,0.55)">
                                        Maximum <strong class="text-white">{{ $exam->inactivity_limit_seconds >= 60
                                            ? number_format($exam->inactivity_limit_seconds / 60, 1) . ' minutes'
                                            : $exam->inactivity_limit_seconds . ' seconds' }}</strong>
                                        of inactivity. Warning at {{ $exam->inactivity_warning_seconds ?? 15 }}s remaining.
                                    </p>
                                </div>
                            </li>
                        @else
                            <li class="flex items-start gap-3 p-3 rounded-xl" style="background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.18)">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-check text-emerald-400 text-xs"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-emerald-200">No Inactivity Limit</span>
                                    <p class="mt-0.5 text-xs" style="color: rgba(255,255,255,0.55)">No inactivity limit enforced.</p>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Panel: NIM Input -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b divider">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                            <i class="fas fa-id-card text-indigo-400 text-xs"></i>
                        </div>
                        <h2 class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.5)">Enter Your NIM</h2>
                    </div>

                    <!-- Status: Upcoming -->
                    <div x-show="status === 'upcoming'" x-transition class="mb-4">
                        <div class="rounded-xl p-4" style="background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.25)">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-2 h-2 rounded-full bg-amber-400 pulse-dot flex-shrink-0"></div>
                                <span class="text-sm font-semibold" style="color: rgba(199,210,254,0.9)">Exam starts in</span>
                            </div>
                            <div class="flex items-center justify-center gap-2">
                                <template x-for="(segment, i) in countdownSegments" :key="i">
                                    <div class="flex items-center gap-2">
                                        <div class="text-center">
                                            <div class="countdown-digit rounded-xl px-3 py-2 min-w-[52px]">
                                                <span class="text-2xl font-bold font-mono text-white" x-text="segment.value"></span>
                                            </div>
                                            <div class="text-xs mt-1" style="color: rgba(255,255,255,0.35)" x-text="segment.label"></div>
                                        </div>
                                        <span x-show="i < countdownSegments.length - 1" class="text-xl font-bold mb-4" style="color: rgba(99,102,241,0.5)">:</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Status: Closed -->
                    <div x-show="status === 'closed'" x-transition class="mb-4">
                        <div class="rounded-xl p-3 flex items-center gap-3" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25)">
                            <div class="w-8 h-8 rounded-lg bg-red-500/15 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-lock text-red-400 text-sm"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-red-300 text-sm">Exam window has ended</div>
                                <div class="text-xs mt-0.5" style="color: rgba(248,113,113,0.6)">NIM entry is disabled. The exam schedule is closed.</div>
                            </div>
                        </div>
                    </div>

                    <!-- NIM Form -->
                    <form @submit.prevent="verifyAndStart()" x-show="status === 'open'" x-transition>
                        <input
                            type="text"
                            x-model="nim"
                            :disabled="loading"
                            class="nim-input w-full px-4 py-3.5 rounded-xl text-white text-lg font-mono tracking-widest transition-all duration-200 mb-3 disabled:opacity-50"
                            placeholder="e.g. 123456789"
                            autocomplete="off"
                            required>

                        <!-- Error -->
                        <div x-show="error" x-transition class="mb-3 p-3 rounded-xl flex items-center gap-2.5" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25)">
                            <i class="fas fa-circle-exclamation text-red-400 flex-shrink-0 text-sm"></i>
                            <p class="text-red-300 text-sm" x-text="error"></p>
                        </div>

                        <!-- Success -->
                        <div x-show="studentData" x-transition class="mb-3 p-3 rounded-xl flex items-center gap-2.5" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25)">
                            <div class="w-7 h-7 rounded-full bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-emerald-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-emerald-300 text-sm font-medium">Identity verified</p>
                                <p class="text-xs" style="color: rgba(52,211,153,0.65)">Welcome, <span class="font-semibold text-emerald-300" x-text="studentData?.name"></span>! Redirecting...</p>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="loading || !nim"
                            class="start-btn w-full py-3.5 rounded-xl font-semibold text-white disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none">
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

                    <!-- CTA when not open -->
                    <div x-show="status !== 'open'" x-transition>
                        <button disabled class="w-full py-3.5 rounded-xl font-semibold cursor-not-allowed flex items-center justify-center gap-2" style="color: rgba(255,255,255,0.25); background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08)">
                            <i class="fas fa-lock text-xs"></i>
                            <span x-text="status === 'upcoming' ? 'Waiting for exam to start...' : 'Exam is closed'"></span>
                        </button>
                    </div>
                </div>

            </div><!-- end right column -->

        </div><!-- end two cards -->

        <!-- Footer note -->
        <p class="text-center text-xs mt-6 flex items-center justify-center gap-2" style="color: rgba(255,255,255,0.25)">
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

    <script>
    (() => {
        const canvas = document.getElementById('bg-canvas');
        const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setSize(window.innerWidth, window.innerHeight);
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
        camera.position.set(0, 0, 4.5);
        const COUNT = 2500;
        const positions = new Float32Array(COUNT * 3);
        const colors    = new Float32Array(COUNT * 3);
        const palette = [
            new THREE.Color('#6366f1'), new THREE.Color('#818cf8'),
            new THREE.Color('#4f46e5'), new THREE.Color('#a5b4fc'), new THREE.Color('#c7d2fe'),
        ];
        for (let i = 0; i < COUNT; i++) {
            const theta = Math.random() * Math.PI * 2;
            const phi   = Math.acos(2 * Math.random() - 1);
            const r     = 2.5 + Math.random() * 7;
            positions[i*3]   = r * Math.sin(phi) * Math.cos(theta);
            positions[i*3+1] = r * Math.sin(phi) * Math.sin(theta);
            positions[i*3+2] = r * Math.cos(phi);
            const c = palette[Math.floor(Math.random() * palette.length)];
            colors[i*3] = c.r; colors[i*3+1] = c.g; colors[i*3+2] = c.b;
        }
        const pGeo = new THREE.BufferGeometry();
        pGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        pGeo.setAttribute('color',    new THREE.BufferAttribute(colors, 3));
        const points = new THREE.Points(pGeo, new THREE.PointsMaterial({ size: 0.028, vertexColors: true, transparent: true, opacity: 1.0, sizeAttenuation: true }));
        scene.add(points);
        const torus = new THREE.Mesh(new THREE.TorusGeometry(1.6, 0.45, 16, 100), new THREE.MeshBasicMaterial({ color: 0x6366f1, wireframe: true, transparent: true, opacity: 0.22 }));
        scene.add(torus);
        const ico1 = new THREE.Mesh(new THREE.IcosahedronGeometry(0.55, 1), new THREE.MeshBasicMaterial({ color: 0x818cf8, wireframe: true, transparent: true, opacity: 0.45 }));
        ico1.position.set(-2.8, 0.6, -1.5); scene.add(ico1);
        const ico2 = new THREE.Mesh(new THREE.IcosahedronGeometry(0.4, 1), new THREE.MeshBasicMaterial({ color: 0xa5b4fc, wireframe: true, transparent: true, opacity: 0.40 }));
        ico2.position.set(2.8, -0.5, -1); scene.add(ico2);
        const oct = new THREE.Mesh(new THREE.OctahedronGeometry(0.3, 0), new THREE.MeshBasicMaterial({ color: 0xc7d2fe, wireframe: true, transparent: true, opacity: 0.50 }));
        oct.position.set(0.5, 1.5, 0.5); scene.add(oct);
        const mouse = { x: 0, y: 0, tx: 0, ty: 0 };
        window.addEventListener('mousemove', (e) => { mouse.tx = (e.clientX/window.innerWidth-0.5)*2; mouse.ty = (e.clientY/window.innerHeight-0.5)*2; });
        window.addEventListener('resize', () => { camera.aspect = window.innerWidth/window.innerHeight; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth, window.innerHeight); });
        let t = 0;
        const animate = () => {
            requestAnimationFrame(animate); t += 0.004;
            mouse.x += (mouse.tx - mouse.x) * 0.06; mouse.y += (mouse.ty - mouse.y) * 0.06;
            points.rotation.y = t*0.07 + mouse.x*0.12; points.rotation.x = mouse.y*0.07;
            torus.rotation.x = t*0.25 + mouse.y*0.1; torus.rotation.y = t*0.18 + mouse.x*0.25;
            ico1.rotation.x = t*0.5; ico1.rotation.y = t*0.35;
            ico2.rotation.x = t*0.3; ico2.rotation.z = t*0.6;
            oct.rotation.x = t*0.8; oct.rotation.y = t*0.5;
            camera.position.x += (mouse.x*0.35 - camera.position.x)*0.04;
            camera.position.y += (-mouse.y*0.25 - camera.position.y)*0.04;
            camera.lookAt(0,0,0); renderer.render(scene, camera);
        };
        animate();
    })();
    </script>
</body>
</html>
