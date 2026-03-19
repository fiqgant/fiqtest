@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Students" subtitle="Manage student identity records used for NIM-based exam access.">
        <a href="{{ route('admin.students.bulk.import') }}" class="btn-secondary"><i class="fas fa-file-import"></i> Bulk Import</a>
        <a href="{{ route('admin.students.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Student</a>
    </x-admin.page-header>

    {{-- Bulk delete form (hidden, submitted via JS) --}}
    <form method="POST" action="{{ route('admin.students.bulk-destroy') }}" id="bulk-form">
        @csrf @method('DELETE')
        <div id="bulk-ids"></div>
    </form>

    <div class="flex items-center justify-between mb-3" id="bulk-toolbar" style="display:none">
        <span class="text-sm text-slate-600"><span id="selected-count">0</span> students selected</span>
        <button onclick="submitBulkDelete()" class="btn-danger">
            <i class="fas fa-trash mr-1.5"></i> Delete Selected
        </button>
    </div>

    <div class="card overflow-x-auto">
        <table class="data-table" style="min-width:700px">
            <thead>
                <tr>
                    <th class="w-8"><input type="checkbox" id="select-all" class="w-4 h-4 accent-indigo-600"></th>
                    <th>NIM</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td><input type="checkbox" data-id="{{ $student->id }}" class="row-checkbox w-4 h-4 accent-indigo-600"></td>
                        <td><span class="font-mono font-semibold text-slate-700">{{ $student->nim }}</span></td>
                        <td class="font-medium text-slate-800">{{ $student->name }}</td>
                        <td class="text-slate-500">{{ $student->email ?: '—' }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.reports.student', $student) }}" class="action-btn action-btn-neutral"><i class="fas fa-chart-line"></i> History</a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="action-btn action-btn-primary"><i class="fas fa-pen"></i> Edit</a>
                                <button class="action-btn action-btn-danger" onclick="confirmDelete('/admin/students/{{ $student->id }}', 'Delete this student?')"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-slate-400"><div class="flex flex-col items-center gap-1"><i class="fas fa-users text-2xl"></i><span>No students yet.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>

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
            showConfirm('Delete ' + n + ' selected student(s)? This action cannot be undone.', function () {
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
