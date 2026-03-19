@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Question Bank" subtitle="Build and manage your question pool.">
        <a href="{{ route('admin.questions.bulk.import') }}" class="btn-secondary"><i class="fas fa-file-import"></i> Bulk Import</a>
        <a href="{{ route('admin.questions.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Question</a>
    </x-admin.page-header>

    {{-- Stats summary --}}
    @php
        $total = $questions->total();
        $diffColors = ['easy' => 'emerald', 'medium' => 'amber', 'hard' => 'rose'];
    @endphp
    <div class="flex items-center gap-3 mb-4 flex-wrap">
        <span class="text-sm font-semibold text-slate-500">{{ number_format($total) }} question{{ $total !== 1 ? 's' : '' }}</span>
        @foreach(['easy','medium','hard'] as $d)
            @if(($stats[$d] ?? 0) > 0)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                bg-{{ $diffColors[$d] }}-100 text-{{ $diffColors[$d] }}-700 border border-{{ $diffColors[$d] }}-200">
                <span class="w-1.5 h-1.5 rounded-full bg-{{ $diffColors[$d] }}-500"></span>
                {{ $stats[$d] }} {{ ucfirst($d) }}
            </span>
            @endif
        @endforeach
    </div>

    {{-- Filter bar --}}
    <div class="card mb-5 p-4">
        <form method="GET" id="filter-form" class="flex flex-wrap items-end gap-3">
            {{-- Search --}}
            <div class="flex-1 min-w-48">
                <label class="form-label">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by title…"
                           class="form-input pl-8"
                           oninput="scheduleFilter()">
                </div>
            </div>

            {{-- Course Offering --}}
            <div class="min-w-52">
                <label class="form-label">Course Offering</label>
                <select name="course_offering_id" class="form-select" onchange="submitFilter()">
                    <option value="">All Offerings</option>
                    @foreach($offerings as $offering)
                        <option value="{{ $offering->id }}" {{ (string) $offeringId === (string) $offering->id ? 'selected' : '' }}>
                            {{ $offering->course->name }} · {{ $offering->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Type --}}
            <div class="min-w-36">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" onchange="submitFilter()">
                    <option value="">All Types</option>
                    @foreach(\App\Models\Question::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Difficulty --}}
            <div class="min-w-32">
                <label class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select" onchange="submitFilter()">
                    <option value="">All</option>
                    <option value="easy"   {{ $difficulty === 'easy'   ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ $difficulty === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard"   {{ $difficulty === 'hard'   ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            @if($search || $offeringId || $type || $difficulty)
            <a href="{{ route('admin.questions.index') }}" class="btn-secondary self-end">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Bulk delete form --}}
    <form method="POST" action="{{ route('admin.questions.bulk-destroy') }}" id="bulk-form">
        @csrf @method('DELETE')
        <div id="bulk-ids"></div>
    </form>

    {{-- Bulk toolbar --}}
    <div class="flex items-center justify-between mb-3 bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-2.5" id="bulk-toolbar" style="display:none">
        <span class="text-sm font-semibold text-indigo-700"><span id="selected-count">0</span> selected</span>
        <button onclick="submitBulkDelete()" class="btn-danger btn-sm">
            <i class="fas fa-trash mr-1.5"></i> Delete Selected
        </button>
    </div>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-8"><input type="checkbox" id="select-all" class="w-4 h-4 accent-indigo-600"></th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Difficulty</th>
                    <th>Pts</th>
                    <th>Offering</th>
                    <th>Tags</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $question)
                    <tr class="hover:bg-slate-50 cursor-pointer" onclick="rowClick(event, '{{ route('admin.questions.edit', $question) }}')">
                        <td onclick="event.stopPropagation()">
                            <input type="checkbox" data-id="{{ $question->id }}" class="row-checkbox w-4 h-4 accent-indigo-600">
                        </td>
                        <td>
                            <div class="font-semibold text-slate-800 leading-snug">{{ $question->title }}</div>
                            @if($question->language)
                                <span class="text-xs font-mono text-slate-400">{{ $question->language }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-medium">
                                {{ \App\Models\Question::TYPES[$question->type] ?? $question->type }}
                            </span>
                        </td>
                        <td><x-admin.status-badge :value="$question->difficulty" /></td>
                        <td class="text-sm font-semibold text-slate-600">{{ $question->default_weight }}</td>
                        <td class="text-slate-500 text-xs leading-snug">
                            {{ $question->courseOffering->course->name }}<br>
                            <span class="text-slate-400">{{ $question->courseOffering->academicPeriod->name }} · {{ $question->courseOffering->class_name }}</span>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach($question->tags->take(3) as $tag)
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 text-[11px] font-medium">{{ $tag->name }}</span>
                                @endforeach
                                @if($question->tags->count() > 3)
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-400 text-[11px]">+{{ $question->tags->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-right" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.questions.stats', $question) }}" class="action-btn action-btn-neutral" title="Stats"><i class="fas fa-chart-bar"></i></a>
                                <a href="{{ route('admin.questions.preview', $question) }}" target="_blank" class="action-btn action-btn-neutral" title="Preview"><i class="fas fa-eye"></i></a>
                                <form method="POST" action="{{ route('admin.questions.duplicate', $question) }}" class="inline" onclick="event.stopPropagation()">@csrf
                                    <button class="action-btn action-btn-neutral" title="Duplicate"><i class="fas fa-copy"></i></button>
                                </form>
                                <a href="{{ route('admin.questions.edit', $question) }}" class="action-btn action-btn-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                <button class="action-btn action-btn-danger" title="Delete"
                                    onclick="event.stopPropagation(); confirmDelete('/admin/questions/{{ $question->id }}', 'Delete question?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-slate-400">
                            <div class="flex flex-col items-center gap-3">
                                <i class="fas fa-database text-3xl text-slate-300"></i>
                                @if($search || $type || $difficulty)
                                    <span class="font-medium">No questions match your filters.</span>
                                    <a href="{{ route('admin.questions.index', array_filter(['course_offering_id' => $offeringId])) }}" class="btn-secondary btn-sm">Clear filters</a>
                                @else
                                    <span class="font-medium">No questions yet.</span>
                                    <a href="{{ route('admin.questions.create') }}" class="btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> Add your first question</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $questions->links() }}</div>

    <script>
        // Row click → edit (ignore checkbox / action clicks)
        function rowClick(e, url) {
            window.location = url;
        }

        // Filter auto-submit
        let filterTimer;
        function scheduleFilter() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(submitFilter, 400);
        }
        function submitFilter() {
            clearTimeout(filterTimer);
            document.getElementById('filter-form').submit();
        }

        // Bulk select
        const selectAll = document.getElementById('select-all');
        const toolbar   = document.getElementById('bulk-toolbar');
        const counter   = document.getElementById('selected-count');

        function updateToolbar() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            counter.textContent = checked.length;
            toolbar.style.display = checked.length > 0 ? 'flex' : 'none';
            selectAll.indeterminate = checked.length > 0 && checked.length < document.querySelectorAll('.row-checkbox').length;
            selectAll.checked = checked.length > 0 && checked.length === document.querySelectorAll('.row-checkbox').length;
        }

        function submitBulkDelete() {
            const n = document.querySelectorAll('.row-checkbox:checked').length;
            showConfirm('Delete ' + n + ' selected question(s)? This cannot be undone.', function () {
                const container = document.getElementById('bulk-ids');
                container.innerHTML = '';
                document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                    const input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = 'ids[]';
                    input.value = cb.dataset.id;
                    container.appendChild(input);
                });
                document.getElementById('bulk-form').submit();
            }, { title: 'Bulk Delete' });
        }

        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = selectAll.checked);
            updateToolbar();
        });
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.addEventListener('change', updateToolbar));
    </script>
@endsection
