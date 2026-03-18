@extends('admin.layouts.app')

@section('content')
    @php
        $statConfig = [
            'periods'            => ['icon' => 'fa-calendar-alt', 'color' => 'from-emerald-400 to-emerald-600', 'label' => 'Academic Periods'],
            'courses'            => ['icon' => 'fa-book',          'color' => 'from-amber-400 to-amber-600',    'label' => 'Courses'],
            'offerings'          => ['icon' => 'fa-layer-group',   'color' => 'from-cyan-400 to-cyan-600',      'label' => 'Offerings'],
            'students'           => ['icon' => 'fa-users',         'color' => 'from-rose-400 to-rose-600',      'label' => 'Students'],
            'exams'              => ['icon' => 'fa-file-alt',      'color' => 'from-violet-400 to-violet-600',  'label' => 'Exams'],
            'questions'          => ['icon' => 'fa-database',      'color' => 'from-orange-400 to-orange-600',  'label' => 'Questions'],
            'attempts'           => ['icon' => 'fa-pencil-alt',    'color' => 'from-indigo-400 to-indigo-600',  'label' => 'Attempts'],
            'submitted_attempts' => ['icon' => 'fa-check-circle',  'color' => 'from-teal-400 to-teal-600',     'label' => 'Submitted'],
        ];
    @endphp

    <!-- Page Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Dashboard</h2>
        <p class="text-slate-500 text-sm mt-0.5">Real-time overview of exam operations, submissions, and content inventory.</p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach($stats as $key => $value)
            @php $cfg = $statConfig[$key] ?? ['icon' => 'fa-circle', 'color' => 'from-slate-400 to-slate-500', 'label' => ucfirst($key)]; @endphp
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $cfg['color'] }} flex items-center justify-center shadow-lg flex-shrink-0">
                    <i class="fas {{ $cfg['icon'] }} text-white text-lg"></i>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-slate-800 leading-none">{{ $value }}</div>
                    <div class="text-xs font-semibold text-slate-400 mt-1">{{ $cfg['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Recent Exams -->
    <div class="card overflow-hidden">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center">
                    <i class="fas fa-file-alt text-violet-600 text-xs"></i>
                </div>
                <span class="font-semibold text-slate-700">Recent Exams</span>
            </div>
            <a href="{{ route('admin.exams.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View all →</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course Offering</th>
                    <th>Schedule</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentExams as $exam)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $exam->title }}</td>
                        <td class="text-slate-500 text-sm">
                            {{ $exam->courseOffering->course->name }}
                            <span class="text-slate-400">· {{ $exam->courseOffering->class_name }}</span>
                        </td>
                        <td class="font-mono text-xs text-slate-500">
                            {{ $exam->opens_at->format('d M H:i') }}<br>
                            <span class="text-slate-400">→ {{ $exam->closes_at->format('d M H:i') }}</span>
                        </td>
                        <td><x-admin.status-badge :value="$exam->status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-slate-400">
                            <div class="flex flex-col items-center gap-1">
                                <i class="fas fa-inbox text-3xl mb-1"></i>
                                <p class="font-medium">No exams yet</p>
                                <p class="text-xs">Create your first exam to get started</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
