@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Exams" subtitle="Schedule coding assessments, control visibility, and monitor attempt activity.">
        <a href="{{ route('admin.exams.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Exam</a>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Offering</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exams as $exam)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $exam->title }}</td>
                        <td class="text-slate-500 text-xs">{{ $exam->courseOffering->course->name }}<br><span class="text-slate-400">{{ $exam->courseOffering->academicPeriod->name }} · {{ $exam->courseOffering->class_name }}</span></td>
                        <td class="font-mono text-xs text-slate-500">{{ $exam->opens_at->format('d M H:i') }}<br>→ {{ $exam->closes_at->format('d M H:i') }}</td>
                        <td><x-admin.status-badge :value="$exam->status" /></td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                <a href="{{ route('admin.exams.monitor', $exam) }}" class="action-btn action-btn-neutral"><i class="fas fa-satellite-dish"></i> Monitor</a>
                                <a href="{{ route('admin.exams.attempts', $exam) }}" class="action-btn action-btn-neutral"><i class="fas fa-list"></i> Attempts</a>
                                <a href="{{ route('admin.exams.question-pool', $exam) }}" class="action-btn action-btn-neutral"><i class="fas fa-layer-group"></i> Pool</a>
                                <a href="{{ route('admin.exams.export', $exam) }}" class="action-btn action-btn-neutral"><i class="fas fa-file-excel"></i> Export</a>
                                <a href="{{ route('admin.exams.edit', $exam) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                <form class="inline" method="POST" action="{{ route('admin.exams.publish', $exam) }}">@csrf
                                    <button class="action-btn action-btn-success"><i class="fas fa-globe"></i> Publish</button>
                                </form>
                                <form class="inline" method="POST" action="{{ route('admin.exams.close', $exam) }}">@csrf
                                    <button class="action-btn action-btn-warning"><i class="fas fa-lock"></i> Close</button>
                                </form>
                                <button class="action-btn action-btn-danger" onclick="confirmDelete('/admin/exams/{{ $exam->id }}', 'Delete exam?')"><i class="fas fa-trash"></i></button>
                                <a class="action-btn action-btn-neutral" target="_blank" href="{{ route('exam.instructions', $exam->slug) }}"><i class="fas fa-external-link-alt"></i></a>
                                <button class="action-btn action-btn-neutral" onclick="copyLink('{{ route('exam.instructions', $exam->slug) }}', this)" title="Copy exam link"><i class="fas fa-link"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-file-alt text-2xl"></i><span>No exams yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $exams->links() }}</div>

    <script>
        function copyLink(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                const icon = btn.querySelector('i');
                icon.className = 'fas fa-check text-emerald-600';
                setTimeout(() => { icon.className = 'fas fa-link'; }, 1500);
            });
        }
    </script>
@endsection
