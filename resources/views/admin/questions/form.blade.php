@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="$question->exists ? 'Edit Question' : 'Create Question'" subtitle="Define prompt, type, difficulty, and answer configuration." />

    <form method="POST"
          action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}"
          id="question-form"
          enctype="multipart/form-data">
        @csrf
        @if($question->exists)
            @method('PUT')
        @endif

        {{-- ── Basic Configuration ─────────────────────────────────────── --}}
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="form-label">Question Type</label>
                    <select name="type" id="question-type" class="form-select" required>
                        @foreach(\App\Models\Question::TYPES as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $question->type ?: 'coding') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty" class="form-select" required>
                        @foreach(['easy', 'medium', 'hard'] as $d)
                            <option value="{{ $d }}" {{ old('difficulty', $question->difficulty ?: 'easy') === $d ? 'selected' : '' }}>{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Default Weight (pts)</label>
                    <input type="number" step="0.01" min="0" name="default_weight" value="{{ old('default_weight', $question->default_weight ?: 10) }}" class="form-input" required>
                </div>
            </div>

            {{-- Language (coding only) --}}
            <div id="section-language" class="hidden">
                <label class="form-label">Language</label>
                <div
                    id="language-combobox"
                    class="relative"
                    data-languages='@json($judge0Languages)'
                    data-initial='{{ old('language', $question->language ?: 'Python (3.8.1)') }}'
                >
                    <input type="hidden" name="language" id="language-value">
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
        </div>

        {{-- ── Content ──────────────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-align-left mr-1.5"></i> Content</div>

            <div class="mb-4">
                <label class="form-label">
                    Description
                    <span class="font-normal text-slate-400 text-xs ml-1">— Markdown supported. Use <code class="bg-slate-100 px-1 rounded">$$...$$</code> for LaTeX, <code class="bg-slate-100 px-1 rounded">```mermaid</code> for diagrams.</span>
                </label>
                <textarea id="description-editor" name="description" rows="8" class="form-textarea" placeholder="Problem statement, constraints, and examples…" required>{{ old('description', $question->description) }}</textarea>
                <input type="file" id="image-upload-input" accept="image/*" class="hidden">
            </div>

            {{-- Starter Code (coding only) --}}
            <div id="section-starter-code" class="hidden mb-4">
                <label class="form-label">Starter Code <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="starter_code" rows="6" class="form-textarea font-mono text-sm" placeholder="// Starter code shown to students">{{ old('starter_code', $question->starter_code) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label">Reference Solution <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="reference_solution" rows="5" id="reference-solution-textarea" class="form-textarea font-mono text-sm" placeholder="// Correct/working solution — visible to admin only">{{ old('reference_solution', $question->reference_solution) }}</textarea>
                <p class="text-xs text-slate-400 mt-1">Shown to admin when reviewing student answers. Not visible to students.</p>
            </div>

            <div class="mb-4">
                <label class="form-label">Hint <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="hint" rows="3" class="form-textarea" placeholder="Hint shown to students when they request it during the exam…">{{ old('hint', $question->hint) }}</textarea>
                <p class="text-xs text-slate-400 mt-1">Leave blank if no hint. Markdown supported.</p>
            </div>

            <div>
                <label class="form-label">Tags</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                            class="w-4 h-4 accent-indigo-600"
                            {{ in_array($tag->id, $selectedTagIds ?? []) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-600">{{ $tag->name }}</span>
                    </label>
                    @endforeach
                    @if($tags->isEmpty())
                        <p class="text-sm text-slate-400">No tags yet. <a href="{{ route('admin.question-tags.index') }}" class="text-indigo-500">Create tags first.</a></p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Test Cases (coding only) ─────────────────────────────────── --}}
        <div id="section-test-cases" class="form-section hidden">
            <div class="form-section-title"><i class="fas fa-vial mr-1.5"></i> Test Cases</div>
            <div class="mb-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                <i class="fas fa-info-circle mr-1.5"></i>
                Line format: <code class="bg-amber-100 px-1 rounded font-mono">input||expected_output||is_hidden (0/1)</code>. Use <code class="bg-amber-100 px-1 rounded font-mono">\n</code> to represent line breaks in values.
            </div>
            <textarea name="test_cases_text" id="test-cases-text" rows="8" class="form-textarea font-mono text-sm" placeholder="5||25||0&#10;-3||9||1">{{ old('test_cases_text', $testCasesText) }}</textarea>
        </div>

        {{-- ── Options Builder (MC / MS) ────────────────────────────────── --}}
        <div id="section-options" class="form-section hidden">
            <div class="form-section-title"><i class="fas fa-list-ul mr-1.5"></i> Answer Options</div>
            <p class="text-xs text-slate-400 mb-3" id="options-hint">Check the box next to the correct answer(s).</p>

            <div id="options-list" class="space-y-2 mb-4">
                @php
                    $existingOptions = old('options') ?? ($question->exists ? $question->options->map(fn($o) => ['text' => $o->text, 'is_correct' => $o->is_correct])->toArray() : []);
                    if (empty($existingOptions)) {
                        $existingOptions = [['text' => '', 'is_correct' => false], ['text' => '', 'is_correct' => false]];
                    }
                @endphp
                @foreach($existingOptions as $i => $opt)
                <div class="option-row flex items-center gap-2">
                    <input type="checkbox" name="options[{{ $i }}][is_correct]" value="1"
                           class="option-correct-checkbox w-4 h-4 accent-indigo-600 flex-shrink-0"
                           {{ !empty($opt['is_correct']) ? 'checked' : '' }}>
                    <input type="text" name="options[{{ $i }}][text]" value="{{ $opt['text'] ?? '' }}"
                           class="form-input flex-1" placeholder="Option text…">
                    <button type="button" onclick="removeOption(this)" class="p-1.5 text-red-400 hover:text-red-600 transition-colors flex-shrink-0" title="Remove option">
                        <i class="fas fa-times w-4 h-4"></i>
                    </button>
                </div>
                @endforeach
            </div>

            <button type="button" onclick="addOption()" class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 transition-colors">
                <i class="fas fa-plus"></i> Add Option
            </button>
        </div>

        {{-- ── True / False Answer ──────────────────────────────────────── --}}
        <div id="section-true-false" class="form-section hidden">
            <div class="form-section-title"><i class="fas fa-check-circle mr-1.5"></i> Correct Answer</div>
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="true_false_answer" value="1" class="w-4 h-4 accent-indigo-600"
                           {{ old('true_false_answer', $question->true_false_answer) === true || old('true_false_answer', $question->true_false_answer) === '1' ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-green-700">True</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="true_false_answer" value="0" class="w-4 h-4 accent-indigo-600"
                           {{ old('true_false_answer', $question->true_false_answer) === false || old('true_false_answer', $question->true_false_answer) === '0' ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-red-700">False</span>
                </label>
            </div>
        </div>

        {{-- ── Essay Model Answer ───────────────────────────────────────── --}}
        <div id="section-essay" class="form-section hidden">
            <div class="form-section-title"><i class="fas fa-align-left mr-1.5"></i> Model Answer <span class="text-xs font-normal text-slate-400">(optional)</span></div>
            <div>
                <label class="form-label">Sample / Model Answer</label>
                <textarea name="essay_model_answer" rows="6" class="form-textarea"
                          placeholder="Write a sample answer or grading rubric here…">{{ old('essay_model_answer', $question->essay_model_answer) }}</textarea>
                <p class="text-xs text-slate-400 mt-1">Shown to admin when grading student essays. Not visible to students.</p>
            </div>
        </div>

        {{-- ── Fill in the Blank Answer ─────────────────────────────────── --}}
        <div id="section-fill-blank" class="form-section hidden">
            <div class="form-section-title"><i class="fas fa-pencil-alt mr-1.5"></i> Correct Answer</div>
            <div>
                <label class="form-label">Expected Answer</label>
                <input type="text" name="fill_blank_answer" value="{{ old('fill_blank_answer', $question->fill_blank_answer) }}"
                       class="form-input" placeholder="The exact correct answer…">
                <p class="text-xs text-slate-400 mt-1">Student's answer will be compared case-insensitively and with whitespace trimmed.</p>
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.questions.index')" />
    </form>

    <!-- EasyMDE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css">
    <script src="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js"></script>

    <script>
    // ── Image upload helper ───────────────────────────────────────────
    const imageInput = document.getElementById('image-upload-input');
    let easyMDEInstance = null;

    imageInput.addEventListener('change', async () => {
        const file = imageInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const resp = await fetch('/admin/questions/upload-image', { method: 'POST', body: formData });
            const json = await resp.json();
            if (json.url && easyMDEInstance) {
                const cm = easyMDEInstance.codemirror;
                const cursor = cm.getCursor();
                cm.replaceRange(`![image](${json.url})`, cursor);
            }
        } catch (e) {
            alert('Image upload failed.');
        } finally {
            imageInput.value = '';
        }
    });

    // ── EasyMDE init ─────────────────────────────────────────────────
    easyMDEInstance = new EasyMDE({
        element: document.getElementById('description-editor'),
        spellChecker: false,
        autofocus: false,
        placeholder: 'Problem statement, constraints, and examples…\n\nSupports **markdown**, `code`, $$LaTeX$$, and ```mermaid blocks.',
        toolbar: [
            'bold', 'italic', 'strikethrough', '|',
            'heading-2', 'heading-3', '|',
            'unordered-list', 'ordered-list', '|',
            'code', 'table', '|',
            'link',
            {
                name: 'upload-image',
                action: () => imageInput.click(),
                className: 'fas fa-image',
                title: 'Upload Image',
            },
            '|',
            'preview', 'side-by-side', 'fullscreen', '|',
            'guide',
        ],
        minHeight: '220px',
        status: false,
    });

    // ── Type-aware show/hide ──────────────────────────────────────────
    const typeSelect = document.getElementById('question-type');

    function applyTypeUI() {
        const type = typeSelect.value;
        const isCoding   = type === 'coding';
        const isMC       = type === 'multiple_choice';
        const isMS       = type === 'multiple_select';
        const isTF       = type === 'true_false';
        const isFITB     = type === 'fill_in_blank';
        const isEssay    = type === 'essay';

        // Show/hide sections
        toggle('section-language',    isCoding);
        toggle('section-starter-code', isCoding);
        toggle('section-test-cases',  isCoding);
        toggle('section-options',     isMC || isMS);
        toggle('section-true-false',  isTF);
        toggle('section-fill-blank',  isFITB);
        toggle('section-essay',       isEssay);

        // Required attributes
        const langInput = document.getElementById('language-value');
        const tcText    = document.getElementById('test-cases-text');
        if (langInput) langInput.required = isCoding;
        if (tcText)    tcText.required    = isCoding;

        // Hint text for MC vs MS
        const optHint = document.getElementById('options-hint');
        if (optHint) {
            optHint.textContent = isMS
                ? 'Check all boxes that represent correct answers (multiple correct).'
                : 'Check exactly one box for the correct answer.';
        }
    }

    function toggle(id, show) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('hidden', !show);
    }

    typeSelect.addEventListener('change', applyTypeUI);
    applyTypeUI(); // run on page load

    // ── Options builder ───────────────────────────────────────────────
    function addOption() {
        const list  = document.getElementById('options-list');
        const index = list.querySelectorAll('.option-row').length;
        const row   = document.createElement('div');
        row.className = 'option-row flex items-center gap-2';
        row.innerHTML = `
            <input type="checkbox" name="options[${index}][is_correct]" value="1"
                   class="option-correct-checkbox w-4 h-4 accent-indigo-600 flex-shrink-0">
            <input type="text" name="options[${index}][text]" value=""
                   class="form-input flex-1" placeholder="Option text…">
            <button type="button" onclick="removeOption(this)" class="p-1.5 text-red-400 hover:text-red-600 transition-colors flex-shrink-0" title="Remove option">
                <i class="fas fa-times w-4 h-4"></i>
            </button>
        `;
        list.appendChild(row);
        reindexOptions();
    }

    function removeOption(btn) {
        const list = document.getElementById('options-list');
        if (list.querySelectorAll('.option-row').length <= 2) return; // minimum 2
        btn.closest('.option-row').remove();
        reindexOptions();
    }

    function reindexOptions() {
        document.querySelectorAll('#options-list .option-row').forEach((row, i) => {
            const cb    = row.querySelector('input[type=checkbox]');
            const input = row.querySelector('input[type=text]');
            if (cb)    cb.name    = `options[${i}][is_correct]`;
            if (input) input.name = `options[${i}][text]`;
        });
    }
    </script>

    @include('admin.questions._language-combobox-script')
@endsection
