@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Student History" :subtitle="$student->nim . ' — ' . $student->name">
        <a href="{{ route('admin.reports.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Reports</a>
    </x-admin.page-header>

    @php
        $statItems = [
            ['label' => 'Total Attempts',   'value' => $history->count(),                                                          'color' => 'from-indigo-400 to-indigo-500', 'text' => 'text-indigo-600',  'icon' => 'fa-pencil-alt'],
            ['label' => 'Disqualified',     'value' => $history->where('is_disqualified', true)->count(),                          'color' => 'from-rose-400 to-rose-500',     'text' => 'text-rose-600',    'icon' => 'fa-ban'],
            ['label' => 'Average Score',    'value' => number_format((float) ($history->avg('percentage') ?? 0), 2) . '%',         'color' => 'from-emerald-400 to-emerald-500','text' => 'text-emerald-600','icon' => 'fa-chart-line'],
        ];
    @endphp

    <div class="grid grid-cols-3 gap-4 mb-6">
        @foreach($statItems as $s)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $s['color'] }} flex items-center justify-center shadow-md mb-3">
                    <i class="fas {{ $s['icon'] }} text-white text-sm"></i>
                </div>
                <div class="text-2xl font-extrabold text-slate-800 leading-none">{{ $s['value'] }}</div>
                <div class="text-xs font-semibold {{ $s['text'] }} uppercase tracking-wider mt-1.5">{{ $s['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Exam</th>
                    <th>Course</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $row)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $row['exam']['title'] }}</td>
                        <td class="text-slate-500 text-xs">{{ $row['exam']['course'] }}<br><span class="text-slate-400">{{ $row['exam']['class'] }}</span></td>
                        <td class="text-slate-500 text-xs">{{ $row['exam']['period'] }}</td>
                        <td>
                            <x-admin.status-badge :value="$row['status']" />
                            @if($row['is_disqualified'])
                                <div class="mt-1 text-xs text-rose-600 font-medium">{{ $row['disqualification_reason'] }}</div>
                            @endif
                        </td>
                        <td>
                            @if($row['max_score'] !== null)
                                <div class="font-bold text-slate-800">
                                    {{ number_format((float) $row['score'], 2) }}
                                    <span class="text-slate-400 font-normal text-xs">/ {{ number_format((float) $row['max_score'], 2) }}</span>
                                </div>
                                <div class="text-xs text-slate-500">{{ number_format((float) ($row['percentage'] ?? 0), 2) }}%</div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="font-mono text-xs text-slate-500">{{ $row['submitted_at'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-inbox text-2xl"></i><span>No attempt history.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
