<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'fiqtest Admin' }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    {{-- Apply theme BEFORE render to prevent flash --}}
    <script>
        if (localStorage.getItem('admin-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        panel: '#0f172a',
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a',
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
        /* ── Background ──────────────────────────── */
        .app-bg {
            background:
                radial-gradient(1200px 500px at 0% 0%, rgba(99,102,241,0.07), transparent 65%),
                radial-gradient(900px 450px at 100% 0%, rgba(16,185,129,0.05), transparent 60%),
                #f1f5f9;
        }
        .dark .app-bg {
            background:
                radial-gradient(1200px 500px at 0% 0%, rgba(99,102,241,0.08), transparent 65%),
                radial-gradient(900px 450px at 100% 0%, rgba(16,185,129,0.04), transparent 60%),
                #0a0f1e;
        }

        /* ── Buttons ─────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff; font-size: 0.875rem; font-weight: 600;
            border-radius: 0.75rem; border: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(99,102,241,0.35);
            text-decoration: none; transition: all 0.2s;
        }
        .btn-primary:hover { background: linear-gradient(135deg, #818cf8, #6366f1); box-shadow: 0 6px 20px rgba(99,102,241,0.45); transform: translateY(-1px); color:#fff; }

        .btn-secondary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: #fff; color: #475569; font-size: 0.875rem; font-weight: 600;
            border: 1.5px solid #e2e8f0; border-radius: 0.75rem; cursor: pointer;
            text-decoration: none; transition: all 0.2s;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-1px); color:#334155; }
        .dark .btn-secondary { background: #1e293b; color: #cbd5e1; border-color: #334155; box-shadow: none; }
        .dark .btn-secondary:hover { background: #273549; border-color: #475569; color: #e2e8f0; }

        .btn-danger {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: linear-gradient(135deg, #f43f5e, #e11d48);
            color: #fff; font-size: 0.875rem; font-weight: 600;
            border-radius: 0.75rem; border: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(244,63,94,0.35);
            text-decoration: none; transition: all 0.2s;
        }
        .btn-danger:hover { filter: brightness(1.08); transform: translateY(-1px); color:#fff; }

        .btn-sm { padding: 0.4rem 0.8rem !important; font-size: 0.8rem !important; }

        /* ── Form elements ───────────────────────── */
        .form-label { display: block; font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
        .dark .form-label { color: #cbd5e1; }

        .form-input, .form-select, .form-textarea {
            display: block; width: 100%; padding: 0.6rem 0.875rem;
            background: #fff; border: 1.5px solid #e2e8f0;
            border-radius: 0.625rem; font-size: 0.875rem; color: #1e293b;
            transition: border-color 0.15s, box-shadow 0.15s; outline: none; box-sizing: border-box;
        }
        .form-input::placeholder, .form-textarea::placeholder { color: #94a3b8; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
        .form-textarea { resize: vertical; }
        .dark .form-input, .dark .form-select, .dark .form-textarea { background: #1e293b; border-color: #334155; color: #e2e8f0; }
        .dark .form-input::placeholder, .dark .form-textarea::placeholder { color: #64748b; }
        .dark .form-input:focus, .dark .form-select:focus, .dark .form-textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.2); }

        /* ── Cards ───────────────────────────────── */
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); overflow: hidden; }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; }
        .card-body { padding: 1.25rem; }
        .dark .card { background: #1e293b; border-color: #334155; box-shadow: 0 1px 8px rgba(0,0,0,0.3); }
        .dark .card-header { background: #162032; border-color: #293548; }

        /* ── Tables ──────────────────────────────── */
        .data-table { width: 100%; font-size: 0.875rem; border-collapse: collapse; }
        .data-table thead th {
            padding: 0.75rem 1.25rem; text-align: left; font-size: 0.7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em; color: #64748b;
            background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; white-space: nowrap; vertical-align: middle;
        }
        .data-table thead th:last-child, .data-table tbody td:last-child { text-align: right; }
        .data-table tbody tr { border-top: 1px solid #f1f5f9; transition: background 0.1s; }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table tbody td { padding: 0.875rem 1.25rem; color: #334155; vertical-align: middle; }
        .dark .data-table thead th { background: #162032; color: #4e637a; border-color: #334155; }
        .dark .data-table tbody tr { border-color: #293548; }
        .dark .data-table tbody tr:hover { background: #1e2d40; }
        .dark .data-table tbody td { color: #cbd5e1; }

        /* ── Action buttons ──────────────────────── */
        .action-btn { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.7rem; border-radius: 0.5rem; font-size: 0.72rem; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: all 0.15s; white-space: nowrap; }
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
        .dark .action-btn-primary   { color: #a5b4fc; background: rgba(79,70,229,0.15); }
        .dark .action-btn-primary:hover   { background: rgba(79,70,229,0.28); }
        .dark .action-btn-danger    { color: #fda4af; background: rgba(225,29,72,0.15); }
        .dark .action-btn-danger:hover    { background: rgba(225,29,72,0.28); }
        .dark .action-btn-neutral   { color: #94a3b8; background: #293548; }
        .dark .action-btn-neutral:hover   { background: #334155; }
        .dark .action-btn-success   { color: #6ee7b7; background: rgba(5,150,105,0.15); }
        .dark .action-btn-success:hover   { background: rgba(5,150,105,0.28); }
        .dark .action-btn-warning   { color: #fcd34d; background: rgba(180,83,9,0.15); }
        .dark .action-btn-warning:hover   { background: rgba(180,83,9,0.28); }

        /* ── Form sections ───────────────────────── */
        .form-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .form-section-title { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 1rem; padding-bottom: 0.625rem; border-bottom: 1px solid #f1f5f9; }
        .dark .form-section { background: #1e293b; border-color: #334155; }
        .dark .form-section-title { color: #4e637a; border-color: #293548; }

        /* ── Modal ───────────────────────────────── */
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); z-index: 50; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-content { background: #fff; border-radius: 1rem; box-shadow: 0 25px 50px rgba(0,0,0,0.25); max-width: 28rem; width: 100%; overflow: hidden; animation: modalIn 0.15s ease-out; }
        .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
        .modal-body { padding: 1.25rem 1.5rem; }
        .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem; }
        .dark .modal-content { background: #1e293b; }
        .dark .modal-header { border-color: #334155; }
        .dark .modal-footer { background: #162032; border-color: #334155; }

        /* ── Topbar ──────────────────────────────── */
        .admin-topbar { background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226,232,240,0.8); }
        .dark .admin-topbar { background: rgba(10,15,30,0.92); border-color: #1e293b; }
        .admin-footer { background: rgba(255,255,255,0.7); backdrop-filter: blur(8px); border-top: 1px solid #e2e8f0; position: sticky; bottom: 0; }
        .dark .admin-footer { background: rgba(15,23,42,0.7); border-color: #334155; }

        /* ── Nav links ───────────────────────────── */
        .nav-link {
            display: inline-flex; align-items: center; gap: 0.375rem;
            padding: 0.4rem 0.75rem; border-radius: 0.625rem;
            font-size: 0.8rem; font-weight: 600; color: #475569;
            text-decoration: none; transition: all 0.15s; white-space: nowrap;
        }
        .nav-link:hover { background: #f1f5f9; color: #1e293b; }
        .nav-link.active { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,0.3); }
        .dark .nav-link { color: #94a3b8; }
        .dark .nav-link:hover { background: #1e293b; color: #e2e8f0; }
        .dark .nav-link.active { color: #fff; }

        /* ── Nav dropdown ────────────────────────── */
        .nav-dropdown-wrap { position: relative; }
        .nav-dropdown {
            display: none; position: absolute; top: 100%; left: 0;
            min-width: 13rem; background: transparent;
            padding-top: 6px; z-index: 100;
        }
        .nav-dropdown-inner {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 0.875rem; box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            padding: 0.375rem; animation: dropIn 0.12s ease-out;
        }
        .dark .nav-dropdown-inner { background: #1e293b; border-color: #334155; box-shadow: 0 8px 30px rgba(0,0,0,0.4); }
        .nav-dropdown-wrap:hover .nav-dropdown,
        .nav-dropdown-wrap.open .nav-dropdown { display: block; }
        .nav-dropdown-item {
            display: flex; align-items: center; gap: 0.625rem;
            padding: 0.5rem 0.75rem; border-radius: 0.625rem;
            font-size: 0.8rem; font-weight: 500; color: #475569;
            text-decoration: none; transition: all 0.12s;
        }
        .nav-dropdown-item:hover { background: #f1f5f9; color: #1e293b; }
        .nav-dropdown-item.active { background: #eef2ff; color: #4f46e5; font-weight: 600; }
        .dark .nav-dropdown-item { color: #94a3b8; }
        .dark .nav-dropdown-item:hover { background: #293548; color: #e2e8f0; }
        .dark .nav-dropdown-item.active { background: rgba(99,102,241,0.15); color: #a5b4fc; }

        /* ── Theme toggle ────────────────────────── */
        .theme-toggle {
            width: 2.25rem; height: 2.25rem; border-radius: 0.75rem;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1.5px solid #e2e8f0; background: #f8fafc;
            cursor: pointer; transition: all 0.2s; color: #64748b; font-size: 0.875rem;
        }
        .theme-toggle:hover { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }
        .dark .theme-toggle { background: #1e293b; border-color: #334155; color: #94a3b8; }
        .dark .theme-toggle:hover { background: #273549; border-color: #475569; color: #e2e8f0; }

        /* ── Confirm modal dark ───────────────────── */
        .dark #acm-box { background: #1e293b !important; }
        .dark #acm-header { border-color: #334155 !important; }
        .dark #acm-title { color: #f1f5f9 !important; }
        .dark #acm-message { color: #94a3b8 !important; }
        .dark #acm-footer { background: #162032 !important; border-color: #334155 !important; }

        /* ── Alerts ──────────────────────────────── */
        .dark .alert-success { background: rgba(16,185,129,0.1) !important; border-color: rgba(16,185,129,0.3) !important; color: #6ee7b7 !important; }
        .dark .alert-error   { background: rgba(225,29,72,0.1)  !important; border-color: rgba(225,29,72,0.3)  !important; color: #fda4af !important; }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(-6px); }
            to   { opacity: 1; transform: scale(1)    translateY(0); }
        }
        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="app-bg text-slate-800 dark:text-slate-200 min-h-screen font-sans">

    @php
        $isAcademic    = request()->routeIs('admin.academic-periods.*','admin.courses.*','admin.course-offerings.*','admin.students.*');
        $isAssessment  = request()->routeIs('admin.questions.*','admin.question-tags.*','admin.exams.*','admin.reports.*');
        $isSystem      = request()->routeIs('admin.profile.*','admin.settings.*');
    @endphp

    <!-- ── Topbar ─────────────────────────────────────────────────── -->
    <header class="admin-topbar sticky top-0 z-50">
        <!-- Main row -->
        <div class="flex items-center justify-between h-14 px-4 lg:px-6 gap-3">

            <!-- Left: Logo + Desktop Nav -->
            <div class="flex items-center gap-1 min-w-0">
                <!-- Logo -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 mr-2 flex-shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow shadow-indigo-500/30">
                        <span class="text-white font-bold text-sm leading-none">T</span>
                    </div>
                    <span class="font-bold text-slate-800 dark:text-white text-sm hidden sm:block">fiqtest</span>
                </a>

                <!-- Desktop Nav -->
                <nav class="hidden lg:flex items-center gap-0.5">
                    <a href="{{ route('admin.dashboard') }}"
                       class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large text-xs"></i> Dashboard
                    </a>

                    {{-- Academic dropdown --}}
                    <div class="nav-dropdown-wrap">
                        <button class="nav-link {{ $isAcademic ? 'active' : '' }}">
                            <i class="fas fa-graduation-cap text-xs"></i> Academic
                            <i class="fas fa-chevron-down text-xs opacity-60"></i>
                        </button>
                        <div class="nav-dropdown">
                          <div class="nav-dropdown-inner">
                            <a href="{{ route('admin.academic-periods.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.academic-periods.*') ? 'active' : '' }}">
                                <i class="fas fa-calendar-alt w-4 text-center text-emerald-500"></i> Academic Periods
                            </a>
                            <a href="{{ route('admin.courses.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                                <i class="fas fa-book w-4 text-center text-amber-500"></i> Courses
                            </a>
                            <a href="{{ route('admin.course-offerings.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.course-offerings.*') ? 'active' : '' }}">
                                <i class="fas fa-layer-group w-4 text-center text-cyan-500"></i> Course Offerings
                            </a>
                            <a href="{{ route('admin.students.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                                <i class="fas fa-users w-4 text-center text-rose-500"></i> Students
                            </a>
                          </div>
                        </div>
                    </div>

                    {{-- Assessment dropdown --}}
                    <div class="nav-dropdown-wrap">
                        <button class="nav-link {{ $isAssessment ? 'active' : '' }}">
                            <i class="fas fa-file-alt text-xs"></i> Assessment
                            <i class="fas fa-chevron-down text-xs opacity-60"></i>
                        </button>
                        <div class="nav-dropdown">
                          <div class="nav-dropdown-inner">
                            <a href="{{ route('admin.questions.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                                <i class="fas fa-database w-4 text-center text-orange-500"></i> Question Bank
                            </a>
                            <a href="{{ route('admin.question-tags.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.question-tags.*') ? 'active' : '' }}">
                                <i class="fas fa-tags w-4 text-center text-teal-500"></i> Question Tags
                            </a>
                            <a href="{{ route('admin.exams.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list w-4 text-center text-purple-500"></i> Exams
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar w-4 text-center text-blue-500"></i> Reports
                            </a>
                          </div>
                        </div>
                    </div>

                    {{-- System dropdown --}}
                    <div class="nav-dropdown-wrap">
                        <button class="nav-link {{ $isSystem ? 'active' : '' }}">
                            <i class="fas fa-cog text-xs"></i> System
                            <i class="fas fa-chevron-down text-xs opacity-60"></i>
                        </button>
                        <div class="nav-dropdown">
                          <div class="nav-dropdown-inner">
                            <a href="{{ route('admin.profile.edit') }}" class="nav-dropdown-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                                <i class="fas fa-user w-4 text-center text-slate-400"></i> My Profile
                            </a>
                            <a href="{{ route('admin.settings.judge0.edit') }}" class="nav-dropdown-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                <i class="fas fa-server w-4 text-center text-slate-400"></i> Judge0 Settings
                            </a>
                          </div>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Right: actions -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <div class="text-right mr-1 hidden xl:block">
                    <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ now()->format('Y-m-d H:i') }}</p>
                </div>

                {{-- Theme toggle --}}
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">
                    <i class="fas fa-moon" id="theme-icon-moon"></i>
                    <i class="fas fa-sun hidden" id="theme-icon-sun"></i>
                </button>

                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-slate-700 dark:text-slate-200 text-sm font-medium hidden sm:flex">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-white" style="font-size:0.6rem"></i>
                    </div>
                    <span class="hidden md:block">{{ session('admin_name', 'Admin') }}</span>
                </a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors text-rose-600 dark:text-rose-400 text-sm font-medium border border-rose-100 dark:border-rose-800">
                        <i class="fas fa-sign-out-alt text-xs"></i>
                        <span class="hidden sm:block">Logout</span>
                    </button>
                </form>

                <!-- Mobile hamburger -->
                <button onclick="toggleMobileNav()" class="lg:hidden w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-bars text-sm" id="mobile-menu-icon"></i>
                </button>
            </div>
        </div>

        <!-- Mobile nav panel -->
        <div id="mobile-nav" class="hidden lg:hidden border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-th-large w-4 text-center"></i> Dashboard
            </a>
            <p class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Academic</p>
            <a href="{{ route('admin.academic-periods.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.academic-periods.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-calendar-alt w-4 text-center text-emerald-500"></i> Academic Periods
            </a>
            <a href="{{ route('admin.courses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.courses.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-book w-4 text-center text-amber-500"></i> Courses
            </a>
            <a href="{{ route('admin.course-offerings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.course-offerings.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-layer-group w-4 text-center text-cyan-500"></i> Course Offerings
            </a>
            <a href="{{ route('admin.students.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.students.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-users w-4 text-center text-rose-500"></i> Students
            </a>
            <p class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Assessment</p>
            <a href="{{ route('admin.questions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.questions.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-database w-4 text-center text-orange-500"></i> Question Bank
            </a>
            <a href="{{ route('admin.question-tags.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.question-tags.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-tags w-4 text-center text-teal-500"></i> Question Tags
            </a>
            <a href="{{ route('admin.exams.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.exams.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-clipboard-list w-4 text-center text-purple-500"></i> Exams
            </a>
            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-chart-bar w-4 text-center text-blue-500"></i> Reports
            </a>
            <p class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">System</p>
            <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.profile.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-user w-4 text-center text-slate-400"></i> My Profile
            </a>
            <a href="{{ route('admin.settings.judge0.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <i class="fas fa-server w-4 text-center text-slate-400"></i> Judge0 Settings
            </a>
        </div>
    </header>

    <!-- ── Page Content ────────────────────────────────────────────── -->
    <main class="min-h-[calc(100vh-3.5rem)] flex flex-col">
        <div class="flex-1 p-4 lg:p-8 pb-4">
            @if(session('success'))
                <div class="alert-success mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 shadow-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 shadow-sm">
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

        <footer class="admin-footer px-6 py-3 flex items-center justify-between text-xs text-slate-500 dark:text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-bold text-indigo-600">fiqtest</span>
                <span>|</span>
                <span>Coding Assessment Platform</span>
            </div>
            <span>&copy; {{ now()->year }} All rights reserved</span>
        </footer>
    </main>

    {{-- Confirm Modal --}}
    <div id="admin-confirm-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
        <div id="acm-box" style="background:#fff; border-radius:1rem; box-shadow:0 25px 50px rgba(0,0,0,0.25); max-width:26rem; width:100%; animation:modalIn 0.15s ease-out; overflow:hidden;">
            <div id="acm-header" style="padding:1.25rem 1.5rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:2.25rem; height:2.25rem; border-radius:50%; background:#fee2e2; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas fa-exclamation-triangle" style="color:#ef4444; font-size:0.875rem;"></i>
                    </div>
                    <span id="acm-title" style="font-weight:600; color:#1e293b; font-size:0.95rem;">Confirm</span>
                </div>
                <button onclick="adminModalClose()" style="border:none; background:none; cursor:pointer; color:#94a3b8; padding:0.25rem; border-radius:0.375rem;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="padding:1.25rem 1.5rem;">
                <p id="acm-message" style="color:#475569; font-size:0.875rem; line-height:1.6; margin:0;"></p>
            </div>
            <div id="acm-footer" style="padding:1rem 1.5rem; border-top:1px solid #f1f5f9; background:#f8fafc; display:flex; justify-content:flex-end; gap:0.75rem;">
                <button onclick="adminModalClose()" class="btn-secondary">Cancel</button>
                <button id="acm-ok" onclick="adminModalConfirm()" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>

    <script>
        // ── Mobile nav toggle ────────────────────────────────────────
        function toggleMobileNav() {
            const nav  = document.getElementById('mobile-nav');
            const icon = document.getElementById('mobile-menu-icon');
            const open = nav.classList.toggle('hidden');
            icon.className = open ? 'fas fa-bars text-sm' : 'fas fa-times text-sm';
        }

        // ── Dark mode ────────────────────────────────────────────────
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('theme-icon-moon').classList.toggle('hidden', isDark);
            document.getElementById('theme-icon-sun').classList.toggle('hidden', !isDark);
        }
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
            updateThemeIcon();
        }
        updateThemeIcon();

        // ── Confirm modal ────────────────────────────────────────────
        var _acmCallback = null;
        function adminModalOpen(title, message, okText, cb) {
            document.getElementById('acm-title').textContent   = title   || 'Confirm';
            document.getElementById('acm-message').textContent = message || 'Are you sure?';
            document.getElementById('acm-ok').textContent      = okText  || 'Delete';
            _acmCallback = cb || null;
            var m = document.getElementById('admin-confirm-modal');
            m.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function adminModalClose() {
            document.getElementById('admin-confirm-modal').style.display = 'none';
            document.body.style.overflow = '';
            _acmCallback = null;
        }
        function adminModalConfirm() {
            var cb = _acmCallback;
            adminModalClose();
            if (cb) cb();
        }
        document.getElementById('admin-confirm-modal').addEventListener('click', function(e) {
            if (e.target === this) adminModalClose();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') adminModalClose();
        });

        window.confirmDelete = function(path, message) {
            adminModalOpen('Confirm Delete', message || 'This data will be permanently deleted. Continue?', 'Yes, Delete', function() {
                var token = document.querySelector('meta[name="csrf-token"]');
                var body  = new URLSearchParams({_method: 'DELETE', _token: token ? token.content : ''});
                fetch(path, {method: 'POST', body: body, headers: {'X-Requested-With': 'XMLHttpRequest'}})
                    .then(function(r) { window.location.href = r.url || window.location.pathname; })
                    .catch(function()  { window.location.reload(); });
            });
        };
        window.showConfirm = function(message, cb, opts) {
            opts = opts || {};
            adminModalOpen(opts.title || 'Confirm', message, opts.okText || 'Yes, Continue', cb);
        };
    </script>
</body>
</html>
