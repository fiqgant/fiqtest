<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'fiqtest Admin' }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        panel: '#0f172a',
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/js/all.min.js"></script>
    <style>
        /* Background */
        .app-bg {
            background:
                radial-gradient(1200px 500px at 0% 0%, rgba(99,102,241,0.07), transparent 65%),
                radial-gradient(900px 450px at 100% 0%, rgba(16,185,129,0.05), transparent 60%),
                #f1f5f9;
        }

        /* ── Buttons ─────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff; font-size: 0.875rem; font-weight: 600;
            border-radius: 0.75rem; border: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(99,102,241,0.35);
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary:hover { background: linear-gradient(135deg, #818cf8, #6366f1); box-shadow: 0 6px 20px rgba(99,102,241,0.45); transform: translateY(-1px); color:#fff; }

        .btn-secondary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: #fff; color: #475569; font-size: 0.875rem; font-weight: 600;
            border: 1.5px solid #e2e8f0; border-radius: 0.75rem; cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-1px); color:#334155; }

        .btn-danger {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: linear-gradient(135deg, #f43f5e, #e11d48);
            color: #fff; font-size: 0.875rem; font-weight: 600;
            border-radius: 0.75rem; border: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(244,63,94,0.35);
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-danger:hover { filter: brightness(1.08); transform: translateY(-1px); color:#fff; }

        /* ── Form elements ───────────────────────── */
        .form-label {
            display: block; font-size: 0.8125rem; font-weight: 600;
            color: #374151; margin-bottom: 0.375rem;
        }
        .form-input, .form-select, .form-textarea {
            display: block; width: 100%;
            padding: 0.6rem 0.875rem;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.625rem;
            font-size: 0.875rem; color: #1e293b;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
            box-sizing: border-box;
        }
        .form-input::placeholder, .form-textarea::placeholder { color: #94a3b8; }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
        }
        .form-textarea { resize: vertical; }

        /* ── Cards ───────────────────────────────── */
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-body { padding: 1.25rem; }

        /* ── Tables ──────────────────────────────── */
        .data-table { width: 100%; font-size: 0.875rem; border-collapse: collapse; }
        .data-table thead th {
            padding: 0.75rem 1.25rem;
            text-align: left; font-size: 0.7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #64748b; background: #f8fafc;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }
        .data-table tbody tr { border-top: 1px solid #f1f5f9; transition: background 0.1s; }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table tbody td { padding: 0.875rem 1.25rem; color: #334155; vertical-align: middle; }

        /* ── Action pill buttons (inside tables) ─── */
        .action-btn {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.3rem 0.7rem; border-radius: 0.5rem;
            font-size: 0.72rem; font-weight: 600;
            text-decoration: none; cursor: pointer; border: none;
            transition: all 0.15s; white-space: nowrap;
        }
        .action-btn-primary   { color: #4f46e5; background: #eef2ff; }
        .action-btn-primary:hover   { background: #e0e7ff; }
        .action-btn-danger    { color: #e11d48; background: #fff1f2; }
        .action-btn-danger:hover    { background: #ffe4e6; }
        .action-btn-neutral   { color: #475569; background: #f1f5f9; }
        .action-btn-neutral:hover   { background: #e2e8f0; }
        .action-btn-success   { color: #059669; background: #ecfdf5; }
        .action-btn-success:hover   { background: #d1fae5; }
        .action-btn-warning   { color: #b45309; background: #fffbeb; }
        .action-btn-warning:hover   { background: #fef3c7; }

        /* ── Form sections ───────────────────────── */
        .form-section {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.25rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .form-section-title {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.1em; color: #94a3b8;
            margin-bottom: 1rem; padding-bottom: 0.625rem;
            border-bottom: 1px solid #f1f5f9;
        }

        /* ── Modal ───────────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(15,23,42,0.55);
            backdrop-filter: blur(4px); z-index: 50;
            display: flex; align-items: center; justify-content: center; padding: 1rem;
        }
        .modal-content {
            background: #fff; border-radius: 1rem;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            max-width: 32rem; width: 100%; max-height: 90vh; overflow: hidden;
        }
        .modal-header {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-body { padding: 1.25rem 1.5rem; overflow-y: auto; max-height: 60vh; }
        .modal-footer {
            padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;
            background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;
        }
    </style>
</head>
<body class="app-bg text-slate-800 min-h-screen font-sans">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-panel text-slate-100 flex flex-col sticky top-0 h-screen">
            <!-- Logo -->
            <div class="p-5 border-b border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <span class="text-white font-bold text-xl leading-none">T</span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">fiqtest</h1>
                        <p class="text-xs text-slate-400">Admin Console</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-th-large w-5 text-center {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-indigo-400' }}"></i>
                    <span>Dashboard</span>
                </a>
                
                <div class="pt-3 pb-2">
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Academic</p>
                </div>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.academic-periods.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.academic-periods.index') }}">
                    <i class="fas fa-calendar-alt w-5 text-center {{ request()->routeIs('admin.academic-periods.*') ? 'text-white' : 'text-emerald-400' }}"></i>
                    <span>Academic Periods</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.courses.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.courses.index') }}">
                    <i class="fas fa-book w-5 text-center {{ request()->routeIs('admin.courses.*') ? 'text-white' : 'text-amber-400' }}"></i>
                    <span>Courses</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.course-offerings.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.course-offerings.index') }}">
                    <i class="fas fa-layer-group w-5 text-center {{ request()->routeIs('admin.course-offerings.*') ? 'text-white' : 'text-cyan-400' }}"></i>
                    <span>Course Offerings</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.students.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.students.index') }}">
                    <i class="fas fa-users w-5 text-center {{ request()->routeIs('admin.students.*') ? 'text-white' : 'text-rose-400' }}"></i>
                    <span>Students</span>
                </a>

                <div class="pt-3 pb-2">
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Assessment</p>
                </div>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.exams.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.exams.index') }}">
                    <i class="fas fa-file-alt w-5 text-center {{ request()->routeIs('admin.exams.*') ? 'text-white' : 'text-purple-400' }}"></i>
                    <span>Exams</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.questions.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.questions.index') }}">
                    <i class="fas fa-database w-5 text-center {{ request()->routeIs('admin.questions.*') ? 'text-white' : 'text-orange-400' }}"></i>
                    <span>Question Bank</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.reports.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.reports.index') }}">
                    <i class="fas fa-chart-bar w-5 text-center {{ request()->routeIs('admin.reports.*') ? 'text-white' : 'text-blue-400' }}"></i>
                    <span>Reports</span>
                </a>

                <div class="pt-3 pb-2">
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">System</p>
                </div>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.profile.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.profile.edit') }}">
                    <i class="fas fa-user w-5 text-center {{ request()->routeIs('admin.profile.*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>My Profile</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-700/50 {{ request()->routeIs('admin.settings.judge0.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25' : '' }}" href="{{ route('admin.settings.judge0.edit') }}">
                    <i class="fas fa-cog w-5 text-center {{ request()->routeIs('admin.settings.judge0.*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Judge0 Settings</span>
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-slate-700/50">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 w-full text-rose-400 hover:bg-rose-500/10 hover:text-rose-300">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden">
            <!-- Top Bar (sticky) -->
            <div class="bg-white/80 backdrop-blur border-b border-white/70 px-6 py-3 shadow-sm flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">{{ $title ?? 'Admin Workspace' }}</p>
                        <p class="text-sm text-slate-600">Manage coding exams, questions, students, and grading</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-right mr-2">
                            <p class="font-mono text-sm text-slate-600">{{ now()->format('Y-m-d H:i') }}</p>
                            <p class="text-xs text-slate-400">Asia/Jakarta</p>
                        </div>
                        <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors text-slate-700 text-sm font-medium">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                            <span>{{ session('admin_name', 'Admin') }}</span>
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 transition-colors text-rose-600 text-sm font-medium border border-rose-100">
                                <i class="fas fa-sign-out-alt text-xs"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 min-h-0 overflow-y-auto p-6 lg:p-8 pb-12">
                @if(session('success'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 shadow-sm flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 shadow-sm">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="font-semibold">Please fix the following errors:</span>
                        </div>
                        <ul class="list-disc pl-6 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div id="admin-content">
                    @yield('content')
                </div>
            </div>

            <!-- Footer (sticky) -->
            <footer class="flex-shrink-0 border-t border-slate-200 bg-white/70 backdrop-blur px-6 py-3 flex items-center justify-between text-xs text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-indigo-600">fiqtest</span>
                    <span>|</span>
                    <span>Coding Assessment Platform</span>
                </div>
                <span>&copy; {{ now()->year }} All rights reserved</span>
            </footer>
        </main>
    </div>
</body>
</html>
