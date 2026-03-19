<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $question->title }}</title>

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

    @if($question->isCoding())
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }

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
        .md-body img { max-width: 100%; height: auto; border-radius: 6px; margin: 0.75rem 0; display: block; border: 1px solid #334155; }
        .md-body .mermaid { background: #1e293b; border-radius: 8px; padding: 1rem; margin: 0.75rem 0; text-align: center; }
        .md-body .katex { font-size: 1em; }
        .md-body .katex-display { overflow-x: auto; margin: 0.75rem 0; }

        .difficulty-easy { color: #4ade80; }
        .difficulty-medium { color: #facc15; }
        .difficulty-hard { color: #f87171; }

        #monaco-container { height: 400px; }

        .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: #1f2937; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen" x-data="previewApp()">

    {{-- Admin preview banner --}}
    <div class="bg-amber-500 text-gray-900 px-4 py-2 flex items-center justify-between text-sm font-medium sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <i class="fas fa-eye"></i>
            <span>Admin Preview — This is how students see this question</span>
        </div>
        <a href="{{ url()->previous() }}" class="flex items-center gap-1.5 bg-gray-900/20 hover:bg-gray-900/30 px-3 py-1 rounded transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Back
        </a>
    </div>

    <div class="max-w-5xl mx-auto p-6">

        {{-- Question header --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 mb-6 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-700 bg-gray-800/80">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-indigo-900/60 text-indigo-300 border border-indigo-700/50">
                        {{ \App\Models\Question::TYPES[$question->type] ?? $question->type }}
                    </span>
                    <span class="text-xs font-semibold difficulty-{{ $question->difficulty }}">
                        <i class="fas fa-circle text-[8px] mr-1"></i>{{ ucfirst($question->difficulty) }}
                    </span>
                    @if($question->language)
                        <span class="text-xs font-mono px-2 py-0.5 rounded bg-gray-700 text-gray-300">{{ $question->language }}</span>
                    @endif
                </div>
                <span class="text-xs text-gray-500">Weight: {{ $question->default_weight }}</span>
            </div>

            <div class="p-5">
                <h2 class="text-lg font-bold text-white mb-4">{{ $question->title }}</h2>
                <div class="md-body" x-ref="descEl">{{ $question->description }}</div>
            </div>
        </div>

        @if($question->isCoding())
        {{-- Coding question --}}
        <div class="grid grid-cols-1 gap-6">

            {{-- Visible test cases --}}
            @php $visibleTc = $question->getVisibleTestCases(); @endphp
            @if(count($visibleTc) > 0)
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-700 text-sm font-medium text-gray-300">
                    <i class="fas fa-flask mr-1.5 text-indigo-400"></i> Sample Test Cases
                </div>
                <div class="p-5 space-y-3">
                    @foreach($visibleTc as $i => $tc)
                    <div class="bg-gray-900 rounded-lg border border-gray-700 overflow-hidden">
                        <div class="flex text-xs font-medium text-gray-500 border-b border-gray-700">
                            <span class="px-3 py-1.5 border-r border-gray-700">Test {{ $i + 1 }}</span>
                        </div>
                        <div class="grid grid-cols-2 divide-x divide-gray-700">
                            <div class="p-3">
                                <div class="text-xs text-gray-500 mb-1">Input</div>
                                <pre class="text-xs text-gray-300 font-mono whitespace-pre-wrap">{{ $tc['input'] ?: '(none)' }}</pre>
                            </div>
                            <div class="p-3">
                                <div class="text-xs text-gray-500 mb-1">Expected Output</div>
                                <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">{{ $tc['expected_output'] }}</pre>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @php $hiddenCount = count($question->getHiddenTestCases()); @endphp
            @if($hiddenCount > 0)
            <div class="bg-gray-800/50 rounded-xl border border-dashed border-gray-600 px-5 py-3 text-sm text-gray-400 flex items-center gap-2">
                <i class="fas fa-eye-slash text-gray-500"></i>
                {{ $hiddenCount }} hidden test case(s) — not visible to students
            </div>
            @endif

            {{-- Code editor --}}
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-700">
                    <span class="text-sm font-medium text-gray-300"><i class="fas fa-code mr-1.5 text-indigo-400"></i> Code Editor</span>
                    <span class="text-xs text-gray-500">{{ $question->language }}</span>
                </div>
                <div id="monaco-container"></div>
                <div class="px-5 py-3 border-t border-gray-700 flex gap-2">
                    <button disabled class="bg-green-600 opacity-50 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                        <i class="fas fa-play"></i> Run Code
                    </button>
                    <button disabled class="bg-blue-600 opacity-50 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                        <i class="fas fa-save"></i> Save Now
                    </button>
                    <span class="text-xs text-gray-500 self-center ml-2">(disabled in preview)</span>
                </div>
            </div>
        </div>

        @else
        {{-- Non-coding answer panel --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-700 text-sm font-medium text-gray-300">
                <i class="fas fa-pen mr-1.5 text-indigo-400"></i> Your Answer
            </div>
            <div class="p-6">

                @if($question->type === 'multiple_choice')
                    <p class="text-sm text-gray-400 mb-4">Select one correct answer.</p>
                    <div class="space-y-3">
                        @foreach($question->options as $option)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-700 bg-gray-800 hover:border-gray-500 cursor-pointer transition-colors">
                            <input type="radio" name="mc_preview" class="mt-0.5 accent-indigo-500 flex-shrink-0" disabled>
                            <span class="text-sm text-gray-200">{{ $option->text }}</span>
                            @if($option->is_correct)
                                <span class="ml-auto text-xs text-green-400 font-medium flex-shrink-0"><i class="fas fa-check-circle"></i> Correct</span>
                            @endif
                        </label>
                        @endforeach
                    </div>

                @elseif($question->type === 'multiple_select')
                    <p class="text-sm text-gray-400 mb-4">Select all that apply.</p>
                    <div class="space-y-3">
                        @foreach($question->options as $option)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-700 bg-gray-800 hover:border-gray-500 cursor-pointer transition-colors">
                            <input type="checkbox" class="mt-0.5 accent-indigo-500 flex-shrink-0" disabled>
                            <span class="text-sm text-gray-200">{{ $option->text }}</span>
                            @if($option->is_correct)
                                <span class="ml-auto text-xs text-green-400 font-medium flex-shrink-0"><i class="fas fa-check-circle"></i> Correct</span>
                            @endif
                        </label>
                        @endforeach
                    </div>

                @elseif($question->type === 'true_false')
                    <p class="text-sm text-gray-400 mb-4">Select True or False.</p>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-700 bg-gray-800 hover:border-gray-500 cursor-pointer transition-colors">
                            <input type="radio" name="tf_preview" class="accent-green-500" disabled>
                            <span class="text-sm font-medium text-green-400">True</span>
                            @if($question->true_false_answer === true)
                                <span class="ml-auto text-xs text-green-400 font-medium"><i class="fas fa-check-circle"></i> Correct Answer</span>
                            @endif
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-700 bg-gray-800 hover:border-gray-500 cursor-pointer transition-colors">
                            <input type="radio" name="tf_preview" class="accent-red-500" disabled>
                            <span class="text-sm font-medium text-red-400">False</span>
                            @if($question->true_false_answer === false)
                                <span class="ml-auto text-xs text-green-400 font-medium"><i class="fas fa-check-circle"></i> Correct Answer</span>
                            @endif
                        </label>
                    </div>

                @elseif($question->type === 'fill_in_blank')
                    <p class="text-sm text-gray-400 mb-4">Type your answer in the field below.</p>
                    <input type="text" disabled placeholder="Type your answer here…"
                           class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-3 text-white text-sm font-mono opacity-60 cursor-not-allowed">
                    <div class="mt-3 flex items-center gap-2 text-sm text-green-400">
                        <i class="fas fa-check-circle"></i>
                        <span>Correct answer: <strong class="font-mono">{{ $question->fill_blank_answer }}</strong></span>
                    </div>

                @elseif($question->type === 'essay')
                    <p class="text-sm text-gray-400 mb-4">Write your essay answer below. Your work is saved automatically.</p>
                    <textarea disabled rows="10" placeholder="Write your answer here…"
                              class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-3 text-white text-sm resize-none opacity-60 cursor-not-allowed"></textarea>
                    <p class="mt-2 text-xs text-amber-400"><i class="fas fa-user-edit mr-1"></i> Essay — manually graded by admin</p>
                @endif

            </div>
        </div>
        @endif

        {{-- Hint preview (if any) --}}
        @if($question->hint)
        <div class="mt-6 bg-amber-900/20 border border-amber-700/40 rounded-xl p-5">
            <div class="flex items-center gap-2 text-amber-400 mb-3">
                <i class="fas fa-lightbulb"></i>
                <span class="font-semibold text-sm">Hint (visible if hints enabled)</span>
            </div>
            <div class="md-body" x-ref="hintEl">{{ $question->hint }}</div>
        </div>
        @endif

    </div>

    <script>
        function previewApp() {
            return {
                init() {
                    this.$nextTick(() => {
                        this.renderMarkdown();
                        @if($question->isCoding())
                        this.initMonaco();
                        @endif
                    });
                },

                renderMarkdown() {
                    marked.setOptions({ breaks: true, gfm: true });

                    const descEl = this.$refs.descEl;
                    if (descEl) {
                        descEl.innerHTML = marked.parse(descEl.textContent || '');
                    }

                    @if($question->hint)
                    const hintEl = this.$refs.hintEl;
                    if (hintEl) {
                        hintEl.innerHTML = marked.parse(hintEl.textContent || '');
                    }
                    @endif

                    // KaTeX
                    document.querySelectorAll('.md-body').forEach(el => {
                        renderMathInElement(el, {
                            delimiters: [
                                { left: '$$', right: '$$', display: true },
                                { left: '$', right: '$', display: false },
                            ],
                            throwOnError: false,
                        });
                    });

                    // Mermaid
                    mermaid.initialize({ startOnLoad: false, theme: 'dark' });
                    document.querySelectorAll('.md-body pre code.language-mermaid').forEach(block => {
                        const div = document.createElement('div');
                        div.className = 'mermaid';
                        div.textContent = block.textContent;
                        block.parentElement.replaceWith(div);
                    });
                    mermaid.run();
                },

                @if($question->isCoding())
                initMonaco() {
                    require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' } });
                    require(['vs/editor/editor.main'], () => {
                        const langMap = {
                            'python': 'python', 'python3': 'python',
                            'javascript': 'javascript', 'js': 'javascript',
                            'java': 'java', 'c': 'c', 'cpp': 'cpp', 'c++': 'cpp',
                            'php': 'php', 'ruby': 'ruby', 'go': 'go',
                            'rust': 'rust', 'kotlin': 'kotlin', 'swift': 'swift',
                            'typescript': 'typescript',
                        };
                        const lang = langMap[('{{ $question->language }}').toLowerCase()] || 'plaintext';

                        monaco.editor.create(document.getElementById('monaco-container'), {
                            value: @json($question->starter_code ?? '# Write your solution here\n'),
                            language: lang,
                            theme: 'vs-dark',
                            fontSize: 14,
                            minimap: { enabled: false },
                            scrollBeyondLastLine: false,
                            readOnly: true,
                            padding: { top: 12, bottom: 12 },
                        });
                    });
                },
                @endif
            };
        }
    </script>
</body>
</html>
