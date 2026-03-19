@extends('admin.layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Dashboard</h2>
            <p class="text-slate-500 text-sm mt-0.5">{{ now()->format('l, d F Y') }}</p>
        </div>
    </div>

    {{-- Alert: pending essay grading --}}
    @if($pendingEssays > 0)
    <div class="mb-5 flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-amber-800 text-sm">
        <i class="fas fa-exclamation-circle text-amber-500 text-lg flex-shrink-0"></i>
        <span><strong>{{ $pendingEssays }} essay answer(s)</strong> are waiting for manual grading.</span>
        <a href="{{ route('admin.exams.index') }}" class="ml-auto text-xs font-semibold text-amber-700 underline hover:no-underline">Go to Exams →</a>
    </div>
    @endif

    {{-- Top stat cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-users text-white text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800 leading-none">{{ $stats['students'] }}</div>
                <div class="text-xs font-semibold text-slate-400 mt-1">Students</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-400 to-violet-600 flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-file-alt text-white text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800 leading-none">{{ $stats['exams'] }}</div>
                <div class="text-xs font-semibold text-slate-400 mt-1">Total Exams</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-database text-white text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800 leading-none">{{ $stats['questions'] }}</div>
                <div class="text-xs font-semibold text-slate-400 mt-1">Questions</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-check-circle text-white text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800 leading-none">{{ $stats['submitted_attempts'] }}</div>
                <div class="text-xs font-semibold text-slate-400 mt-1">Submissions</div>
            </div>
        </div>
    </div>

    {{-- Secondary metrics --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm text-center">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Students Live Now</div>
            <div class="text-4xl font-extrabold {{ $liveCount > 0 ? 'text-indigo-600' : 'text-slate-300' }}">{{ $liveCount }}</div>
            @if($liveCount > 0)
            <div class="flex items-center justify-center gap-1.5 mt-2 text-xs text-emerald-600 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Active
            </div>
            @endif
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm text-center">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Avg Score</div>
            @php $avg = round($avgScore ?? 0, 1); @endphp
            <div class="text-4xl font-extrabold {{ $avg >= 75 ? 'text-emerald-600' : ($avg >= 50 ? 'text-amber-500' : 'text-rose-500') }}">
                {{ $avg > 0 ? $avg . '%' : '—' }}
            </div>
            <div class="text-xs text-slate-400 mt-1">across all graded</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm text-center">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Disqualified</div>
            <div class="text-4xl font-extrabold {{ $disqualifiedCount > 0 ? 'text-rose-500' : 'text-slate-300' }}">{{ $disqualifiedCount }}</div>
            <div class="text-xs text-slate-400 mt-1">total attempts</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Active exams --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <i class="fas fa-satellite-dish text-emerald-600 text-xs"></i>
                    </div>
                    <span class="font-semibold text-slate-700">Exams Open Right Now</span>
                </div>
                <span class="text-xs font-semibold {{ $activeExams->count() > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                    {{ $activeExams->count() }} active
                </span>
            </div>
            @if($activeExams->isEmpty())
                <div class="px-5 py-8 text-center text-slate-400 text-sm">No exams currently open.</div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($activeExams as $exam)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-800 text-sm">{{ $exam->title }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $exam->courseOffering->course->name }} · closes {{ $exam->closes_at->format('H:i') }}</div>
                        </div>
                        <div class="flex items-center gap-4 text-right">
                            <div>
                                <div class="text-lg font-bold text-indigo-600">{{ $exam->active_count }}</div>
                                <div class="text-xs text-slate-400">in progress</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-emerald-600">{{ $exam->submitted_count }}</div>
                                <div class="text-xs text-slate-400">submitted</div>
                            </div>
                            <a href="{{ route('admin.exams.monitor', $exam) }}" class="action-btn action-btn-neutral text-xs">
                                <i class="fas fa-satellite-dish"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Upcoming exams --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                        <i class="fas fa-clock text-amber-600 text-xs"></i>
                    </div>
                    <span class="font-semibold text-slate-700">Upcoming Exams</span>
                </div>
                <a href="{{ route('admin.exams.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View all →</a>
            </div>
            @if($upcomingExams->isEmpty())
                <div class="px-5 py-8 text-center text-slate-400 text-sm">No upcoming exams scheduled.</div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($upcomingExams as $exam)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-800 text-sm">{{ $exam->title }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $exam->courseOffering->course->name }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-mono text-slate-600">{{ $exam->opens_at->format('d M H:i') }}</div>
                            <div class="text-xs text-slate-400">{{ $exam->opens_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent submissions --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-pencil-alt text-indigo-600 text-xs"></i>
                </div>
                <span class="font-semibold text-slate-700">Recent Submissions</span>
            </div>
            <a href="{{ route('admin.exams.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View all →</a>
        </div>
        @if($recentAttempts->isEmpty())
            <div class="px-5 py-10 text-center text-slate-400 text-sm">No submissions yet.</div>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Exam</th>
                    <th>Submitted</th>
                    <th>Score</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentAttempts as $attempt)
                @php $pct = $attempt->max_score > 0 ? round($attempt->total_score / $attempt->max_score * 100) : 0; @endphp
                <tr>
                    <td>
                        <div class="font-semibold text-slate-800">{{ $attempt->student->name }}</div>
                        <div class="font-mono text-xs text-slate-400">{{ $attempt->student->nim }}</div>
                    </td>
                    <td class="text-sm text-slate-600">{{ $attempt->exam->title }}</td>
                    <td class="font-mono text-xs text-slate-500">{{ optional($attempt->submitted_at)->format('d M H:i') ?? '—' }}</td>
                    <td>
                        <div class="font-bold text-slate-800">{{ $attempt->total_score }} <span class="text-slate-400 font-normal text-xs">/ {{ $attempt->max_score }}</span></div>
                        <div class="w-20 bg-slate-100 rounded-full h-1.5 mt-1">
                            <div class="h-1.5 rounded-full {{ $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-rose-400') }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </td>
                    <td>
                        <x-admin.status-badge :value="$attempt->status" />
                        @if($attempt->is_disqualified)
                            <div class="text-xs text-rose-500 mt-0.5 font-medium">Disqualified</div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
@endsection
