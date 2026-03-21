@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header
        title="Question Pool"
        :subtitle="$exam->title"
    >
        <a href="{{ route('admin.exams.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </x-admin.page-header>

    {{-- Distribution summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        @foreach(['easy' => ['color' => 'emerald', 'label' => 'Easy'], 'medium' => ['color' => 'amber', 'label' => 'Medium'], 'hard' => ['color' => 'rose', 'label' => 'Hard']] as $diff => $meta)
        @php
            $available = $counts[$diff] ?? 0;
            $needed    = $exam->{$diff . '_count'};
            $ok        = $available >= $needed;
        @endphp
        <div class="bg-white dark:bg-slate-800 rounded-2xl border {{ $ok ? 'border-slate-200 dark:border-slate-700' : 'border-rose-300' }} p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold text-{{ $meta['color'] }}-600">{{ $meta['label'] }}</span>
                @if($ok)
                    <span class="text-xs text-emerald-600 font-medium"><i class="fas fa-check-circle"></i> OK</span>
                @else
                    <span class="text-xs text-rose-600 font-medium"><i class="fas fa-exclamation-circle"></i> Insufficient</span>
                @endif
            </div>
            <div class="text-3xl font-extrabold text-slate-800 dark:text-slate-100">{{ $available }}</div>
            <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">available &bull; need {{ $needed }}</div>
            <div class="mt-3 w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                @php $barPct = $needed > 0 ? min(100, round($available / $needed * 100)) : 100; @endphp
                <div class="h-2 rounded-full {{ $ok ? 'bg-' . $meta['color'] . '-400' : 'bg-rose-400' }}" style="width: {{ $barPct }}%"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Question list --}}
    <div class="card overflow-x-auto">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <span class="font-semibold text-slate-700 dark:text-slate-200">All Eligible Questions ({{ $questions->count() }} total)</span>
            <span class="text-xs text-slate-400 dark:text-slate-500">These are the questions that may appear in this exam</span>
        </div>
        <table class="data-table" style="min-width:700px">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Difficulty</th>
                    <th>Weight</th>
                    <th>Tags</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $question)
                <tr>
                    <td class="font-semibold text-slate-800">{{ $question->title }}</td>
                    <td>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-medium">
                            {{ \App\Models\Question::TYPES[$question->type] ?? $question->type }}
                        </span>
                    </td>
                    <td><x-admin.status-badge :value="$question->difficulty" /></td>
                    <td class="text-sm text-slate-600">{{ $question->default_weight }} pts</td>
                    <td class="text-xs text-slate-400">{{ $question->tags->pluck('name')->join(', ') ?: '—' }}</td>
                    <td>
                        <a href="{{ route('admin.questions.preview', $question) }}" target="_blank"
                           class="action-btn action-btn-neutral text-xs"><i class="fas fa-eye"></i> Preview</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No questions match this exam's filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
