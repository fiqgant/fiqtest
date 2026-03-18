@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header
        title="Enrollment"
        :subtitle="$courseOffering->course->name . ' — ' . $courseOffering->academicPeriod->name . ' · Class ' . $courseOffering->class_name"
    >
        <a href="{{ route('admin.course-offerings.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.course-offerings.enrollment.sync', $courseOffering) }}">
        @csrf

        <div class="card overflow-hidden">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-cyan-100 flex items-center justify-center">
                        <i class="fas fa-user-plus text-cyan-600 text-xs"></i>
                    </div>
                    <span class="font-semibold text-slate-700">Select Students</span>
                </div>
                <div id="enrollment-count" class="text-xs text-slate-500 font-medium"></div>
            </div>

            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input
                            type="text"
                            id="enrollment-search"
                            class="form-input pl-8 py-2 text-sm"
                            placeholder="Search by NIM or name…"
                        >
                    </div>
                    <button type="button" id="select-all-btn" class="action-btn action-btn-primary"><i class="fas fa-check-double"></i> Select All</button>
                    <button type="button" id="deselect-all-btn" class="action-btn action-btn-neutral"><i class="fas fa-times"></i> Deselect All</button>
                </div>
            </div>

            <div class="divide-y divide-slate-50 max-h-[520px] overflow-y-auto" id="student-list">
                @php $selected = $courseOffering->students->pluck('id')->all(); @endphp
                @foreach($students as $student)
                    @php $isChecked = in_array($student->id, old('student_ids', $selected), true); @endphp
                    <label
                        class="enrollment-row flex items-center gap-4 px-5 py-3 cursor-pointer hover:bg-slate-50 transition-colors {{ $isChecked ? 'bg-indigo-50/40' : '' }}"
                        data-name="{{ strtolower($student->name) }}"
                        data-nim="{{ strtolower($student->nim) }}"
                    >
                        <input
                            type="checkbox"
                            name="student_ids[]"
                            value="{{ $student->id }}"
                            class="enrollment-checkbox w-4 h-4 rounded accent-indigo-600 flex-shrink-0"
                            {{ $isChecked ? 'checked' : '' }}
                        >
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-slate-800 text-sm">{{ $student->name }}</div>
                            <div class="font-mono text-xs text-slate-400">{{ $student->nim }}{{ $student->email ? ' · ' . $student->email : '' }}</div>
                        </div>
                        <div class="enrollment-badge flex-shrink-0">
                            @if($isChecked)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold"><i class="fas fa-check text-[10px]"></i> Enrolled</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-400 text-xs">Not enrolled</span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
                <div class="text-sm text-slate-500">{{ $students->links() }}</div>
                <button class="btn-primary" type="submit"><i class="fas fa-save"></i> Save Enrollment</button>
            </div>
        </div>
    </form>

    <script>
        (() => {
            const searchInput = document.getElementById('enrollment-search');
            const selectAllBtn = document.getElementById('select-all-btn');
            const deselectAllBtn = document.getElementById('deselect-all-btn');
            const countEl = document.getElementById('enrollment-count');
            const rows = document.querySelectorAll('.enrollment-row');

            const updateCount = () => {
                const total = document.querySelectorAll('.enrollment-checkbox').length;
                const checked = document.querySelectorAll('.enrollment-checkbox:checked').length;
                if (countEl) countEl.textContent = `${checked} / ${total} selected`;
            };

            const updateBadge = (label, checkbox) => {
                const badge = label.querySelector('.enrollment-badge');
                if (!badge) return;
                if (checkbox.checked) {
                    badge.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold"><i class="fas fa-check text-[10px]"></i> Enrolled</span>';
                    label.classList.add('bg-indigo-50/40');
                } else {
                    badge.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-400 text-xs">Not enrolled</span>';
                    label.classList.remove('bg-indigo-50/40');
                }
            };

            rows.forEach((row) => {
                const checkbox = row.querySelector('.enrollment-checkbox');
                if (!checkbox) return;
                checkbox.addEventListener('change', () => {
                    updateBadge(row, checkbox);
                    updateCount();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    const q = searchInput.value.toLowerCase().trim();
                    rows.forEach((row) => {
                        const name = row.dataset.name || '';
                        const nim = row.dataset.nim || '';
                        row.style.display = (!q || name.includes(q) || nim.includes(q)) ? '' : 'none';
                    });
                });
            }

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    rows.forEach((row) => {
                        if (row.style.display === 'none') return;
                        const cb = row.querySelector('.enrollment-checkbox');
                        if (cb && !cb.checked) { cb.checked = true; updateBadge(row, cb); }
                    });
                    updateCount();
                });
            }

            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', () => {
                    rows.forEach((row) => {
                        if (row.style.display === 'none') return;
                        const cb = row.querySelector('.enrollment-checkbox');
                        if (cb && cb.checked) { cb.checked = false; updateBadge(row, cb); }
                    });
                    updateCount();
                });
            }

            updateCount();
        })();
    </script>
@endsection
