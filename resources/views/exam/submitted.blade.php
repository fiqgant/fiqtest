<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Submitted – {{ $exam->title }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/js/all.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        body {
            background:
                radial-gradient(ellipse 80% 50% at 0% 0%, rgba(99,102,241,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 100% 100%, rgba(139,92,246,0.14) 0%, transparent 60%),
                #060b18;
            min-height: 100vh;
        }

        .orb { position: fixed; border-radius: 50%; pointer-events: none; filter: blur(90px); will-change: transform; }
        .orb-1 { top: -10%; right: -5%; width: 55vw; height: 55vw; background: radial-gradient(circle, rgba(99,102,241,0.2), transparent 70%); animation: orbFloat 10s ease-in-out infinite; }
        .orb-2 { bottom: -15%; left: -5%; width: 45vw; height: 45vw; background: radial-gradient(circle, rgba(139,92,246,0.15), transparent 70%); animation: orbFloat 13s ease-in-out infinite reverse; }
        .orb-3 { top: 40%; left: 35%; width: 35vw; height: 35vw; background: radial-gradient(circle, rgba(16,185,129,0.07), transparent 70%); animation: orbFloat 17s ease-in-out infinite 2s; }
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

        .icon-ring-success {
            background: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.25);
            box-shadow: 0 0 40px rgba(16,185,129,0.15);
        }
        .icon-ring-disqualified {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.25);
            box-shadow: 0 0 40px rgba(239,68,68,0.15);
        }

        .disq-box {
            background: rgba(239,68,68,0.07);
            border: 1px solid rgba(239,68,68,0.22);
        }

        @keyframes iconPop {
            0% { transform: scale(0.7); opacity: 0; }
            70% { transform: scale(1.08); }
            100% { transform: scale(1); opacity: 1; }
        }
        .icon-pop { animation: iconPop 0.5s cubic-bezier(0.16,1,0.3,1) forwards; }
    </style>
</head>
<body class="text-white min-h-screen flex items-center justify-center p-4">

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="max-w-md w-full relative z-10">
        <div class="glass-card rounded-2xl p-8 text-center">

            <!-- Icon -->
            <div class="flex items-center justify-center mb-6">
                @if($attempt->is_disqualified)
                    <div class="icon-ring-disqualified w-20 h-20 rounded-full flex items-center justify-center icon-pop">
                        <i class="fas fa-xmark text-red-400 text-3xl"></i>
                    </div>
                @else
                    <div class="icon-ring-success w-20 h-20 rounded-full flex items-center justify-center icon-pop">
                        <i class="fas fa-check text-emerald-400 text-3xl"></i>
                    </div>
                @endif
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-white mb-1">
                {{ $attempt->is_disqualified ? 'Attempt Disqualified' : 'Submission Received' }}
            </h1>

            <!-- Exam name -->
            <p class="text-sm mb-5" style="color: rgba(255,255,255,0.5)">{{ $exam->title }}</p>

            <!-- Disqualification box -->
            @if($attempt->is_disqualified)
                <div class="disq-box rounded-xl px-4 py-3 mb-5 text-left">
                    <div class="flex items-start gap-2.5">
                        <i class="fas fa-triangle-exclamation text-red-400 text-sm mt-0.5 flex-shrink-0"></i>
                        <p class="text-red-300 text-sm leading-relaxed">
                            {{ $attempt->disqualification_reason ?? 'Your attempt has been marked as failed by exam policy.' }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Message -->
            <div class="rounded-xl px-4 py-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07)">
                <p class="text-sm leading-relaxed" style="color: rgba(255,255,255,0.45)">
                    Scores are hidden by exam configuration. Please wait for the instructor announcement.
                </p>
            </div>

        </div>

        <p class="text-center text-xs mt-5" style="color: rgba(255,255,255,0.22)">
            fiqtest &middot; Coding Assessment Platform
        </p>
    </div>

</body>
</html>
