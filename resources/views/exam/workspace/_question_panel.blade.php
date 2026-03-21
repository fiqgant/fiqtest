            <div class="flex-1 flex overflow-hidden">
                <div class="w-1/2 border-r border-neutral-800 overflow-y-auto scrollbar-thin p-6">
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
                                        <div class="bg-neutral-900 rounded-lg p-4 mb-3">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs text-gray-400 uppercase mb-1">Input</div>
                                                    <pre class="font-mono text-sm bg-black p-2 rounded" x-text="tc.input"></pre>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-gray-400 uppercase mb-1">Output</div>
                                                    <pre class="font-mono text-sm bg-black p-2 rounded" x-text="tc.expected_output"></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
