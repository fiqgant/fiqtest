<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .monaco-editor-container { height: 100%; min-height: 400px; }

        .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: #1f2937; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }

        .difficulty-easy { color: #4ade80; }
        .difficulty-medium { color: #facc15; }
        .difficulty-hard { color: #f87171; }

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
    </style>
</head>
<body class="bg-gray-900 text-white" style="height: calc(100vh - 36px); overflow: hidden; margin-top: 36px;" x-data="previewApp()" @keydown.ctrl.enter.window="runCode()"  @keydown.meta.enter.window="runCode()">

    {{-- Admin preview banner --}}
    <div class="bg-amber-500 text-gray-900 px-4 flex items-center justify-between text-xs font-semibold fixed top-0 left-0 right-0 z-50" style="height:36px">
        <div class="flex items-center gap-2">
            <i class="fas fa-eye"></i>
            <span>ADMIN PREVIEW — This is how students see this question (read-only, answers shown for reference)</span>
        </div>
        <a href="{{ url()->previous() }}" class="flex items-center gap-1.5 bg-gray-900/20 hover:bg-gray-900/30 px-3 py-1 rounded transition-colors">
            <i class="fas fa-arrow-left"></i> Back to Admin
        </a>
    </div>

    {{-- === Exact workspace layout === --}}
    <div class="flex h-full">

        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col flex-shrink-0">
            <div class="p-4 border-b border-gray-700">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Questions</h3>
                <div class="text-xs text-gray-500">1 question (preview)</div>
            </div>
            <div class="flex-1 overflow-y-auto p-2 scrollbar-thin">
                <div class="block w-full text-left p-3 rounded-lg mb-2 bg-indigo-600 ring-2 ring-indigo-400">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Q1</span>
                    </div>
                    <div class="text-xs mt-1 difficulty-{{ $question->difficulty }}">{{ ucfirst($question->difficulty) }}</div>
                    <div class="text-xs text-gray-300 mt-1 truncate">{{ Str::limit($question->title, 25) }}</div>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <main class="flex-1 flex flex-col min-w-0">

            {{-- Header --}}
            <header class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6 flex-shrink-0">
                <div class="flex items-center space-x-4 min-w-0">
                    <h1 class="text-lg font-semibold truncate">Preview Mode</h1>
                    <span class="text-gray-500">|</span>
                    <span class="text-sm text-gray-400">Admin — {{ $question->courseOffering->course->name ?? '' }}</span>
                </div>
                <div class="flex items-center space-x-6 flex-shrink-0">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-mono text-xl font-bold text-white">--:--</span>
                    </div>
                    <button disabled class="bg-indigo-600 opacity-50 px-6 py-2 rounded-lg font-semibold cursor-not-allowed flex items-center space-x-2">
                        <span>Final Submit</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </header>

            {{-- Content: left description + right answer --}}
            <div class="flex-1 flex overflow-hidden">

                {{-- Left: Description --}}
                <div class="w-1/2 border-r border-gray-700 overflow-y-auto scrollbar-thin p-6">
                    <h2 class="text-2xl font-bold mb-4">
                        {{ $question->title }}
                        <span class="text-sm font-normal text-gray-400 ml-2">
                            ({{ ucfirst($question->difficulty) }} &bull; {{ $question->default_weight }} pts)
                        </span>
                    </h2>

                    <div class="md-body" x-ref="descEl">{{ $question->description }}</div>

                    @if($question->isCoding())
                        @php $visibleTc = $question->getVisibleTestCases(); @endphp
                        @if(count($visibleTc) > 0)
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-3">Example Test Cases</h3>
                            @foreach($visibleTc as $tc)
                            <div class="bg-gray-800 rounded-lg p-4 mb-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase mb-1">Input</div>
                                        <pre class="font-mono text-sm bg-gray-900 p-2 rounded">{{ $tc['input'] ?: '(none)' }}</pre>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase mb-1">Output</div>
                                        <pre class="font-mono text-sm bg-gray-900 p-2 rounded">{{ $tc['expected_output'] }}</pre>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        @php $hiddenCount = count($question->getHiddenTestCases()); @endphp
                        @if($hiddenCount > 0)
                        <div class="mt-3 text-xs text-gray-500 flex items-center gap-1.5">
                            <i class="fas fa-eye-slash"></i>
                            {{ $hiddenCount }} hidden test case(s) — not visible to students
                        </div>
                        @endif
                    @endif

                    {{-- Admin-only: show correct answer info in description panel --}}
                    @if($question->type === 'true_false')
                    <div class="mt-6 bg-green-900/20 border border-green-700/40 rounded-lg p-3 text-sm">
                        <span class="text-green-400 font-medium"><i class="fas fa-check-circle mr-1"></i> Correct Answer:</span>
                        <span class="text-white ml-1">{{ $question->true_false_answer ? 'True' : 'False' }}</span>
                    </div>
                    @elseif($question->type === 'fill_in_blank')
                    <div class="mt-6 bg-green-900/20 border border-green-700/40 rounded-lg p-3 text-sm">
                        <span class="text-green-400 font-medium"><i class="fas fa-check-circle mr-1"></i> Correct Answer:</span>
                        <span class="font-mono text-white ml-1">{{ $question->fill_blank_answer }}</span>
                    </div>
                    @elseif(in_array($question->type, ['multiple_choice', 'multiple_select']))
                    <div class="mt-6 bg-green-900/20 border border-green-700/40 rounded-lg p-3 text-sm">
                        <span class="text-green-400 font-medium"><i class="fas fa-check-circle mr-1"></i> Correct Answer(s):</span>
                        <span class="text-white ml-1">{{ $question->options->where('is_correct', true)->pluck('text')->join(', ') }}</span>
                    </div>
                    @endif

                    @if($question->hint)
                    <div class="mt-4 bg-amber-900/20 border border-amber-700/40 rounded-lg p-3 text-sm">
                        <div class="text-amber-400 font-medium mb-1"><i class="fas fa-lightbulb mr-1"></i> Hint (if enabled)</div>
                        <div class="md-body text-xs" x-ref="hintEl">{{ $question->hint }}</div>
                    </div>
                    @endif
                </div>

                {{-- Right: Answer panel --}}
                <div class="w-1/2 flex flex-col min-h-0">

                    @if($question->isCoding())
                    {{-- Coding: editor + output panel --}}
                    <div style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden">
                        <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium">Code Editor</span>
                                <span class="text-xs text-gray-500">({{ $question->language }})</span>
                            </div>
                        </div>

                        <div class="flex-1" id="monaco-editor"></div>

                        <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-t border-gray-700 flex-shrink-0">
                            <div class="flex items-center space-x-2">
                                <button @click="runCode()" :disabled="running"
                                    :class="running ? 'opacity-60 cursor-not-allowed' : 'hover:bg-green-500'"
                                    class="bg-green-600 px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors">
                                    <i class="fas" :class="running ? 'fa-spinner fa-spin' : 'fa-play'"></i>
                                    <span x-text="running ? 'Running…' : 'Run Code'"></span>
                                </button>
                                <button @click="resetCode()" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded-lg font-medium transition-colors">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                            <span class="text-xs text-amber-400"><i class="fas fa-flask mr-1"></i> Admin Test Mode</span>
                        </div>

                        <div class="h-52 bg-gray-900 border-t border-gray-700 flex flex-col flex-shrink-0">
                            <div class="flex items-center justify-between border-b border-gray-700 px-4">
                                <div class="flex">
                                    <button @click="outputTab='output'" :class="outputTab==='output' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-gray-400'" class="px-4 py-2 text-sm font-medium">Output</button>
                                    <button @click="outputTab='tests'" :class="outputTab==='tests' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-gray-400'" class="px-4 py-2 text-sm font-medium">Test Cases</button>
                                </div>
                                <span x-show="lastTime" class="text-xs text-gray-500" x-text="'⏱ ' + lastTime + 'ms'"></span>
                            </div>
                            <div class="flex-1 p-4 overflow-auto font-mono text-sm scrollbar-thin">
                                {{-- Output tab --}}
                                <div x-show="outputTab==='output'">
                                    <template x-if="!output && !error && !running">
                                        <div class="text-gray-500">Click "Run Code" to execute</div>
                                    </template>
                                    <template x-if="error">
                                        <pre class="text-red-400 whitespace-pre-wrap" x-text="error"></pre>
                                    </template>
                                    <template x-if="output && !error">
                                        <pre class="text-green-300 whitespace-pre-wrap" x-text="output"></pre>
                                    </template>
                                </div>
                                {{-- Test Cases tab --}}
                                <div x-show="outputTab==='tests'" class="space-y-2">
                                    <template x-if="testResults.length === 0">
                                        <div class="text-gray-500">Run code to see test case results</div>
                                    </template>
                                    <template x-for="(tc, i) in testResults" :key="i">
                                        <div :class="tc.passed ? 'border-green-700/50 bg-green-900/20' : 'border-red-700/50 bg-red-900/20'" class="border rounded p-3 text-xs">
                                            <div class="flex items-center gap-2 mb-2">
                                                <i :class="tc.passed ? 'fas fa-check text-green-400' : 'fas fa-times text-red-400'"></i>
                                                <span :class="tc.passed ? 'text-green-400' : 'text-red-400'" class="font-semibold" x-text="'Test ' + (i+1) + (tc.hidden ? ' (hidden)' : '')"></span>
                                            </div>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div><div class="text-gray-400 mb-1">Input</div><pre class="bg-gray-900 p-1.5 rounded text-gray-300 whitespace-pre-wrap" x-text="tc.input || '(none)'"></pre></div>
                                                <div><div class="text-gray-400 mb-1">Expected</div><pre class="bg-gray-900 p-1.5 rounded text-emerald-300 whitespace-pre-wrap" x-text="tc.expected"></pre></div>
                                                <div><div class="text-gray-400 mb-1">Got</div><pre class="bg-gray-900 p-1.5 rounded" :class="tc.passed ? 'text-emerald-300' : 'text-red-300'" x-text="tc.got ?? '—'"></pre></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    @else
                    {{-- Non-coding: answer panel --}}
                    <div style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden">
                        <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
                            <span class="text-sm font-medium">Your Answer</span>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 scrollbar-thin">

                            @if($question->type === 'multiple_choice')
                                <p class="text-sm text-gray-400 mb-4">Select one correct answer.</p>
                                <div class="space-y-3">
                                    @foreach($question->options as $option)
                                    <label class="flex items-start gap-3 p-3 rounded-lg border
                                        {{ $option->is_correct ? 'border-green-500/50 bg-green-900/10' : 'border-gray-700 bg-gray-800' }}
                                        cursor-default transition-colors">
                                        <input type="radio" name="mc_preview" class="mt-0.5 accent-indigo-500 flex-shrink-0" disabled {{ $option->is_correct ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-200">{{ $option->text }}</span>
                                        @if($option->is_correct)
                                            <span class="ml-auto text-xs text-green-400 font-medium flex-shrink-0 whitespace-nowrap"><i class="fas fa-check-circle"></i> Correct</span>
                                        @endif
                                    </label>
                                    @endforeach
                                </div>

                            @elseif($question->type === 'multiple_select')
                                <p class="text-sm text-gray-400 mb-4">Select all that apply.</p>
                                <div class="space-y-3">
                                    @foreach($question->options as $option)
                                    <label class="flex items-start gap-3 p-3 rounded-lg border
                                        {{ $option->is_correct ? 'border-green-500/50 bg-green-900/10' : 'border-gray-700 bg-gray-800' }}
                                        cursor-default transition-colors">
                                        <input type="checkbox" class="mt-0.5 accent-indigo-500 flex-shrink-0" disabled {{ $option->is_correct ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-200">{{ $option->text }}</span>
                                        @if($option->is_correct)
                                            <span class="ml-auto text-xs text-green-400 font-medium flex-shrink-0 whitespace-nowrap"><i class="fas fa-check-circle"></i> Correct</span>
                                        @endif
                                    </label>
                                    @endforeach
                                </div>

                            @elseif($question->type === 'true_false')
                                <p class="text-sm text-gray-400 mb-4">Select True or False.</p>
                                <div class="space-y-3">
                                    <label class="flex items-center gap-3 p-4 rounded-lg border
                                        {{ $question->true_false_answer === true ? 'border-green-500 bg-green-900/30' : 'border-gray-700 bg-gray-800' }}
                                        cursor-default transition-colors">
                                        <input type="radio" name="tf_preview" class="accent-green-500" disabled {{ $question->true_false_answer ? 'checked' : '' }}>
                                        <span class="text-sm font-medium text-green-400">True</span>
                                        @if($question->true_false_answer === true)
                                            <span class="ml-auto text-xs text-green-400 font-medium"><i class="fas fa-check-circle"></i> Correct Answer</span>
                                        @endif
                                    </label>
                                    <label class="flex items-center gap-3 p-4 rounded-lg border
                                        {{ $question->true_false_answer === false ? 'border-green-500 bg-green-900/30' : 'border-gray-700 bg-gray-800' }}
                                        cursor-default transition-colors">
                                        <input type="radio" name="tf_preview" class="accent-red-500" disabled {{ !$question->true_false_answer ? 'checked' : '' }}>
                                        <span class="text-sm font-medium text-red-400">False</span>
                                        @if($question->true_false_answer === false)
                                            <span class="ml-auto text-xs text-green-400 font-medium"><i class="fas fa-check-circle"></i> Correct Answer</span>
                                        @endif
                                    </label>
                                </div>

                            @elseif($question->type === 'fill_in_blank')
                                <p class="text-sm text-gray-400 mb-4">Type your answer in the field below.</p>
                                <input type="text" disabled placeholder="{{ $question->fill_blank_answer }}"
                                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white text-sm font-mono focus:border-indigo-500 focus:outline-none opacity-70 cursor-not-allowed">

                            @elseif($question->type === 'essay')
                                <p class="text-sm text-gray-400 mb-4">Write your essay answer below. Your work is saved automatically.</p>
                                <textarea disabled rows="16"
                                    class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white text-sm resize-none focus:border-indigo-500 focus:outline-none scrollbar-thin opacity-60 cursor-not-allowed"
                                    placeholder="Write your answer here…"></textarea>
                                <p class="mt-2 text-xs text-amber-400"><i class="fas fa-user-edit mr-1"></i> Essay — manually graded by admin</p>
                            @endif

                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </main>
    </div>

    <script>
        function previewApp() {
            return {
                running: false,
                output: '',
                error: '',
                lastTime: null,
                outputTab: 'output',
                testResults: [],
                editor: null,

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

                    document.querySelectorAll('.md-body').forEach(el => {
                        renderMathInElement(el, {
                            delimiters: [
                                { left: '$$', right: '$$', display: true },
                                { left: '$', right: '$', display: false },
                            ],
                            throwOnError: false,
                        });
                    });

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

                        this.editor = monaco.editor.create(document.getElementById('monaco-editor'), {
                            value: @json($question->starter_code ?? '# Write your solution here\n'),
                            language: lang,
                            theme: 'vs-dark',
                            fontSize: 14,
                            minimap: { enabled: false },
                            scrollBeyondLastLine: false,
                            padding: { top: 12, bottom: 12 },
                        });
                    });
                },

                resetCode() {
                    if (this.editor) {
                        this.editor.setValue(@json($question->starter_code ?? '# Write your solution here\n'));
                    }
                },

                async runCode() {
                    if (this.running || !this.editor) return;
                    this.running = true;
                    this.output = '';
                    this.error = '';
                    this.lastTime = null;
                    this.testResults = [];
                    this.outputTab = 'tests';

                    const code = this.editor.getValue();
                    const testCases = @json($question->test_cases ?? []);
                    const csrfToken = document.querySelector('meta[name=csrf-token]').content;
                    const runUrl = '{{ route('admin.questions.preview.run', $question) }}';

                    try {
                        for (const tc of testCases) {
                            const res = await fetch(runUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({ code, stdin: tc.input ?? '' }),
                            });
                            const data = await res.json();
                            const got      = (data.output ?? '').trimEnd();
                            const expected = (tc.expected_output ?? '').trimEnd();
                            this.testResults.push({
                                input: tc.input,
                                expected,
                                got,
                                passed: got === expected && !data.error,
                                hidden: tc.is_hidden ?? false,
                                time: data.time,
                                error: data.error,
                            });
                            this.lastTime = data.time ?? this.lastTime;

                            // Stop early on runtime error
                            if (data.error) {
                                this.error = data.error;
                                this.outputTab = 'output';
                                break;
                            }
                        }

                        if (!this.error) {
                            const passed = this.testResults.filter(r => r.passed).length;
                            const total  = this.testResults.length;
                            this.output = `✓ ${passed}/${total} test cases passed`;
                        }
                    } catch (e) {
                        this.error = 'Request failed: ' + e.message;
                        this.outputTab = 'output';
                    } finally {
                        this.running = false;
                    }
                },
                @endif
            };
        }
    </script>
</body>
</html>
