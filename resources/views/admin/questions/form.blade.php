@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="$question->exists ? 'Edit Question' : 'Create Question'" subtitle="Define prompt, language, difficulty, and executable test cases." />

    <form method="POST" action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}">
        @csrf
        @if($question->exists)
            @method('PUT')
        @endif

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-info-circle mr-1.5"></i> Basic Configuration</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Title</label>
                    <input type="text" name="title" value="{{ old('title', $question->title) }}" class="form-input" placeholder="Question title" required>
                </div>
                <div>
                    <label class="form-label">Course Offering</label>
                    <select name="course_offering_id" class="form-select" required>
                        <option value="">Select offering…</option>
                        @foreach($offerings as $offering)
                            <option value="{{ $offering->id }}" {{ (string) old('course_offering_id', $question->course_offering_id) === (string) $offering->id ? 'selected' : '' }}>
                                {{ $offering->course->name }} — {{ $offering->academicPeriod->name }} · {{ $offering->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty" class="form-select" required>
                        @foreach(['easy', 'medium', 'hard'] as $difficulty)
                            <option value="{{ $difficulty }}" {{ old('difficulty', $question->difficulty ?: 'easy') === $difficulty ? 'selected' : '' }}>{{ ucfirst($difficulty) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Language</label>
                    <div
                        id="language-combobox"
                        class="relative"
                        data-languages='@json($judge0Languages)'
                        data-initial='{{ old('language', $question->language ?: 'Python (3.8.1)') }}'
                    >
                        <input type="hidden" name="language" id="language-value" required>
                        <input
                            type="text"
                            id="language-search"
                            class="form-input"
                            placeholder="Type to search language…"
                            autocomplete="off"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            aria-controls="language-options"
                        >
                        <div
                            id="language-options"
                            class="hidden absolute z-30 mt-1 max-h-64 w-full overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-xl"
                            role="listbox"
                        ></div>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Judge0-supported language.</p>
                </div>
                <div>
                    <label class="form-label">Default Weight (pts)</label>
                    <input type="number" step="0.01" min="0" name="default_weight" value="{{ old('default_weight', $question->default_weight ?: 10) }}" class="form-input" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-align-left mr-1.5"></i> Content</div>
            <div class="mb-4">
                <label class="form-label">Description</label>
                <textarea name="description" rows="8" class="form-textarea" placeholder="Problem statement, constraints, and examples…" required>{{ old('description', $question->description) }}</textarea>
            </div>
            <div class="mb-4">
                <label class="form-label">Starter Code <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="starter_code" rows="6" class="form-textarea font-mono text-sm" placeholder="// Starter code shown to students">{{ old('starter_code', $question->starter_code) }}</textarea>
            </div>
            <div>
                <label class="form-label">Reference Solution</label>
                <textarea name="reference_solution" rows="6" class="form-textarea font-mono text-sm" placeholder="// Correct/working solution">{{ old('reference_solution', $question->reference_solution) }}</textarea>
                <p class="text-xs text-slate-400 mt-1">Shown to admin when reviewing student answers.</p>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-vial mr-1.5"></i> Test Cases</div>
            <div class="mb-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                <i class="fas fa-info-circle mr-1.5"></i>
                Line format: <code class="bg-amber-100 px-1 rounded font-mono">input||expected_output||is_hidden (0/1)</code>. Use <code class="bg-amber-100 px-1 rounded font-mono">\n</code> to represent line breaks in values.
            </div>
            <textarea name="test_cases_text" rows="8" class="form-textarea font-mono text-sm" placeholder="5||25||0&#10;-3||9||1" required>{{ old('test_cases_text', $testCasesText) }}</textarea>
        </div>

        <x-admin.form-actions :cancel="route('admin.questions.index')" />
    </form>

    <script>
        (() => {
            const root = document.getElementById('language-combobox');
            if (!root) {
                return;
            }

            const hiddenInput = document.getElementById('language-value');
            const searchInput = document.getElementById('language-search');
            const optionsPanel = document.getElementById('language-options');
            const raw = root.dataset.languages || '[]';
            const initial = (root.dataset.initial || '').trim();

            let languages = [];
            try {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    languages = parsed
                        .map((row) => ({
                            id: Number(row.id),
                            name: String(row.name || '').trim(),
                        }))
                        .filter((row) => row.name !== '');
                }
            } catch (_) {
                languages = [];
            }

            let filtered = [...languages];
            let activeIndex = -1;
            let isOpen = false;

            const normalize = (value) => String(value || '').toLowerCase();
            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const openPanel = () => {
                if (!isOpen) {
                    isOpen = true;
                    optionsPanel.classList.remove('hidden');
                    searchInput.setAttribute('aria-expanded', 'true');
                }
            };

            const closePanel = () => {
                if (isOpen) {
                    isOpen = false;
                    optionsPanel.classList.add('hidden');
                    searchInput.setAttribute('aria-expanded', 'false');
                }
            };

            const updateHiddenByExact = (value) => {
                const exact = languages.find((row) => normalize(row.name) === normalize(value));
                hiddenInput.value = exact ? exact.name : '';
            };

            const highlightMatch = (text, query) => {
                const escapedText = escapeHtml(text);
                if (!query) {
                    return escapedText;
                }

                const source = text.toLowerCase();
                const needle = query.toLowerCase();
                const index = source.indexOf(needle);

                if (index < 0) {
                    return escapedText;
                }

                const start = escapeHtml(text.slice(0, index));
                const match = escapeHtml(text.slice(index, index + query.length));
                const end = escapeHtml(text.slice(index + query.length));

                return `${start}<span class="bg-amber-100 text-amber-900 rounded px-0.5">${match}</span>${end}`;
            };

            const renderOptions = () => {
                const query = searchInput.value.trim();
                const normalizedQuery = normalize(query);

                filtered = languages.filter((row) => normalize(row.name).includes(normalizedQuery));

                if (filtered.length === 0) {
                    activeIndex = -1;
                    optionsPanel.innerHTML = '<div class="px-3 py-2 text-sm text-slate-500">No matching language found.</div>';
                    return;
                }

                if (activeIndex < 0 || activeIndex >= filtered.length) {
                    activeIndex = 0;
                }

                optionsPanel.innerHTML = filtered.map((row, index) => {
                    const activeClass = index === activeIndex ? 'bg-indigo-50 text-indigo-900' : 'text-slate-700';
                    return `<button type="button" class="language-option flex w-full items-center justify-between px-3 py-2 text-left text-sm ${activeClass}" data-index="${index}" role="option" aria-selected="${index === activeIndex ? 'true' : 'false'}"><span>${highlightMatch(row.name, query)}</span><span class="ml-3 text-xs text-slate-400 font-mono">${row.id}</span></button>`;
                }).join('');

                optionsPanel.querySelectorAll('.language-option').forEach((option) => {
                    option.addEventListener('mousedown', (event) => {
                        event.preventDefault();
                    });
                    option.addEventListener('click', () => {
                        const index = Number(option.dataset.index);
                        if (!Number.isNaN(index)) {
                            selectByIndex(index);
                        }
                    });
                });
            };

            const selectByIndex = (index) => {
                if (index < 0 || index >= filtered.length) {
                    return;
                }

                const selected = filtered[index];
                activeIndex = index;
                searchInput.value = selected.name;
                hiddenInput.value = selected.name;
                closePanel();
            };

            const moveActive = (direction) => {
                if (filtered.length === 0) {
                    return;
                }

                if (activeIndex < 0) {
                    activeIndex = direction > 0 ? 0 : filtered.length - 1;
                } else {
                    activeIndex = (activeIndex + direction + filtered.length) % filtered.length;
                }

                renderOptions();
            };

            const applyInitial = () => {
                if (!initial) {
                    hiddenInput.value = '';
                    searchInput.value = '';
                    return;
                }

                const exact = languages.find((row) => normalize(row.name) === normalize(initial));
                if (exact) {
                    hiddenInput.value = exact.name;
                    searchInput.value = exact.name;
                    return;
                }

                const startsWithMatch = languages.find((row) => normalize(row.name).startsWith(normalize(initial)));
                if (startsWithMatch) {
                    hiddenInput.value = startsWithMatch.name;
                    searchInput.value = startsWithMatch.name;
                    return;
                }

                hiddenInput.value = '';
                searchInput.value = initial;
            };

            applyInitial();
            renderOptions();

            searchInput.addEventListener('focus', () => {
                renderOptions();
                openPanel();
            });

            searchInput.addEventListener('input', () => {
                activeIndex = 0;
                updateHiddenByExact(searchInput.value);
                renderOptions();
                openPanel();
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    openPanel();
                    moveActive(1);
                    return;
                }

                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    openPanel();
                    moveActive(-1);
                    return;
                }

                if (event.key === 'Enter') {
                    if (isOpen && activeIndex >= 0) {
                        event.preventDefault();
                        selectByIndex(activeIndex);
                    } else {
                        updateHiddenByExact(searchInput.value);
                    }
                    return;
                }

                if (event.key === 'Escape') {
                    event.preventDefault();
                    closePanel();
                    return;
                }

                if (event.key === 'Tab') {
                    if (isOpen && activeIndex >= 0) {
                        selectByIndex(activeIndex);
                    } else {
                        updateHiddenByExact(searchInput.value);
                    }
                }
            });

            searchInput.addEventListener('blur', () => {
                window.setTimeout(() => {
                    updateHiddenByExact(searchInput.value);
                    closePanel();
                }, 120);
            });

            document.addEventListener('click', (event) => {
                if (!root.contains(event.target)) {
                    closePanel();
                }
            });
        })();
    </script>
@endsection
