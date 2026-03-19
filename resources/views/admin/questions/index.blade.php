@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Question Bank" subtitle="Build a balanced pool of easy, medium, and hard coding problems.">
        <a href="{{ route('admin.questions.bulk.import') }}" class="btn-secondary"><i class="fas fa-file-import"></i> Bulk Import</a>
        <a href="{{ route('admin.questions.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Question</a>
    </x-admin.page-header>

    <div class="card mb-5 p-5">
        <form method="GET" class="flex items-end gap-3">
            <div class="flex-1">
                <label class="form-label">Filter by Course Offering</label>
                <select name="course_offering_id" class="form-select">
                    <option value="">All Offerings</option>
                    @foreach($offerings as $offering)
                        <option value="{{ $offering->id }}" {{ (string) $offeringId === (string) $offering->id ? 'selected' : '' }}>
                            {{ $offering->course->name }} — {{ $offering->academicPeriod->name }} · {{ $offering->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="btn-secondary"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    {{-- Bulk delete form (hidden, submitted via JS) --}}
    <form method="POST" action="{{ route('admin.questions.bulk-destroy') }}" id="bulk-form">
        @csrf @method('DELETE')
        <div id="bulk-ids"></div>
    </form>

    <div class="flex items-center justify-between mb-3" id="bulk-toolbar" style="display:none">
        <span class="text-sm text-slate-600"><span id="selected-count">0</span> questions selected</span>
        <button onclick="submitBulkDelete()" class="btn-danger">
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
                    <th>Offering</th>
                    <th>Language</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $question)
                    <tr>
                        <td><input type="checkbox" data-id="{{ $question->id }}" class="row-checkbox w-4 h-4 accent-indigo-600"></td>
                        <td class="font-semibold text-slate-800">{{ $question->title }}</td>
                        <td><span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-medium">{{ \App\Models\Question::TYPES[$question->type] ?? $question->type }}</span></td>
                        <td><x-admin.status-badge :value="$question->difficulty" /></td>
                        <td class="text-slate-500 text-xs">{{ $question->courseOffering->course->name }}<br><span class="text-slate-400">{{ $question->courseOffering->academicPeriod->name }} · {{ $question->courseOffering->class_name }}</span></td>
                        <td>@if($question->language)<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-mono font-semibold">{{ $question->language }}</span>@else<span class="text-slate-300">—</span>@endif</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.questions.preview', $question) }}" target="_blank" class="action-btn action-btn-neutral"><i class="fas fa-eye"></i> Preview</a>
                                <a href="{{ route('admin.questions.edit', $question) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                <button class="action-btn action-btn-danger" onclick="confirmDelete('/admin/questions/{{ $question->id }}', 'Delete question?')"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-database text-2xl"></i><span>No questions yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $questions->links() }}</div>

    <script>
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
            showConfirm('Delete ' + n + ' selected question(s)? This action cannot be undone.', function () {
                const container = document.getElementById('bulk-ids');
                container.innerHTML = '';
                document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
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
