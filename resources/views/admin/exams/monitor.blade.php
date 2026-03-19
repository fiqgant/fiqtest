@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header
        title="Live Monitor"
        :subtitle="$exam->title"
    >
        <a href="{{ route('admin.exams.attempts', $exam) }}" class="btn-secondary"><i class="fas fa-list"></i> All Attempts</a>
        <a href="{{ route('admin.exams.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </x-admin.page-header>

    {{-- Stats bar --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6" id="stats-bar">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Active Now</div>
            <div class="text-3xl font-extrabold text-indigo-600" id="stat-active">—</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Submitted</div>
            <div class="text-3xl font-extrabold text-emerald-600" id="stat-submitted">—</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Enrolled</div>
            <div class="text-3xl font-extrabold text-slate-800" id="stat-enrolled">—</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Last Updated</div>
            <div class="text-lg font-bold text-slate-600 font-mono" id="stat-updated">—</div>
        </div>
    </div>

    {{-- Active students table --}}
    <div class="card overflow-x-auto">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <span class="font-semibold text-slate-700">Active Students</span>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">Auto-refresh every 10s</span>
                <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-medium" id="refresh-indicator">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Live
                </span>
            </div>
        </div>
        <div id="table-wrapper">
            <table class="data-table" style="min-width:700px">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Started</th>
                        <th>Time Remaining</th>
                        <th>Progress</th>
                        <th>Tab Switches</th>
                        <th>Last Activity</th>
                        <th>IP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="monitor-tbody">
                    <tr><td colspan="8" class="px-5 py-8 text-center text-slate-400">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const dataUrl = '{{ route('admin.exams.monitor.data', $exam) }}';
        let interval;

        function formatTime(seconds) {
            if (seconds <= 0) return '00:00';
            const m = Math.floor(seconds / 60).toString().padStart(2, '0');
            const s = (seconds % 60).toString().padStart(2, '0');
            return m + ':' + s;
        }

        async function refresh() {
            try {
                const res = await fetch(dataUrl);
                const data = await res.json();

                document.getElementById('stat-active').textContent    = data.active.length;
                document.getElementById('stat-submitted').textContent = data.submitted;
                document.getElementById('stat-enrolled').textContent  = data.enrolled;
                document.getElementById('stat-updated').textContent   = data.timestamp;

                const tbody = document.getElementById('monitor-tbody');

                if (data.active.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-10 text-center text-slate-400"><i class="fas fa-users text-2xl mb-2 block"></i>No students currently taking this exam.</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.active.map(s => {
                    const tabClass = s.tab_switches >= 3 ? 'text-rose-600 font-bold' : (s.tab_switches >= 1 ? 'text-amber-600' : 'text-slate-600');
                    const timeClass = s.remaining_sec <= 300 ? 'text-rose-600 font-bold animate-pulse' : 'text-slate-800 font-mono';
                    const disqBadge = s.is_disqualified
                        ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-xs font-medium"><i class="fas fa-ban"></i> Disqualified</span>`
                        : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium"><i class="fas fa-pencil-alt"></i> In Progress</span>`;

                    return `<tr>
                        <td>
                            <div class="font-semibold text-slate-800">${s.name}</div>
                            <div class="font-mono text-xs text-slate-400">${s.nim}</div>
                        </td>
                        <td class="font-mono text-xs text-slate-500">${s.started_at}</td>
                        <td class="${timeClass}">${formatTime(s.remaining_sec)}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-700 font-medium">${s.answered}/${s.total_questions}</span>
                                <div class="w-20 bg-slate-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full bg-indigo-500"
                                         style="width: ${s.total_questions > 0 ? Math.round(s.answered/s.total_questions*100) : 0}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="${tabClass}"><i class="fas fa-exchange-alt mr-1"></i>${s.tab_switches}</td>
                        <td class="text-xs text-slate-500">${s.last_activity}</td>
                        <td class="font-mono text-xs text-slate-400">${s.ip_address ?? '—'}</td>
                        <td>${disqBadge}</td>
                    </tr>`;
                }).join('');

            } catch(e) {
                document.getElementById('stat-updated').textContent = 'Error';
            }
        }

        refresh();
        interval = setInterval(refresh, 10000);

        // Stop refresh when tab hidden to save resources
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(interval);
            } else {
                refresh();
                interval = setInterval(refresh, 10000);
            }
        });
    </script>
@endsection
