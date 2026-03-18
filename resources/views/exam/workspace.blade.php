<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} - Coding Exam</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- KaTeX -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"></script>

    <!-- marked.js -->
    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.0/marked.min.js"></script>

    <!-- Mermaid -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        .monaco-editor-container {
            height: 100%;
            min-height: 400px;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #1f2937;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 3px;
        }
        
        .difficulty-easy { color: #4ade80; }
        .difficulty-medium { color: #facc15; }
        .difficulty-hard { color: #f87171; }

        /* Markdown prose dark */
        .md-body { font-size: 0.875rem; line-height: 1.7; color: #cbd5e1; }
        .md-body h1, .md-body h2, .md-body h3 { color: #f1f5f9; font-weight: 700; margin: 1rem 0 0.5rem; }
        .md-body h1 { font-size: 1.25rem; }
        .md-body h2 { font-size: 1.1rem; }
        .md-body h3 { font-size: 1rem; }
        .md-body p { margin: 0.5rem 0; }
        .md-body ul, .md-body ol { padding-left: 1.5rem; margin: 0.5rem 0; }
        .md-body li { margin: 0.2rem 0; }
        .md-body code { background: #1e293b; color: #a5f3fc; padding: 0.1em 0.4em; border-radius: 4px; font-size: 0.82em; font-family: 'JetBrains Mono', monospace; }
        .md-body pre { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 0.875rem 1rem; overflow-x: auto; margin: 0.75rem 0; }
        .md-body pre code { background: none; padding: 0; color: #e2e8f0; font-size: 0.8rem; }
        .md-body blockquote { border-left: 3px solid #6366f1; padding-left: 1rem; color: #94a3b8; margin: 0.75rem 0; }
        .md-body table { width: 100%; border-collapse: collapse; margin: 0.75rem 0; font-size: 0.82rem; }
        .md-body th { background: #1e293b; color: #f1f5f9; padding: 0.5rem 0.75rem; border: 1px solid #334155; text-align: left; }
        .md-body td { padding: 0.4rem 0.75rem; border: 1px solid #1e293b; }
        .md-body tr:nth-child(even) td { background: #0f172a; }
        .md-body strong { color: #f1f5f9; }
        .md-body a { color: #818cf8; text-decoration: underline; }
        .md-body .mermaid { background: #1e293b; border-radius: 8px; padding: 1rem; margin: 0.75rem 0; text-align: center; }
        .md-body .katex { font-size: 1em; }
        .md-body .katex-display { overflow-x: auto; margin: 0.75rem 0; }
    </style>
</head>
<body class="bg-gray-900 text-white h-screen overflow-hidden" x-data="examApp()">

    <script>
        // Disable right-click context menu
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Disable keyboard shortcuts for developer tools and view source
        document.addEventListener('keydown', function(e) {
            // F12
            if (e.key === 'F12') {
                e.preventDefault();
                return false;
            }
            
            // Ctrl+Shift+I (DevTools)
            if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                e.preventDefault();
                return false;
            }
            
            // Ctrl+Shift+J (Console)
            if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                e.preventDefault();
                return false;
            }
            
            // Ctrl+Shift+C (Inspect Element)
            if (e.ctrlKey && e.shiftKey && e.key === 'C') {
                e.preventDefault();
                return false;
            }
            
            // Ctrl+U / Ctrl+A (View Source / Select All)
            if (e.ctrlKey && e.key === 'u') {
                e.preventDefault();
                return false;
            }
            
            // Ctrl+S (Save) - prevent accidental save dialog
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                return false;
            }
            
            // Ctrl+P (Print)
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                return false;
            }
            
            // Ctrl+Shift+P (Command Palette)
            if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                e.preventDefault();
                return false;
            }
        });

        // Disable text selection (optional - can be removed if students need to copy)
        document.addEventListener('selectstart', function(e) {
            // Allow selection in code editor only
            const target = e.target;
            if (!target.closest('#monaco-editor, textarea, input')) {
                // e.preventDefault(); // Uncomment to fully disable selection
            }
        });

        // Block developer tools detection
        (function() {
            const devtools = function() {};
            devtools.toString = function() {
                if (window.__devtools === true) {
                    return 'yes';
                }
                return 'no';
            };
            
            // Override console methods to detect devtools
            const originalConsole = console.log;
            console.log = function() {
                // Allow normal console usage
                originalConsole.apply(console, arguments);
            };
        })();
    </script>

    <div class="flex h-full">
        <aside class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col flex-shrink-0">
            <div class="p-4 border-b border-gray-700">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Questions</h3>
                <div class="text-xs text-gray-500">
                    {{ $attempt->attemptQuestions->count() }} questions
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto p-2 scrollbar-thin">
                <template x-for="(aq, index) in allQuestions" :key="aq.id">
                    <button @click="switchQuestion(aq.id)"
                       class="block w-full text-left p-3 rounded-lg mb-2 transition-all duration-200"
                       :class="currentAttemptQuestionId === aq.id ? 'bg-indigo-600 ring-2 ring-indigo-400' : 'bg-gray-700 hover:bg-gray-600'">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium" x-text="'Q' + (index + 1)"></span>
                            <span x-show="aq.has_code" class="text-xs text-green-400"><i class="fas fa-check-circle"></i></span>
                        </div>
                        <div class="text-xs mt-1"
                             :class="aq.difficulty === 'easy' ? 'text-green-400' : (aq.difficulty === 'medium' ? 'text-yellow-400' : 'text-red-400')"
                             x-text="aq.difficulty.charAt(0).toUpperCase() + aq.difficulty.slice(1)"></div>
                        <div class="text-xs text-gray-400 mt-1 truncate" x-text="aq.title.substring(0, 25) + (aq.title.length > 25 ? '...' : '')"></div>
                    </button>
                </template>
            </div>
        </aside>

        <main class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6 flex-shrink-0">
                <div class="flex items-center space-x-4 min-w-0">
                    <h1 class="text-lg font-semibold truncate">{{ $exam->title }}</h1>
                    <span class="text-gray-500">|</span>
                    <span class="text-sm text-gray-400 truncate">
                        {{ $attempt->student->nim }} - {{ $attempt->student->name }}
                    </span>
                </div>
                
                <div class="flex items-center space-x-6 flex-shrink-0">
                    <div class="text-xs text-gray-300" x-show="maxTabSwitches > 0">
                        Tab exits: <span x-text="tabSwitchCount"></span>/<span x-text="maxTabSwitches"></span>
                    </div>
                    <div class="text-xs text-gray-300" x-show="inactivityLimitSeconds > 0">
                        Inactivity limit: <span x-text="inactivityLimitSeconds"></span>s
                    </div>
                    <div class="flex items-center space-x-2" 
                         x-data="timer(workspaceConfig)"
                         x-init="start()">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-mono text-xl font-bold"
                              :class="timeRemaining <= 300 ? 'text-red-500 animate-pulse' : 'text-white'"
                              x-text="formatTime(timeRemaining)"></span>
                    </div>
                    
                    <button @click="toggleFullscreen()"
                            class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center space-x-2"
                            title="Toggle Fullscreen">
                        <i class="fas fa-expand" x-show="!isFullscreen"></i>
                        <i class="fas fa-compress" x-show="isFullscreen"></i>
                    </button>
                    
                    <button @click="showSubmitModal = true"
                            class="bg-indigo-600 hover:bg-indigo-700 px-6 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center space-x-2">
                        <span>Final Submit</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="flex-1 flex overflow-hidden">
                <div class="w-1/2 border-r border-gray-700 overflow-y-auto scrollbar-thin p-6">
                    <template x-if="currentQuestion">
                        <div>
                            <h2 class="text-2xl font-bold mb-4">
                                <span x-text="currentQuestion.title"></span>
                                <span class="text-sm font-normal text-gray-400 ml-2">
                                    (<span x-text="currentQuestion.difficulty.charAt(0).toUpperCase() + currentQuestion.difficulty.slice(1)"></span>
                                    &bull; <span x-text="currentQuestion.weight"></span> pts)
                                </span>
                            </h2>

                            <div class="md-body"
                                x-ref="descEl"
                                x-effect="renderMarkdown($refs.descEl, currentQuestion?.description || '')">
                            </div>

                            <template x-if="currentQuestion.test_cases && currentQuestion.test_cases.length > 0">
                                <div class="mt-6">
                                    <h3 class="text-lg font-semibold mb-3">Example Test Cases</h3>
                                    <template x-for="(tc, i) in currentQuestion.test_cases" :key="i">
                                        <div class="bg-gray-800 rounded-lg p-4 mb-3">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs text-gray-400 uppercase mb-1">Input</div>
                                                    <pre class="font-mono text-sm bg-gray-900 p-2 rounded" x-text="tc.input"></pre>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-gray-400 uppercase mb-1">Output</div>
                                                    <pre class="font-mono text-sm bg-gray-900 p-2 rounded" x-text="tc.expected_output"></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="w-1/2 flex flex-col">
                    <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium">Code Editor</span>
                            <span class="text-xs text-gray-500" x-text="currentQuestion ? '(' + currentQuestion.language.charAt(0).toUpperCase() + currentQuestion.language.slice(1) + ')' : ''"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span x-show="saving" class="text-xs text-gray-400">
                                <i class="fas fa-spinner fa-spin"></i> Saving...
                            </span>
                            <span x-show="saved && !saving" class="text-xs text-green-400">
                                <i class="fas fa-check"></i> Saved
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex-1" id="monaco-editor"></div>
                    
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-t border-gray-700">
                        <div class="flex items-center space-x-2">
                            <button @click="runCode()"
                                    :disabled="running"
                                    class="bg-green-600 hover:bg-green-700 disabled:bg-gray-600 px-4 py-2 rounded-lg font-medium flex items-center space-x-2 transition-colors">
                                <span x-show="!running"><i class="fas fa-play"></i> Run Code</span>
                                <span x-show="running"><i class="fas fa-spinner fa-spin"></i> Running...</span>
                            </button>

                            <button @click="autosave(true)"
                                    :disabled="saving"
                                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-600 px-4 py-2 rounded-lg font-medium transition-colors">
                                <span x-show="!saving"><i class="fas fa-save"></i> Save Now</span>
                                <span x-show="saving"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                            </button>
                        </div>

                        <button @click="resetCode()"
                                class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                            Reset
                        </button>
                    </div>

                    <div class="h-72 bg-gray-900 border-t border-gray-700 flex flex-col">
                        <div class="flex border-b border-gray-700">
                            <button @click="activeTab = 'output'" 
                                    class="px-4 py-2 text-sm font-medium"
                                    :class="activeTab === 'output' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-gray-400 hover:text-white'">
                                Output
                            </button>
                            <button @click="activeTab = 'testcases'" 
                                    class="px-4 py-2 text-sm font-medium"
                                    :class="activeTab === 'testcases' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-gray-400 hover:text-white'">
                                Test Results
                            </button>
                        </div>
                        
                        <div class="flex-1 p-4 overflow-auto font-mono text-sm scrollbar-thin">
                            <div x-show="activeTab === 'output'">
                                <pre x-show="output" :class="outputStatus === 'success' ? 'text-green-400' : 'text-red-400'" x-text="output || 'Run your code to see output'"></pre>
                                <pre x-show="error" class="text-red-400" x-text="error"></pre>
                                <div x-show="!output && !error" class="text-gray-500">
                                    Click "Run Code" to execute your solution
                                </div>
                            </div>
                            
                            <div x-show="activeTab === 'testcases'" x-cloak>
                                <template x-if="testResults.length > 0">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-sm text-gray-400">Test Results</span>
                                            <span class="text-sm font-medium" 
                                                  :class="allTestsPassed ? 'text-green-400' : 'text-red-400'">
                                                <span x-text="passedCount"></span>/<span x-text="testResults.length"></span> passed
                                            </span>
                                        </div>
                                        <template x-for="(result, index) in testResults" :key="index">
                                            <div class="flex items-center justify-between p-2 rounded mb-1"
                                                 :class="result.is_correct ? 'bg-green-900/30' : 'bg-red-900/30'">
                                                <span class="text-sm">
                                                    <i :class="result.is_correct ? 'fas fa-check-circle text-green-400' : 'fas fa-times-circle text-red-400'" class="mr-2"></i>
                                                    Test <span x-text="index + 1"></span>
                                                </span>
                                                <span class="text-xs text-gray-400" x-text="result.execution_time_ms + 'ms'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <div x-show="testResults.length === 0" class="text-gray-500">
                                    Run code to see test results
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div x-show="showSubmitModal" 
         x-cloak
         class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
         @keydown.escape.window="showSubmitModal = false">
        <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl"
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
                        class="flex-1 bg-gray-600 hover:bg-gray-700 py-2 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
                <button @click="submitExam()"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 py-2 rounded-lg font-medium transition-colors">
                    Confirm Submit
                </button>
            </div>
        </div>
    </div>

    <!-- Fullscreen Requirement Modal -->
    <div x-show="!isFullscreen && !fullscreenDismissed" 
         x-cloak
         class="fixed inset-0 bg-gray-900 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl border border-indigo-500/50 text-center">
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
        <div class="bg-gray-800 rounded-xl p-6 max-w-lg w-full mx-4 shadow-2xl border border-amber-500/50">
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

    <!-- Inactivity Warning Modal -->
    <div x-show="showInactivityWarningModal" 
         x-cloak
         class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
         @keydown.escape.window="showInactivityWarningModal = false">
        <div class="bg-gray-800 rounded-xl p-6 max-w-lg w-full mx-4 shadow-2xl border border-rose-500/50">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 rounded-full bg-rose-500/20 flex items-center justify-center">
                    <i class="fas fa-clock text-rose-500 text-3xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center text-rose-400 mb-2">Inactivity Warning</h3>
            <p class="text-gray-300 text-center mb-4">No mouse/keyboard activity detected.</p>
            <p class="text-center text-rose-300 mb-6">
                Auto-disqualification in 
                <span class="font-mono font-bold text-2xl" x-text="inactivityWarningRemainingSeconds"></span>
                seconds unless you interact now.
            </p>
            <p class="text-center text-gray-400 text-sm mb-4">Move your mouse or press any key to dismiss.</p>
        </div>
    </div>

    <!-- Disqualification Modal -->
    <div x-show="showDisqualificationModal" 
         x-cloak
         class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-xl p-6 max-w-lg w-full mx-4 shadow-2xl border border-red-500/50">
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
            'allQuestions' => $attempt->attemptQuestions->map(fn($aq) => [
                'id' => $aq->id,
                'question_id' => $aq->question_id,
                'title' => $aq->question->title,
                'difficulty' => $aq->question->difficulty,
                'weight' => $aq->weight,
                'description' => $aq->question->description,
                'language' => $aq->question->language,
                'starter_code' => $aq->question->starter_code ?? '',
                'code' => $aq->code ?? '',
                'test_cases' => $aq->question->getVisibleTestCases(),
                'has_code' => $aq->code && strlen($aq->code) > strlen($aq->question->starter_code ?? ''),
            ])->values()->toArray(),
        ];
    @endphp
    <script id="workspace-config" type="application/json">{!! json_encode($workspaceConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
    <script>
        const workspaceConfigNode = document.getElementById('workspace-config');
        let workspaceConfig = {};
        if (workspaceConfigNode) {
            try {
                workspaceConfig = JSON.parse(workspaceConfigNode.textContent || '{}');
            } catch (_) {
                workspaceConfig = {};
            }
        }
        
        let editor = null;
        let fallbackEditor = null;
        let currentAttemptQuestionId = Number(workspaceConfig.attemptQuestionId || 0);
        let starterCode = String(workspaceConfig.starterCode || '');
        let currentCode = String(workspaceConfig.currentCode || '');
        let examAppInstance = null;

        function resolveMonacoLanguage(rawLanguage) {
            const value = String(rawLanguage || '').toLowerCase();

            if (value.includes('python')) return 'python';
            if (value.includes('javascript') || value.includes('node')) return 'javascript';
            if (value.includes('typescript')) return 'typescript';
            if (value.includes('c++') || value.includes('cpp')) return 'cpp';
            if (value.startsWith('c ') || value.startsWith('c(') || value === 'c') return 'c';
            if (value.includes('java')) return 'java';
            if (value.includes('php')) return 'php';
            if (value.includes('go')) return 'go';
            if (value.includes('rust')) return 'rust';

            return 'plaintext';
        }
        
        function activateFallbackEditor() {
            if (fallbackEditor) {
                return;
            }

            const container = document.getElementById('monaco-editor');
            if (!container) {
                return;
            }

            container.innerHTML = '';

            const textArea = document.createElement('textarea');
            textArea.className = 'h-full w-full resize-none bg-gray-900 p-4 font-mono text-sm text-white outline-none';
            textArea.spellcheck = false;
            textArea.value = currentCode || starterCode || '# Write your code here\n';
            textArea.addEventListener('input', () => {
                scheduleAutosave();
            });

            container.appendChild(textArea);
            fallbackEditor = textArea;
        }

        function getCodeValue() {
            if (editor && typeof editor.getValue === 'function') {
                return editor.getValue();
            }

            if (fallbackEditor) {
                return fallbackEditor.value;
            }

            return '';
        }

        function setCodeValue(nextValue) {
            if (editor && typeof editor.setValue === 'function') {
                editor.setValue(nextValue);
                return;
            }

            if (fallbackEditor) {
                fallbackEditor.value = nextValue;
            }
        }

        function initializeMonacoEditor() {
            try {
                const monacoLanguage = resolveMonacoLanguage(String(workspaceConfig.editorLanguage || ''));
                const container = document.getElementById('monaco-editor');

                if (!container || !window.monaco || !window.monaco.editor) {
                    activateFallbackEditor();
                    return;
                }

                editor = monaco.editor.create(container, {
                    value: currentCode || starterCode || '# Write your code here\n',
                    language: monacoLanguage,
                    theme: 'vs-dark',
                    automaticLayout: true,
                    minimap: { enabled: false },
                    fontSize: 14,
                    lineNumbers: 'on',
                    scrollBeyondLastLine: false,
                    padding: { top: 16 },
                    tabSize: 4,
                    insertSpaces: true,
                });

                editor.onDidChangeModelContent(() => {
                    scheduleAutosave();
                });
            } catch (_) {
                activateFallbackEditor();
            }
        }

        if (typeof window.require === 'function') {
            window.require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
            window.require(['vs/editor/editor.main'], initializeMonacoEditor, () => {
                activateFallbackEditor();
            });

            setTimeout(() => {
                if (!editor && !fallbackEditor) {
                    activateFallbackEditor();
                }
            }, 4000);
        } else {
            activateFallbackEditor();
        }
        
        let autosaveTimeout;
        function scheduleAutosave() {
            clearTimeout(autosaveTimeout);
            autosaveTimeout = setTimeout(() => {
                if (examAppInstance) {
                    examAppInstance.autosave();
                }
            }, 2000);
        }

        // Audio alert system using Web Audio API
        let audioContext = null;
        let warningBeepInterval = null;
        
        function initAudioContext() {
            if (!audioContext) {
                try {
                    audioContext = new (window.AudioContext || window.webkitAudioContext)();
                } catch (e) {
                    console.warn('Web Audio API not supported');
                }
            }
            // Resume context if suspended (browser autoplay policy)
            if (audioContext && audioContext.state === 'suspended') {
                audioContext.resume();
            }
            return audioContext;
        }

        function playWarningBeep() {
            try {
                const ctx = initAudioContext();
                if (!ctx) return;

                const oscillator = ctx.createOscillator();
                const gainNode = ctx.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(ctx.destination);

                // Warning beep - loud two-tone alert at full volume
                oscillator.frequency.setValueAtTime(880, ctx.currentTime); // A5
                oscillator.frequency.setValueAtTime(660, ctx.currentTime + 0.2); // E5

                gainNode.gain.setValueAtTime(1.0, ctx.currentTime); // Full volume
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);

                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.4);
            } catch (e) {
                console.warn('Failed to play warning beep:', e);
            }
        }

        function startWarningLoop() {
            // Stop any existing loop
            stopWarningLoop();
            
            // Play immediately
            playWarningBeep();
            
            // Loop every 600ms
            warningBeepInterval = setInterval(() => {
                playWarningBeep();
            }, 600);
        }

        function stopWarningLoop() {
            if (warningBeepInterval) {
                clearInterval(warningBeepInterval);
                warningBeepInterval = null;
            }
        }

        // Fullscreen functions
        function requestFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen().catch(() => {
                    console.warn('Fullscreen request failed');
                });
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        }

        function exitFullscreen() {
            if (document.exitFullscreen) {
                document.exitFullscreen().catch(() => {});
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }

        function playDisqualificationSound() {
            try {
                const ctx = initAudioContext();
                if (!ctx) return;

                // Three descending tones for disqualification - full volume
                const frequencies = [523, 392, 262]; // C5, G4, C4
                frequencies.forEach((freq, i) => {
                    const oscillator = ctx.createOscillator();
                    const gainNode = ctx.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);

                    oscillator.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.25);
                    oscillator.type = 'square';

                    gainNode.gain.setValueAtTime(1.0, ctx.currentTime + i * 0.25); // Full volume
                    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.25 + 0.23);

                    oscillator.start(ctx.currentTime + i * 0.25);
                    oscillator.stop(ctx.currentTime + i * 0.25 + 0.25);
                });
            } catch (e) {
                console.warn('Failed to play disqualification sound:', e);
            }
        }
        
        function examApp() {
            return {
                running: false,
                saving: false,
                saved: false,
                output: '',
                error: '',
                outputStatus: '',
                showSubmitModal: false,
                showTabWarningModal: false,
                showInactivityWarningModal: false,
                showDisqualificationModal: false,
                tabWarningModalMessage: '',
                disqualificationMessage: '',
                disqualificationRedirectUrl: '',
                activeTab: 'output',
                testResults: [],
                allQuestions: (workspaceConfig.allQuestions || []),
                currentAttemptQuestionId: Number(workspaceConfig.attemptQuestionId || 0),
                get currentQuestion() {
                    return this.allQuestions.find(q => q.id === this.currentAttemptQuestionId) || null;
                },
                tabSwitchCount: Number(workspaceConfig.initialTabSwitchCount || 0),
                maxTabSwitches: Number(workspaceConfig.maxTabSwitches || 0),
                tabSwitchWarningCount: Number(workspaceConfig.tabSwitchWarningCount || 1),
                inactivityLimitSeconds: Number(workspaceConfig.inactivityLimitSeconds || 0),
                inactivityWarningSeconds: Number(workspaceConfig.inactivityWarningSeconds || 15),
                inactivityWarningRemainingSeconds: 0,
                inactivityWarningTickHandle: null,
                inactivityWarningShown: false,
                disqualified: false,
                activityTimeoutHandle: null,
                tabSwitchDebounceHandle: null,
                lastUserActivityAtMs: Date.now(),
                lastTabSwitchSentAtMs: 0,
                lastActivitySyncAt: 0,
                monitoringInitialized: false,
                isPageNavigating: false,
                isFullscreen: false,
                fullscreenDismissed: sessionStorage.getItem('examFullscreenDismissed') === 'true',

                init() {
                    if (this.monitoringInitialized) {
                        return;
                    }

                    this.monitoringInitialized = true;
                    examAppInstance = this;
                    this.setupActivityMonitoring();

                    
                    // Track fullscreen state changes
                    const handleFullscreenChange = () => {
                        this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
                        // Save state before leaving page
                        if (this.isFullscreen) {
                            sessionStorage.setItem('examFullscreenWasActive', 'true');
                        }
                    };
                    document.addEventListener('fullscreenchange', handleFullscreenChange);
                    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
                    
                    // Auto re-enter fullscreen on page load if previously in fullscreen
                    const wasInFullscreen = sessionStorage.getItem('examFullscreenWasActive') === 'true';
                    if (wasInFullscreen) {
                        setTimeout(() => {
                            requestFullscreen();
                            this.isFullscreen = true;
                        }, 300);
                    }
                },

                setupActivityMonitoring() {
                    this.registerActivity();
                    this.startInactivityWarningTicker();

                    const markNavigating = () => {
                        this.isPageNavigating = true;
                        if (this.tabSwitchDebounceHandle) {
                            clearTimeout(this.tabSwitchDebounceHandle);
                            this.tabSwitchDebounceHandle = null;
                        }
                    };

                    window.addEventListener('beforeunload', markNavigating);
                    window.addEventListener('pagehide', markNavigating);
                    window.addEventListener('blur', () => {
                        this.queueTabSwitchCheck('app_switch');
                    });
                    window.addEventListener('focus', () => {
                        this.isPageNavigating = false;
                        this.registerActivity();
                    });

                    document.querySelectorAll('a[data-internal-nav="1"]').forEach((node) => {
                        node.addEventListener('click', (event) => {
                            if (
                                event.defaultPrevented
                                || event.button !== 0
                                || event.metaKey
                                || event.ctrlKey
                                || event.shiftKey
                                || event.altKey
                                || node.target === '_blank'
                            ) {
                                return;
                            }

                            markNavigating();
                        });
                    });

                    const activityEvents = ['mousemove', 'keydown', 'mousedown', 'scroll', 'touchstart'];
                    activityEvents.forEach((eventName) => {
                        window.addEventListener(eventName, () => this.registerActivity(), { passive: true });
                    });

                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            this.queueTabSwitchCheck('tab_hidden');
                            return;
                        }

                        if (this.tabSwitchDebounceHandle) {
                            clearTimeout(this.tabSwitchDebounceHandle);
                            this.tabSwitchDebounceHandle = null;
                        }

                        this.isPageNavigating = false;
                        this.registerActivity();
                    });
                },

                startInactivityWarningTicker() {
                    if (this.inactivityWarningTickHandle) {
                        clearInterval(this.inactivityWarningTickHandle);
                        this.inactivityWarningTickHandle = null;
                    }

                    this.inactivityWarningTickHandle = setInterval(() => {
                        if (this.disqualified || this.inactivityLimitSeconds <= 0) {
                            this.showInactivityWarningModal = false;
                            return;
                        }

                        const elapsedSeconds = Math.floor((Date.now() - this.lastUserActivityAtMs) / 1000);
                        const remaining = Math.max(0, this.inactivityLimitSeconds - elapsedSeconds);
                        this.inactivityWarningRemainingSeconds = remaining;

                        const warningWindow = Math.max(0, this.inactivityWarningSeconds);
                        const shouldShow = warningWindow > 0
                            && remaining > 0
                            && remaining <= warningWindow;
                        
                        // Start/stop warning loop
                        if (shouldShow && !this.inactivityWarningShown) {
                            this.inactivityWarningShown = true;
                            startWarningLoop();
                        } else if (!shouldShow && this.inactivityWarningShown) {
                            this.inactivityWarningShown = false;
                            stopWarningLoop();
                        }
                        
                        this.showInactivityWarningModal = shouldShow;
                    }, 1000);
                },

                queueTabSwitchCheck(source = 'tab_switch') {
                    if (this.maxTabSwitches <= 0 || this.disqualified || this.isPageNavigating) {
                        return;
                    }

                    if (this.tabSwitchDebounceHandle) {
                        clearTimeout(this.tabSwitchDebounceHandle);
                    }

                    this.tabSwitchDebounceHandle = setTimeout(() => {
                        this.tabSwitchDebounceHandle = null;

                        const nowMs = Date.now();
                        if (nowMs - this.lastTabSwitchSentAtMs < 800) {
                            return;
                        }

                        if (!this.isPageNavigating && (document.hidden || !document.hasFocus())) {
                            this.lastTabSwitchSentAtMs = nowMs;
                            this.reportTabSwitch();
                        }
                    }, 350);
                },

                registerActivity() {
                    if (this.disqualified) {
                        return;
                    }

                    this.lastUserActivityAtMs = Date.now();
                    this.showInactivityWarningModal = false;

                    this.scheduleInactivityTimeout();

                    const nowMs = Date.now();
                    if (nowMs - this.lastActivitySyncAt >= 15000) {
                        this.lastActivitySyncAt = nowMs;
                        this.reportActivityEvent('activity');
                    }
                },

                scheduleInactivityTimeout() {
                    if (this.activityTimeoutHandle) {
                        clearTimeout(this.activityTimeoutHandle);
                        this.activityTimeoutHandle = null;
                    }

                    if (this.inactivityLimitSeconds <= 0) {
                        return;
                    }

                    this.activityTimeoutHandle = setTimeout(() => {
                        this.reportActivityEvent('inactivity_timeout');
                    }, this.inactivityLimitSeconds * 1000);
                },

                async reportTabSwitch() {
                    if (this.maxTabSwitches <= 0 || this.disqualified) {
                        return;
                    }

                    await this.reportActivityEvent('tab_switch');
                },

                showTabWarning(message) {
                    this.tabWarningModalMessage = message;
                    this.showTabWarningModal = true;
                    startWarningLoop();
                },

                hideTabWarning() {
                    this.showTabWarningModal = false;
                    stopWarningLoop();
                },

                async switchQuestion(aqId) {
                    if (aqId === this.currentAttemptQuestionId) return;

                    // Autosave current code before switching
                    await this.autosave(true);

                    // Save current code back to allQuestions cache
                    const prev = this.allQuestions.find(q => q.id === this.currentAttemptQuestionId);
                    if (prev) {
                        prev.code = getCodeValue();
                        prev.has_code = prev.code && prev.code.length > prev.starter_code.length;
                    }

                    // Switch to new question
                    this.currentAttemptQuestionId = aqId;
                    currentAttemptQuestionId = aqId;

                    const next = this.allQuestions.find(q => q.id === aqId);
                    if (next) {
                        const nextCode = next.code || next.starter_code || '';
                        setCodeValue(nextCode);

                        // Update Monaco language if needed
                        if (editor && window.monaco) {
                            const newLang = resolveMonacoLanguage(next.language);
                            const model = editor.getModel();
                            if (model) {
                                monaco.editor.setModelLanguage(model, newLang);
                            }
                        }

                        starterCode = next.starter_code || '';
                        workspaceConfig.attemptQuestionId = aqId;
                        workspaceConfig.starterCode = next.starter_code || '';
                    }

                    // Clear output panel
                    this.output = '';
                    this.error = '';
                    this.testResults = [];
                    this.activeTab = 'output';
                },

                toggleFullscreen() {
                    if (document.fullscreenElement || document.webkitFullscreenElement) {
                        exitFullscreen();
                        this.isFullscreen = false;
                    } else {
                        requestFullscreen();
                        this.isFullscreen = true;
                    }
                },

                enterFullscreen() {
                    requestFullscreen();
                    this.isFullscreen = true;
                    sessionStorage.setItem('examFullscreenWasActive', 'true');
                    sessionStorage.setItem('examFullscreenDismissed', 'true');
                },

                dismissFullscreen() {
                    this.fullscreenDismissed = true;
                    sessionStorage.setItem('examFullscreenDismissed', 'true');
                },

                async reportActivityEvent(eventType) {
                    if (this.disqualified || !workspaceConfig.activityUrl) {
                        return;
                    }

                    try {
                        const response = await fetch(String(workspaceConfig.activityUrl), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': String(workspaceConfig.csrfToken || ''),
                            },
                            body: JSON.stringify({ event: eventType }),
                        });

                        const data = await response.json();
                        if (typeof data.tab_switch_count !== 'undefined') {
                            this.tabSwitchCount = Number(data.tab_switch_count || 0);
                        }

                        if (eventType === 'tab_switch' && !data.disqualified && this.maxTabSwitches > 0) {
                            const shouldWarn = this.tabSwitchWarningCount > 0 && this.tabSwitchCount >= this.tabSwitchWarningCount;
                            if (shouldWarn) {
                                const remaining = Math.max(0, this.maxTabSwitches - this.tabSwitchCount);
                                this.showTabWarning(`You left the exam tab/app ${this.tabSwitchCount} time(s). Remaining allowed exits: ${remaining}.`);
                            }
                        }

                        if (data.disqualified) {
                            this.handleDisqualification(data.message || 'Attempt disqualified by exam policy.', data.redirect_url || workspaceConfig.submittedUrl);
                        }
                    } catch (_) {
                    }
                },

                handleDisqualification(message, redirectUrl) {
                    if (this.disqualified) {
                        return;
                    }

                    this.disqualified = true;
                    this.showSubmitModal = false;
                    this.showTabWarningModal = false;
                    this.showInactivityWarningModal = false;
                    clearTimeout(this.activityTimeoutHandle);
                    if (this.inactivityWarningTickHandle) {
                        clearInterval(this.inactivityWarningTickHandle);
                        this.inactivityWarningTickHandle = null;
                    }

                    this.disqualificationMessage = message;
                    this.disqualificationRedirectUrl = redirectUrl || String(workspaceConfig.submittedUrl || '');
                    this.showDisqualificationModal = true;
                    
                    // Play disqualification sound
                    playDisqualificationSound();
                },

                confirmDisqualification() {
                    this.showDisqualificationModal = false;
                    const redirectUrl = this.disqualificationRedirectUrl || String(workspaceConfig.submittedUrl || '');
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                },
                  
                async runCode() {
                    // Initialize audio context on first user interaction
                    initAudioContext();
                    
                    this.running = true;
                    this.output = '';
                    this.error = '';
                    
                    try {
                        const response = await fetch(String(workspaceConfig.runUrl || ''), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': String(workspaceConfig.csrfToken || '')
                            },
                            body: JSON.stringify({
                                attempt_question_id: currentAttemptQuestionId,
                                code: getCodeValue()
                            })
                        });
                        
                        const data = await response.json();
                        this.output = data.output || '';
                        this.error = data.error || '';
                        this.outputStatus = data.status;
                        
                        if (data.status === 'success') {
                            this.activeTab = 'output';
                        }
                    } catch (e) {
                        this.error = 'Failed to execute code: ' + e.message;
                    } finally {
                        this.running = false;
                    }
                },
                
                async autosave(force = false) {
                    if ((!editor && !fallbackEditor) || this.saving) {
                        return;
                    }

                    this.saving = true;
                    this.saved = false;
                    
                    try {
                        const codeValue = getCodeValue();
                        if (!force && codeValue === currentCode) {
                            this.saved = true;
                            setTimeout(() => { this.saved = false; }, 1200);
                            return;
                        }

                        await fetch(String(workspaceConfig.autosaveUrl || ''), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': String(workspaceConfig.csrfToken || '')
                            },
                            body: JSON.stringify({
                                attempt_question_id: currentAttemptQuestionId,
                                code: codeValue
                            })
                        });
                        currentCode = codeValue;
                        this.saved = true;
                        setTimeout(() => { this.saved = false; }, 2000);
                    } catch (e) {
                        console.error('Autosave failed:', e);
                    } finally {
                        this.saving = false;
                    }
                },
                
                resetCode() {
                    if (confirm('Reset code to original? This will lose your current changes.')) {
                        setCodeValue(starterCode || '');
                    }
                },
                
                async submitExam() {
                    this.isPageNavigating = true;
                    document.getElementById('submit-exam-form').submit();
                },
                
                get passedCount() {
                    return this.testResults.filter(r => r.is_correct).length;
                },
                
                get allTestsPassed() {
                    return this.testResults.length > 0 && this.passedCount === this.testResults.length;
                }
            };
        }
        
        function timer(config) {
            return {
                timeRemaining: Number(config.initialRemainingSeconds || 0),

                computeRemainingSeconds() {
                    const startedAtIso = String(config.attemptStartedAtIso || '');
                    const closesAtIso = String(config.examClosesAtIso || '');
                    const durationMinutes = Number(config.examDurationMinutes || 0);

                    const startedAt = new Date(startedAtIso);
                    const closesAt = new Date(closesAtIso);

                    if (Number.isNaN(startedAt.getTime()) || Number.isNaN(closesAt.getTime()) || durationMinutes <= 0) {
                        return this.timeRemaining;
                    }

                    const durationExpiresAt = new Date(startedAt.getTime() + durationMinutes * 60 * 1000);
                    const hardExpiresAt = durationExpiresAt.getTime() < closesAt.getTime() ? durationExpiresAt : closesAt;
                    const remaining = Math.floor((hardExpiresAt.getTime() - Date.now()) / 1000);

                    return Math.max(0, remaining);
                },
                
                start() {
                    this.timeRemaining = this.computeRemainingSeconds();
                    setInterval(() => {
                        this.timeRemaining = this.computeRemainingSeconds();
                        if (this.timeRemaining === 0) {
                            this.$dispatch('time-up');
                        }
                    }, 1000);
                },
                
                formatTime(seconds) {
                    const hrs = Math.floor(seconds / 3600);
                    const mins = Math.floor((seconds % 3600) / 60);
                    const secs = seconds % 60;
                    
                    if (hrs > 0) {
                        return `${hrs}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    }
                    return `${mins}:${secs.toString().padStart(2, '0')}`;
                }
            };
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('timer', timer);
        });
    </script>

    <script>
        // Initialize Mermaid
        mermaid.initialize({ startOnLoad: false, theme: 'dark', securityLevel: 'loose' });

        // Protect LaTeX blocks from being mangled by marked
        function extractLatex(raw) {
            const store = [];
            const placeholder = (i) => `LATEX_PLACEHOLDER_${i}_END`;

            // Replace $$...$$ and $...$ with placeholders
            let out = raw
                .replace(/\$\$([\s\S]+?)\$\$/g, (_, math) => {
                    store.push({ display: true, math });
                    return placeholder(store.length - 1);
                })
                .replace(/\$([^\n$]+?)\$/g, (_, math) => {
                    store.push({ display: false, math });
                    return placeholder(store.length - 1);
                });

            return { out, store };
        }

        function restoreLatex(html, store) {
            return html.replace(/LATEX_PLACEHOLDER_(\d+)_END/g, (_, i) => {
                const { display, math } = store[Number(i)];
                try {
                    return katex.renderToString(math, { displayMode: display, throwOnError: false });
                } catch {
                    return display ? `$$${math}$$` : `$${math}$`;
                }
            });
        }

        // Markdown + LaTeX + Mermaid renderer
        function renderMarkdown(el, raw) {
            if (!el) return;

            if (!raw || !raw.trim()) {
                el.innerHTML = '';
                return;
            }

            // Step 1: extract LaTeX before marked touches it
            const { out: safeRaw, store } = extractLatex(raw);

            // Step 2: configure marked for mermaid
            marked.use({
                breaks: true,
                gfm: true,
                renderer: {
                    code({ text, lang }) {
                        if (lang === 'mermaid') {
                            return `<div class="mermaid">${text}</div>`;
                        }
                        return false;
                    }
                }
            });

            // Step 3: parse markdown
            let html = marked.parse(safeRaw);

            // Step 4: restore LaTeX as rendered KaTeX HTML
            html = restoreLatex(html, store);

            el.innerHTML = html;

            // Step 5: Mermaid
            const mermaidNodes = el.querySelectorAll('.mermaid');
            if (mermaidNodes.length) {
                mermaid.run({ nodes: mermaidNodes });
            }
        }

        // Expose to Alpine scope
        document.addEventListener('alpine:init', () => {
            Alpine.magic('renderMarkdown', () => renderMarkdown);
        });
    </script>
</body>
</html>
