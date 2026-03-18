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
                <label class="form-label">
                    Description
                    <span class="font-normal text-slate-400 text-xs ml-1">— Markdown supported. Use <code class="bg-slate-100 px-1 rounded">$$...$$</code> for LaTeX, <code class="bg-slate-100 px-1 rounded">```mermaid</code> for diagrams.</span>
                </label>
                <textarea id="description-editor" name="description" rows="8" class="form-textarea" placeholder="Problem statement, constraints, and examples…" required>{{ old('description', $question->description) }}</textarea>
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
            <div>
                <label class="form-label">Hint <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="hint" rows="3" class="form-textarea" placeholder="Hint shown to students when they request it during the exam…">{{ old('hint', $question->hint) }}</textarea>
                <p class="text-xs text-slate-400 mt-1">Leave blank if no hint for this question. Markdown supported.</p>
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

    <!-- EasyMDE Markdown Editor -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css">
    <script src="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js"></script>
    <script>
        const easyMDE = new EasyMDE({
            element: document.getElementById('description-editor'),
            spellChecker: false,
            autofocus: false,
            placeholder: 'Problem statement, constraints, and examples…\n\nSupports **markdown**, `code`, $$LaTeX$$, and ```mermaid blocks.',
            toolbar: [
                'bold', 'italic', 'strikethrough', '|',
                'heading-2', 'heading-3', '|',
                'unordered-list', 'ordered-list', '|',
                'code', 'table', '|',
                'link', '|',
                'preview', 'side-by-side', 'fullscreen', '|',
                'guide',
            ],
            minHeight: '220px',
            status: false,
        });
    </script>

    @include('admin.questions._language-combobox-script')
@endsection
