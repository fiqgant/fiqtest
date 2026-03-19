@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Question Tags" subtitle="Manage tags to categorize questions and filter exam pools." />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <form method="POST" action="{{ route('admin.question-tags.store') }}">
                @csrf
                <div class="form-section">
                    <div class="form-section-title">Add New Tag</div>
                    <input type="text" name="name" class="form-input mb-3" placeholder="e.g. array, recursion, sorting" value="{{ old('name') }}" required>
                    @error('name') <p class="text-xs text-rose-500 mb-2">{{ $message }}</p> @enderror
                    <button type="submit" class="btn-primary w-full">Add Tag</button>
                </div>
            </form>
        </div>

        <div class="md:col-span-2">
            <div class="form-section">
                <div class="flex items-center justify-between mb-4">
                    <div class="form-section-title mb-0">All Tags ({{ $tags->count() }})</div>
                    @if($tags->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-1.5 text-sm text-slate-500 cursor-pointer select-none">
                                <input type="checkbox" id="select-all-tags" class="w-4 h-4 accent-indigo-600">
                                Select all
                            </label>
                        </div>
                    @endif
                </div>

                {{-- Bulk toolbar --}}
                <div id="bulk-toolbar" class="hidden mb-3 flex items-center justify-between bg-rose-50 border border-rose-200 rounded-xl px-4 py-2">
                    <span class="text-sm text-rose-700 font-medium">
                        <span id="selected-count">0</span> tag(s) selected
                    </span>
                    <button type="button" onclick="submitBulkDelete()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>

                @if($tags->isEmpty())
                    <p class="text-sm text-slate-400 text-center py-6">No tags yet.</p>
                @else
                    <div class="flex flex-wrap gap-2" id="tags-container">
                        @foreach($tags as $tag)
                        <div class="tag-item flex items-center gap-2 bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-1.5 transition-all"
                             data-id="{{ $tag->id }}">
                            <input type="checkbox" value="{{ $tag->id }}"
                                   class="tag-checkbox w-3.5 h-3.5 accent-indigo-600 cursor-pointer"
                                   onchange="updateBulkToolbar()">
                            <span class="text-sm font-medium text-indigo-700">{{ $tag->name }}</span>
                            <span class="text-xs text-indigo-400">{{ $tag->questions_count }} questions</span>
                            <button type="button" class="text-rose-400 hover:text-rose-600 ml-1"
                                    onclick="confirmDelete('/admin/question-tags/{{ $tag->id }}', 'Delete tag &quot;{{ $tag->name }}&quot;?')">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    const selectAll = document.getElementById('select-all-tags');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.tag-checkbox').forEach(cb => cb.checked = selectAll.checked);
            updateBulkToolbar();
        });
    }

    function updateBulkToolbar() {
        const checked = document.querySelectorAll('.tag-checkbox:checked');
        const toolbar = document.getElementById('bulk-toolbar');
        const count   = document.getElementById('selected-count');
        if (toolbar) toolbar.classList.toggle('hidden', checked.length === 0);
        if (count)   count.textContent = checked.length;

        // Highlight selected tags
        document.querySelectorAll('.tag-item').forEach(item => {
            const cb = item.querySelector('.tag-checkbox');
            item.classList.toggle('ring-2', cb && cb.checked);
            item.classList.toggle('ring-indigo-400', cb && cb.checked);
        });
    }

    function submitBulkDelete() {
        const checked = document.querySelectorAll('.tag-checkbox:checked');
        const n = checked.length;
        if (n === 0) return;

        showConfirm(
            'Delete ' + n + ' selected tag(s)? This action cannot be undone.',
            () => {
                const params = new URLSearchParams();
                params.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                params.append('_method', 'DELETE');
                checked.forEach(cb => params.append('ids[]', cb.value));

                fetch('/admin/question-tags/bulk-destroy', { method: 'POST', body: params })
                    .then(r => { window.location.href = r.url || window.location.href; })
                    .catch(() => window.location.reload());
            },
            { title: 'Bulk Delete', okText: 'Yes, Delete' }
        );
    }
    </script>
@endsection
