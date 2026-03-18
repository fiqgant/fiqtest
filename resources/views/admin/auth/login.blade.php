<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fiqtest Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background: radial-gradient(1000px 500px at 10% 10%, rgba(99,102,241,.18), transparent 60%), radial-gradient(900px 450px at 90% 5%, rgba(16,185,129,.13), transparent 65%), #020617;">
    <div class="w-full max-w-md space-y-4">
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-700/60 rounded-2xl p-8 shadow-2xl shadow-indigo-900/20">

            <!-- Logo -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-code text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-indigo-300 font-semibold">fiqtest</p>
                    <h1 class="text-xl font-bold text-white leading-none">Admin Console</h1>
                </div>
            </div>

            <p class="text-sm text-slate-400 mb-6">Sign in to manage exams, question bank, and grading.</p>

            @if($errors->any())
                <div class="mb-5 rounded-xl border border-rose-700/50 bg-rose-950/60 px-4 py-3 text-rose-300 text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full rounded-xl bg-slate-800/80 border border-slate-700 px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/30 outline-none transition-all text-sm"
                        placeholder="admin@example.com"
                        required
                        autofocus
                    >
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full rounded-xl bg-slate-800/80 border border-slate-700 px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/30 outline-none transition-all text-sm"
                        placeholder="••••••••"
                        required
                    >
                </div>
                <button
                    type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-400 hover:to-indigo-500 px-4 py-3 font-semibold text-white shadow-lg shadow-indigo-900/40 transition-all duration-200 hover:-translate-y-0.5 mt-2"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </button>
            </form>
        </div>

        <footer class="text-center text-xs text-slate-500">
            &copy; {{ now()->year }} fiqtest — Secure admin access.
        </footer>
    </div>
</body>
</html>
