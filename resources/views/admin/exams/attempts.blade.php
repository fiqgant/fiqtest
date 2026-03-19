@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Exam Attempts" :subtitle="$exam->title">
        <a href="{{ route('admin.exams.monitor', $exam) }}" class="btn-secondary"><i class="fas fa-satellite-dish"></i> Live Monitor</a>
        <a href="{{ route('admin.exams.export', $exam) }}" class="btn-secondary"><i class="fas fa-file-excel"></i> Export Excel</a>
        <a href="{{ route('admin.exams.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Exams</a>
    </x-admin.page-header>

    {{-- Score distribution chart --}}
    @if(array_sum($buckets) > 0)
    <div class="card p-5 mb-5">
        <div class="flex items-center justify-between mb-4">
            <div class="font-semibold text-slate-700 text-sm">Score Distribution</div>
            <div class="text-xs text-slate-500">
                {{ array_sum($buckets) }} submitted &bull;
                Avg: <span class="font-bold text-slate-700">{{ $avgPct }}%</span>
            </div>
        </div>
        <div class="flex items-end gap-3 h-32">
            @foreach($buckets as $label => $count)
            @php
                $height = $maxBucket > 0 ? round($count / $maxBucket * 100) : 0;
                $color  = match(true) {
                    str_starts_with($label, '9'), str_starts_with($label, '8') => 'bg-emerald-500',
                    str_starts_with($label, '7') => 'bg-blue-500',
                    str_starts_with($label, '6') => 'bg-amber-400',
                    default => 'bg-rose-400',
                };
            @endphp
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="text-xs font-bold text-slate-600">{{ $count > 0 ? $count : '' }}</div>
                <div class="w-full rounded-t-md {{ $color }} transition-all" style="height: {{ max(4, $height) }}%"></div>
                <div class="text-xs text-slate-400 whitespace-nowrap">{{ $label }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Started</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Tab Switches</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $attempt)
                    <tr>
                        <td>
                            <div class="font-semibold text-slate-800">{{ $attempt->student->name }}</div>
                            <div class="font-mono text-xs text-slate-400">{{ $attempt->student->nim }}</div>
                        </td>
                        <td class="font-mono text-xs text-slate-500">{{ optional($attempt->started_at)->format('d M H:i') ?? '—' }}</td>
                        <td class="font-mono text-xs text-slate-500">{{ optional($attempt->submitted_at)->format('d M H:i') ?? '—' }}</td>
                        <td>
                            <x-admin.status-badge :value="$attempt->status" />
                            @if($attempt->is_disqualified)
                                <div class="mt-1 text-xs text-rose-600 font-medium">{{ $attempt->disqualification_reason }}</div>
                            @endif
                        </td>
                        <td>
                            @php $pct = $attempt->max_score > 0 ? round($attempt->total_score / $attempt->max_score * 100) : 0; @endphp
                            <div class="font-bold text-slate-800">{{ $attempt->total_score }} <span class="text-slate-400 font-normal text-xs">/ {{ $attempt->max_score }}</span></div>
                            <div class="w-24 bg-slate-100 rounded-full h-1.5 mt-1">
                                <div class="h-1.5 rounded-full {{ $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-rose-400') }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                        <td>
                            @if($attempt->tab_switch_count > 0)
                                <span class="font-semibold {{ $attempt->tab_switch_count >= 3 ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ $attempt->tab_switch_count }}×
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.exams.attempts.detail', [$exam, $attempt]) }}" class="btn-secondary text-xs py-1 px-3">
                                <i class="fas fa-eye"></i> Review
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-pencil-alt text-2xl"></i><span>No attempts yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $attempts->links() }}</div>
@endsection
