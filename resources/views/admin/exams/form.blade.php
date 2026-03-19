@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="$exam->exists ? 'Edit Exam' : 'Create Exam'" subtitle="Configure schedule, distribution, weights, and score visibility for this assessment." />

    <form method="POST" action="{{ $exam->exists ? route('admin.exams.update', $exam) : route('admin.exams.store') }}">
        @csrf
        @if($exam->exists) @method('PUT') @endif

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-info-circle mr-1.5"></i> Basic Info</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Title</label>
                    <input type="text" name="title" value="{{ old('title', $exam->title) }}" class="form-input" placeholder="Exam title" required>
                </div>
                <div>
                    <label class="form-label">Course Offering</label>
                    <select name="course_offering_id" class="form-select" required>
                        <option value="">Select offering…</option>
                        @foreach($offerings as $offering)
                            <option value="{{ $offering->id }}" {{ (string) old('course_offering_id', $exam->course_offering_id) === (string) $offering->id ? 'selected' : '' }}>
                                {{ $offering->course->name }} — {{ $offering->academicPeriod->name }} · {{ $offering->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-textarea" placeholder="Optional exam description…">{{ old('description', $exam->description) }}</textarea>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-calendar-alt mr-1.5"></i> Schedule & Visibility</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="form-label">Opens At</label>
                    <input type="datetime-local" name="opens_at" value="{{ old('opens_at', optional($exam->opens_at)->format('Y-m-d\TH:i')) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Closes At</label>
                    <input type="datetime-local" name="closes_at" value="{{ old('closes_at', optional($exam->closes_at)->format('Y-m-d\TH:i')) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Duration (minutes)</label>
                    <input type="number" min="1" max="600" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?? 120) }}" class="form-input" required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach(['draft', 'published', 'closed'] as $s)
                            <option value="{{ $s }}" {{ old('status', $exam->status ?? 'draft') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 mt-7">
                    <input type="checkbox" id="show_score" name="show_score_immediately" value="1" class="w-4 h-4 rounded accent-indigo-600" {{ old('show_score_immediately', $exam->show_score_immediately) ? 'checked' : '' }}>
                    <label for="show_score" class="text-sm font-medium text-slate-700 cursor-pointer">Show score immediately after submission</label>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-shield-alt mr-1.5"></i> Proctoring Rules</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Max Tab/App Switches</label>
                    <input type="number" min="0" max="20" name="max_tab_switches" value="{{ old('max_tab_switches', $exam->max_tab_switches ?? 3) }}" class="form-input" required>
                    <p class="text-xs text-slate-400 mt-1">Set 0 to disable tab-switch auto-fail.</p>
                </div>
                <div>
                    <label class="form-label">Warn at Switch #</label>
                    <input type="number" min="0" max="20" name="tab_switch_warning_count" value="{{ old('tab_switch_warning_count', $exam->tab_switch_warning_count ?? 1) }}" class="form-input" required>
                    <p class="text-xs text-slate-400 mt-1">e.g. 1 = warn on first switch.</p>
                </div>
                <div>
                    <label class="form-label">Inactivity Limit (seconds)</label>
                    <input type="number" min="0" max="3600" name="inactivity_limit_seconds" value="{{ old('inactivity_limit_seconds', $exam->inactivity_limit_seconds ?? 60) }}" class="form-input" required>
                    <p class="text-xs text-slate-400 mt-1">Set 0 to disable inactivity auto-fail.</p>
                </div>
                <div>
                    <label class="form-label">Inactivity Warning Window (seconds)</label>
                    <input type="number" min="0" max="3600" name="inactivity_warning_seconds" value="{{ old('inactivity_warning_seconds', $exam->inactivity_warning_seconds ?? 15) }}" class="form-input" required>
                    <p class="text-xs text-slate-400 mt-1">Warning shown when N seconds remain.</p>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-lightbulb mr-1.5"></i> Hint System</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="hints_enabled" name="hints_enabled" value="1" class="mt-1 w-4 h-4 rounded accent-indigo-600" {{ old('hints_enabled', $exam->hints_enabled ?? false) ? 'checked' : '' }}>
                    <div>
                        <label for="hints_enabled" class="form-label cursor-pointer">Enable Hints</label>
                        <p class="text-xs text-slate-400">Students can request hints for questions that have one.</p>
                    </div>
                </div>
                <div>
                    <label class="form-label">Max Hints per Question</label>
                    <input type="number" min="0" max="10" name="max_hints_per_question" value="{{ old('max_hints_per_question', $exam->max_hints_per_question ?? 1) }}" class="form-input">
                    <p class="text-xs text-slate-400 mt-1">Set 0 for unlimited. Only applies when hints are enabled.</p>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-sliders-h mr-1.5"></i> Question Distribution & Weights</div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="form-label">Easy Count</label>
                    <input type="number" min="0" name="easy_count" value="{{ old('easy_count', $exam->easy_count ?? 1) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Medium Count</label>
                    <input type="number" min="0" name="medium_count" value="{{ old('medium_count', $exam->medium_count ?? 1) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Hard Count</label>
                    <input type="number" min="0" name="hard_count" value="{{ old('hard_count', $exam->hard_count ?? 1) }}" class="form-input">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Easy Weight (pts)</label>
                    <input type="number" min="0" step="0.01" name="easy_weight" value="{{ old('easy_weight', $exam->easy_weight ?? 20) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Medium Weight (pts)</label>
                    <input type="number" min="0" step="0.01" name="medium_weight" value="{{ old('medium_weight', $exam->medium_weight ?? 30) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Hard Weight (pts)</label>
                    <input type="number" min="0" step="0.01" name="hard_weight" value="{{ old('hard_weight', $exam->hard_weight ?? 50) }}" class="form-input">
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="flex items-center justify-between mb-3">
                <div class="form-section-title mb-0"><i class="fas fa-tags mr-1.5"></i> Question Pool Filter</div>
                @if($allTags->isNotEmpty())
                <div class="flex items-center gap-3">
                    <button type="button" id="tag-select-all" onclick="toggleAllTags(true)"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                        Select All
                    </button>
                    <span class="text-slate-300">|</span>
                    <button type="button" id="tag-deselect-all" onclick="toggleAllTags(false)"
                            class="text-xs text-slate-500 hover:text-slate-700 font-medium transition-colors">
                        Deselect All
                    </button>
                    <span class="text-xs text-slate-400" id="tag-selected-count"></span>
                </div>
                @endif
            </div>
            <p class="text-sm text-slate-500 mb-4">Leave empty to use all questions from the course offering. Select tags to limit the pool to questions with those tags (OR logic).</p>
            <div class="flex flex-wrap gap-2" id="tag-pool">
                @foreach($allTags as $tag)
                <label class="flex items-center gap-1.5 cursor-pointer bg-white border border-slate-200 rounded-xl px-3 py-2 hover:border-indigo-300 has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50">
                    <input type="checkbox" name="question_filter_tags[]" value="{{ $tag->id }}"
                        class="pool-tag-checkbox w-4 h-4 accent-indigo-600"
                        onchange="updateTagCount()"
                        {{ in_array($tag->id, old('question_filter_tags', $exam->question_filter_tags ?? [])) ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-slate-700">{{ $tag->name }}</span>
                </label>
                @endforeach
                @if($allTags->isEmpty())
                    <p class="text-sm text-slate-400">No tags created yet. <a href="{{ route('admin.question-tags.index') }}" class="text-indigo-500">Create tags first.</a></p>
                @endif
            </div>
        </div>

        <script>
        function toggleAllTags(check) {
            document.querySelectorAll('.pool-tag-checkbox').forEach(cb => cb.checked = check);
            updateTagCount();
        }

        function updateTagCount() {
            const total   = document.querySelectorAll('.pool-tag-checkbox').length;
            const checked = document.querySelectorAll('.pool-tag-checkbox:checked').length;
            const el      = document.getElementById('tag-selected-count');
            if (el) el.textContent = checked > 0 ? checked + ' / ' + total + ' selected' : '';
        }

        updateTagCount();
        </script>

        <x-admin.form-actions :cancel="route('admin.exams.index')" />
    </form>
@endsection
