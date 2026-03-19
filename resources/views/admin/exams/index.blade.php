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
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Primary actions --}}
                                <a href="{{ route('admin.exams.monitor', $exam) }}" class="action-btn action-btn-neutral"><i class="fas fa-satellite-dish"></i> Monitor</a>
                                <a href="{{ route('admin.exams.attempts', $exam) }}" class="action-btn action-btn-neutral"><i class="fas fa-list"></i> Attempts</a>
                                <a href="{{ route('admin.exams.edit', $exam) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>

                                {{-- More dropdown --}}
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open" class="action-btn action-btn-neutral"><i class="fas fa-ellipsis-h"></i></button>
                                    <div x-show="open" x-transition
                                         class="absolute right-0 mt-1 w-44 bg-white border border-slate-200 rounded-lg shadow-lg z-20 py-1 text-left">
                                        <a href="{{ route('admin.exams.question-pool', $exam) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><i class="fas fa-layer-group w-4 text-slate-400"></i> Question Pool</a>
                                        <a href="{{ route('admin.exams.export', $exam) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><i class="fas fa-file-excel w-4 text-slate-400"></i> Export Excel</a>
                                        <a href="{{ route('exam.instructions', $exam->slug) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><i class="fas fa-external-link-alt w-4 text-slate-400"></i> Open Link</a>
                                        <button onclick="copyLink('{{ route('exam.instructions', $exam->slug) }}')" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><i class="fas fa-link w-4 text-slate-400"></i> Copy Link</button>
                                        <div class="border-t border-slate-100 my-1"></div>
                                        <form method="POST" action="{{ route('admin.exams.publish', $exam) }}">@csrf
                                            <button class="w-full flex items-center gap-2 px-3 py-2 text-sm text-emerald-700 hover:bg-emerald-50"><i class="fas fa-globe w-4"></i> Publish</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.exams.close', $exam) }}">@csrf
                                            <button class="w-full flex items-center gap-2 px-3 py-2 text-sm text-amber-700 hover:bg-amber-50"><i class="fas fa-lock w-4"></i> Close</button>
                                        </form>
                                        <div class="border-t border-slate-100 my-1"></div>
                                        <button onclick="confirmDelete('/admin/exams/{{ $exam->id }}', 'Delete exam?')" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-rose-600 hover:bg-rose-50"><i class="fas fa-trash w-4"></i> Delete</button>
                                    </div>
                                </div>
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

    {{-- Toast --}}
    <div id="copy-toast" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;font-size:14px;padding:8px 16px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.2);opacity:0;pointer-events:none;transition:opacity .3s;z-index:9999;">
        <i class="fas fa-check" style="color:#34d399;margin-right:6px;"></i> Link copied!
    </div>

    <script>
        function copyLink(url) {
            const done = () => {
                const toast = document.getElementById('copy-toast');
                toast.style.opacity = '1';
                setTimeout(() => { toast.style.opacity = '0'; }, 2000);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(done);
            } else {
                const el = document.createElement('textarea');
                el.value = url;
                el.style.position = 'fixed';
                el.style.opacity = '0';
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                done();
            }
        }
    </script>
@endsection
