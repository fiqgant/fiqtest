@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Academic Periods" subtitle="Define semester windows and keep exam data separated across terms.">
        <a href="{{ route('admin.academic-periods.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Period</a>
    </x-admin.page-header>

    <div class="card overflow-x-auto">
        <table class="data-table" style="min-width:700px">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date Range</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $period)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $period->name }}</td>
                        <td class="font-mono text-xs text-slate-500">{{ $period->start_date->format('d M Y') }} — {{ $period->end_date->format('d M Y') }}</td>
                        <td><x-admin.status-badge :value="$period->is_active ? 'active' : 'inactive'" /></td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.reports.period', $period) }}" class="action-btn action-btn-neutral"><i class="fas fa-chart-bar"></i> Report</a>
                                <a href="{{ route('admin.academic-periods.edit', $period) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                <button class="action-btn action-btn-danger" onclick="confirmDelete('/admin/academic-periods/{{ $period->id }}', 'Delete period?')"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-calendar-alt text-2xl"></i><span>No academic periods yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $periods->links() }}</div>
@endsection
