@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Period Grade Report" :subtitle="$academicPeriod->name">
        <a href="{{ route('admin.reports.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Reports</a>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Class</th>
                    <th>Students</th>
                    <th>Submitted</th>
                    <th>Not Started</th>
                    <th>Average</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periodReports as $report)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $report['offering']['course'] }}</td>
                        <td><span class="inline-flex items-center px-2 py-0.5 rounded-md bg-cyan-50 text-cyan-700 text-xs font-semibold">{{ $report['offering']['class'] }}</span></td>
                        <td class="font-semibold text-slate-700">{{ $report['statistics']['total_students'] }}</td>
                        <td>
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                                <i class="fas fa-check-circle text-xs"></i>
                                {{ $report['statistics']['submitted'] }}
                            </span>
                        </td>
                        <td>
                            @if($report['statistics']['not_started'] > 0)
                                <span class="inline-flex items-center gap-1 text-rose-600 font-semibold">
                                    <i class="fas fa-minus-circle text-xs"></i>
                                    {{ $report['statistics']['not_started'] }}
                                </span>
                            @else
                                <span class="text-slate-400">0</span>
                            @endif
                        </td>
                        <td>
                            @php $avg = (float) $report['statistics']['average_score']; @endphp
                            <div class="font-bold {{ $avg >= 75 ? 'text-emerald-600' : ($avg >= 50 ? 'text-amber-600' : 'text-rose-600') }}">
                                {{ number_format($avg, 2) }}%
                            </div>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.reports.offering', $report['offering']['id']) }}" class="action-btn action-btn-primary">
                                <i class="fas fa-chart-bar"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-inbox text-2xl"></i><span>No offering report data in this period.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
