    <!-- Exam Closed Modal -->
    <div x-show="showExamClosedModal"
         x-cloak
         class="fixed inset-0 bg-black/80 flex items-center justify-center z-[60]">
        <div class="bg-neutral-900 rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl border border-red-500/60 text-center">
            <div class="flex items-center justify-center mb-5">
                <div class="w-20 h-20 rounded-full bg-red-500/20 flex items-center justify-center">
                    <i class="fas fa-lock text-red-400 text-4xl"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-red-400 mb-3">Exam Closed</h3>
            <p class="text-gray-300 mb-2">The exam has been closed by the administrator.</p>
            <p class="text-gray-400 text-sm mb-6">Your answers have been automatically submitted.</p>
            <div class="text-4xl font-bold text-white mb-2" x-text="examClosedCountdown"></div>
            <p class="text-gray-500 text-xs">Redirecting...</p>
        </div>
    </div>

    <div x-show="showSubmitModal"
         x-cloak
         class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
         @keydown.escape.window="showSubmitModal = false">
        <div class="bg-neutral-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl"
             @click.outside="showSubmitModal = false">
            <h3 class="text-xl font-bold mb-4">Submit Exam</h3>
            <p class="text-gray-300 mb-4">
                Are you sure you want to submit? This action cannot be undone.
            </p>
            <div class="bg-yellow-900/30 border border-yellow-700 rounded-lg p-3 mb-4">
                <div class="flex items-center text-yellow-400">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span class="text-sm">Once submitted, you cannot change your answers</span>
                </div>
            </div>
            <div class="flex space-x-3">
                <button @click="showSubmitModal = false"
                        class="flex-1 bg-neutral-700 hover:bg-neutral-800 py-2 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
                <button @click="submitExam()"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 py-2 rounded-lg font-medium transition-colors">
                    Confirm Submit
                </button>
            </div>
        </div>
    </div>

    <!-- Hint Modal -->
    <div x-show="showHintModal"
         x-cloak
         class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
         @keydown.escape.window="showHintModal = false">
        <div class="bg-neutral-900 rounded-xl p-6 max-w-lg w-full mx-4 shadow-2xl border border-amber-500/40"
             @click.outside="showHintModal = false">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2 text-amber-400">
                    <i class="fas fa-lightbulb text-xl"></i>
                    <h3 class="text-lg font-bold">Hint</h3>
                </div>
                <template x-if="maxHintsPerQuestion > 0">
                    <span class="text-xs text-gray-400" x-text="'Used ' + hintUsedCount + ' of ' + maxHintsPerQuestion + ' hint(s)'"></span>
                </template>
            </div>
            <div class="prose prose-invert prose-sm max-w-none text-gray-200 bg-black rounded-lg p-4 mb-5"
                 x-ref="hintContentEl"></div>
            <div class="flex justify-end">
                <button @click="showHintModal = false"
                        class="px-5 py-2 bg-neutral-700 hover:bg-neutral-600 rounded-lg font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Fullscreen Requirement Modal -->
    <div x-show="!isFullscreen && !fullscreenDismissed"
         x-cloak
         class="fixed inset-0 bg-black flex items-center justify-center z-50">
        <div class="bg-neutral-900 rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl border border-indigo-500/50 text-center">
            <div class="flex items-center justify-center mb-6">
                <div class="w-20 h-20 rounded-full bg-indigo-500/20 flex items-center justify-center">
                    <i class="fas fa-expand text-indigo-400 text-4xl"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-white mb-4">Exam Mode</h3>
            <p class="text-gray-300 mb-6">
                This exam requires fullscreen mode to ensure exam integrity.
            </p>
            <div class="bg-yellow-900/30 border border-yellow-700 rounded-lg p-3 mb-6">
                <div class="flex items-center text-yellow-400 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span>Leaving fullscreen may trigger violation warnings</span>
                </div>
            </div>
            <button @click="enterFullscreen()"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 px-8 py-4 rounded-lg font-semibold transition-colors text-lg">
                <i class="fas fa-expand mr-2"></i> Enter Fullscreen to Start
            </button>
            <button @click="dismissFullscreen()"
                    class="mt-3 text-gray-400 hover:text-gray-300 text-sm">
                Continue without fullscreen
            </button>
        </div>
    </div>

    <form id="submit-exam-form" method="POST" action="{{ route('exam.submit', $attempt->id) }}" class="hidden">
        @csrf
    </form>

    <!-- Tab Warning Modal -->
    <div x-show="showTabWarningModal"
         x-cloak
         class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
         @keydown.escape.window="hideTabWarning()">
        <div class="bg-neutral-900 rounded-xl p-6 max-w-lg w-full mx-4 shadow-2xl border border-amber-500/50">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 rounded-full bg-amber-500/20 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-amber-500 text-3xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center text-amber-400 mb-4">Tab/App Switch Warning</h3>
            <p class="text-gray-300 text-center mb-6" x-text="tabWarningModalMessage"></p>
            <div class="flex justify-center">
                <button @click="hideTabWarning()"
                        class="bg-amber-600 hover:bg-amber-700 px-8 py-3 rounded-lg font-semibold transition-colors">
                    I Understand
                </button>
            </div>
        </div>
    </div>

    <!-- Time's Up Modal -->
    <div x-show="showTimeUpModal"
         x-cloak
         class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
        <div class="bg-neutral-900 rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl border border-red-500/60 text-center">
            <div class="flex items-center justify-center mb-5">
                <div class="w-20 h-20 rounded-full bg-red-500/20 flex items-center justify-center">
                    <i class="fas fa-hourglass-end text-red-400 text-4xl"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-red-400 mb-3">Time's Up!</h3>
            <p class="text-gray-300 mb-2">The exam duration has ended.</p>
            <p class="text-gray-400 text-sm mb-6">Your answers will be submitted automatically in</p>
            <div class="text-5xl font-bold text-white mb-6" x-text="timeUpCountdown"></div>
            <p class="text-gray-500 text-xs">seconds...</p>
        </div>
    </div>

    <!-- Inactivity Warning Modal -->
    <div x-show="showInactivityWarningModal"
         x-cloak
         class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
        <div class="bg-neutral-900 rounded-xl p-6 max-w-lg w-full mx-4 shadow-2xl border border-rose-500/50">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 rounded-full bg-rose-500/20 flex items-center justify-center">
                    <i class="fas fa-clock text-rose-500 text-3xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center text-rose-400 mb-2">Inactivity Warning</h3>
            <p class="text-gray-300 text-center mb-4">No activity detected. You will be disqualified soon.</p>
            <p class="text-center text-rose-300 mb-6">
                Auto-disqualification in
                <span class="font-mono font-bold text-2xl" x-text="inactivityWarningRemainingSeconds"></span>
                seconds.
            </p>
            <div class="flex justify-center">
                <button @click="dismissInactivityWarning()"
                    class="px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-lg transition-colors">
                    I'm here — dismiss warning
                </button>
            </div>
        </div>
    </div>

    <!-- Disqualification Modal -->
    <div x-show="showDisqualificationModal"
         x-cloak
         class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
        <div class="bg-neutral-900 rounded-xl p-6 max-w-lg w-full mx-4 shadow-2xl border border-red-500/50">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 rounded-full bg-red-500/20 flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-500 text-3xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center text-red-400 mb-4">Exam Disqualified</h3>
            <p class="text-gray-300 text-center mb-6" x-text="disqualificationMessage"></p>
            <div class="flex justify-center">
                <button @click="confirmDisqualification()"
                        class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-lg font-semibold transition-colors">
                    Acknowledged
                </button>
            </div>
        </div>
    </div>

    @php
        $workspaceConfig = [
            'attemptQuestionId' => $currentAttemptQuestion->id,
            'starterCode' => $currentAttemptQuestion->question->starter_code ?? '',
            'currentCode' => $currentAttemptQuestion->code ?? '',
            'runUrl' => route('exam.run', $attempt->id),
            'autosaveUrl' => route('exam.autosave', $attempt->id),
            'activityUrl' => route('exam.activity', $attempt->id),
            'submittedUrl' => route('exam.submitted', $attempt->id),
            'csrfToken' => csrf_token(),
            'editorLanguage' => $currentAttemptQuestion->question->language,
            'maxTabSwitches' => (int) $exam->max_tab_switches,
            'tabSwitchWarningCount' => (int) ($exam->tab_switch_warning_count ?? 1),
            'inactivityLimitSeconds' => (int) $exam->inactivity_limit_seconds,
            'inactivityWarningSeconds' => (int) ($exam->inactivity_warning_seconds ?? 15),
            'initialTabSwitchCount' => (int) $attempt->tab_switch_count,
            'attemptStartedAtIso' => optional($attempt->started_at)->toIso8601String(),
            'examClosesAtIso' => optional($exam->closes_at)->toIso8601String(),
            'examDurationMinutes' => (int) $exam->duration_minutes,
            'initialRemainingSeconds' => (int) $remainingSeconds,
            'hintsEnabled' => (bool) $exam->hints_enabled,
            'maxHintsPerQuestion' => (int) ($exam->max_hints_per_question ?? 1),
            'hintBaseUrl' => route('exam.hint', ['attempt' => $attempt->id, 'attemptQuestion' => 0]),
            'allQuestions' => $attempt->attemptQuestions->map(fn($aq) => [
                'id'             => $aq->id,
                'question_id'    => $aq->question_id,
                'title'          => $aq->question->title,
                'difficulty'     => $aq->question->difficulty,
                'weight'         => $aq->weight,
                'description'    => $aq->question->description,
                'type'           => $aq->question->type,
                'language'       => $aq->question->language,
                'starter_code'   => $aq->question->starter_code ?? '',
                'code'           => $aq->code ?? '',
                'student_answer' => $aq->student_answer ?? '',
                'test_cases'     => $aq->question->getVisibleTestCases(),
                'options'        => (function() use ($aq, $attempt, $exam) {
                    $opts = $aq->question->options->map(fn($o) => [
                        'id'   => $o->id,
                        'text' => $o->text,
                    ])->toArray();
                    if ($exam->shuffle_options && in_array($aq->question->type, ['multiple_choice', 'multiple_select'])) {
                        $seed = $attempt->id * 1000 + $aq->question_id;
                        mt_srand($seed);
                        shuffle($opts);
                        mt_srand();
                    }
                    return array_values($opts);
                })(),
                'has_answer'  => ($aq->code && strlen($aq->code) > strlen($aq->question->starter_code ?? ''))
                              || ($aq->student_answer && $aq->student_answer !== ''),
                'has_hint'       => !empty($aq->question->hint),
                'hint_used_count' => (int) $aq->hint_used_count,
            ])->values()->toArray(),
        ];
    @endphp
    <script id="workspace-config" type="application/json">{!! json_encode($workspaceConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
