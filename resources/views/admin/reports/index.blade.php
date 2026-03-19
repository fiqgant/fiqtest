@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Reports" subtitle="Detailed and easy-to-read grade reports across offerings, periods, and individual students." />

    @php
        $statItems = [
            ['label' => 'Total Attempts',        'value' => $reportStats['total_attempts'],        'color' => 'from-indigo-400 to-indigo-500', 'text' => 'text-indigo-600', 'icon' => 'fa-pencil-alt'],
            ['label' => 'Graded Attempts',        'value' => $reportStats['graded_attempts'],        'color' => 'from-emerald-400 to-emerald-500','text' => 'text-emerald-600','icon' => 'fa-check-circle'],
            ['label' => 'Disqualified Attempts',  'value' => $reportStats['disqualified_attempts'],  'color' => 'from-rose-400 to-rose-500',     'text' => 'text-rose-600',   'icon' => 'fa-ban'],
            ['label' => 'Average Score',          'value' => number_format((float) $reportStats['average_percentage'], 2) . '%', 'color' => 'from-cyan-400 to-cyan-500','text' => 'text-cyan-600','icon' => 'fa-chart-line'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach($statItems as $s)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $s['color'] }} flex items-center justify-center shadow-md mb-3">
                    <i class="fas {{ $s['icon'] }} text-white text-sm"></i>
                </div>
                <div class="text-2xl font-extrabold text-slate-800 leading-none">{{ $s['value'] }}</div>
                <div class="text-xs font-semibold {{ $s['text'] }} uppercase tracking-wider mt-1.5">{{ $s['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

        <!-- Offering Report -->
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-7 h-7 rounded-lg bg-cyan-100 flex items-center justify-center">
                    <i class="fas fa-layer-group text-cyan-600 text-xs"></i>
                </div>
                <h3 class="font-semibold text-slate-800">Report by Offering</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">See complete scores for every student in one class offering.</p>

            <form id="offering-report-form" class="space-y-3">
                <div>
                    <label class="form-label">Academic Period Filter</label>
                    <select name="academic_period_id" onchange="this.form.submit()" class="form-select text-sm">
                        <option value="">All periods</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ (string) request('academic_period_id') === (string) $period->id ? 'selected' : '' }}>
                                {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Course Offering</label>
                    <select id="offering-report-id" class="form-select text-sm" required>
                        <option value="">Select offering…</option>
                        @foreach($offerings as $offering)
                            <option value="{{ $offering->id }}">{{ $offering->course->name }} — {{ $offering->academicPeriod->name }} · {{ $offering->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary w-full justify-center"><i class="fas fa-chart-bar"></i> Open Offering Report</button>
            </form>
        </div>

        <!-- Period Report -->
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-purple-600 text-xs"></i>
                </div>
                <h3 class="font-semibold text-slate-800">Report by Period</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">Compare all classes and summary statistics in one period.</p>

            <form id="period-report-form" class="space-y-3">
                <div>
                    <label class="form-label">Academic Period</label>
                    <select id="period-report-id" class="form-select text-sm" required>
                        <option value="">Select period…</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}">{{ $period->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary w-full justify-center"><i class="fas fa-chart-bar"></i> Open Period Report</button>
            </form>
        </div>

        <!-- Student Report -->
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-7 h-7 rounded-lg bg-rose-100 flex items-center justify-center">
                    <i class="fas fa-user text-rose-600 text-xs"></i>
                </div>
                <h3 class="font-semibold text-slate-800">Report by Student</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">View detailed personal exam history including status and scores.</p>

            <form id="student-report-form" class="space-y-3">
                <div>
                    <label class="form-label">Student</label>
                    <select id="student-report-id" class="form-select text-sm" required>
                        <option value="">Select student…</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->nim }} — {{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary w-full justify-center"><i class="fas fa-chart-line"></i> Open Student Report</button>
            </form>
        </div>
    </div>

    <div class="card overflow-x-auto">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-clock text-indigo-600 text-xs"></i>
                </div>
                <span class="font-semibold text-slate-700">Recent Attempt Activity</span>
            </div>
        </div>
        <table class="data-table" style="min-width:700px">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Exam</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAttempts as $attempt)
                    <tr>
                        <td>
                            <div class="font-semibold text-slate-800">{{ $attempt->student->name }}</div>
                            <div class="font-mono text-xs text-slate-400">{{ $attempt->student->nim }}</div>
                        </td>
                        <td>
                            <div class="font-medium text-slate-700">{{ $attempt->exam->title }}</div>
                            <div class="text-xs text-slate-400">{{ $attempt->exam->courseOffering->course->name }}</div>
                        </td>
                        <td>
                            <x-admin.status-badge :value="$attempt->status" />
                            @if($attempt->is_disqualified)
                                <div class="mt-1 text-xs text-rose-600 font-medium">{{ $attempt->disqualification_reason }}</div>
                            @endif
                        </td>
                        <td class="font-semibold text-slate-800">
                            {{ number_format((float) $attempt->total_score, 2) }}
                            <span class="text-slate-400 font-normal text-xs">/ {{ number_format((float) $attempt->max_score, 2) }}</span>
                        </td>
                        <td class="font-mono text-xs text-slate-500">{{ optional($attempt->submitted_at)->format('d M H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-inbox text-2xl"></i><span>No attempt activity yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        (() => {
            const offeringForm = document.getElementById('offering-report-form');
            const periodForm = document.getElementById('period-report-form');
            const studentForm = document.getElementById('student-report-form');

            if (offeringForm) {
                offeringForm.addEventListener('submit', (event) => {
                    const offeringId = document.getElementById('offering-report-id')?.value;
                    const periodId = offeringForm.querySelector('[name="academic_period_id"]')?.value || '';

                    if (!offeringId) {
                        return;
                    }

                    event.preventDefault();
                    const query = periodId ? `?academic_period_id=${encodeURIComponent(periodId)}` : '';
                    window.location.href = `{{ url('admin/reports/offering') }}/${offeringId}${query}`;
                });
            }

            if (periodForm) {
                periodForm.addEventListener('submit', (event) => {
                    const periodId = document.getElementById('period-report-id')?.value;
                    if (!periodId) {
                        return;
                    }

                    event.preventDefault();
                    window.location.href = `{{ url('admin/reports/period') }}/${periodId}`;
                });
            }

            if (studentForm) {
                studentForm.addEventListener('submit', (event) => {
                    const studentId = document.getElementById('student-report-id')?.value;
                    if (!studentId) {
                        return;
                    }

                    event.preventDefault();
                    window.location.href = `{{ url('admin/reports/student') }}/${studentId}`;
                });
            }
        })();
    </script>
@endsection
