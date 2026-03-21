                <div class="w-1/2 flex flex-col min-h-0">

                    {{-- ── CODING: Monaco editor ─────────────────────────────── --}}
                    <div :style="(currentQuestion && currentQuestion.type === 'coding') ? {display:'flex',flexDirection:'column',flex:'1',minHeight:'0',overflow:'hidden'} : {display:'none'}"
                         style="display:none">
                            <div class="flex items-center justify-between px-4 py-2 bg-neutral-900 border-b border-neutral-800">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium">Code Editor</span>
                                    <span class="text-xs text-gray-500" x-text="currentQuestion ? '(' + (currentQuestion.language || '') + ')' : ''"></span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span x-show="saving" class="text-xs text-gray-400"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                                    <span x-show="saved && !saving" class="text-xs text-green-400"><i class="fas fa-check"></i> Saved</span>
                                </div>
                            </div>

                            <div class="flex-1" id="monaco-editor"></div>

                            <div class="flex items-center justify-between px-4 py-3 bg-neutral-900 border-t border-neutral-800 flex-shrink-0">
                                <div class="flex items-center space-x-2">
                                    <button @click="runCode()" :disabled="running"
                                            class="bg-green-600 hover:bg-green-700 disabled:bg-neutral-700 px-4 py-2 rounded-lg font-medium flex items-center space-x-2 transition-colors">
                                        <span x-show="!running"><i class="fas fa-play"></i> Run Code</span>
                                        <span x-show="running"><i class="fas fa-spinner fa-spin"></i> Running...</span>
                                    </button>
                                    <button @click="autosave(true)" :disabled="saving"
                                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-neutral-700 px-4 py-2 rounded-lg font-medium transition-colors">
                                        <span x-show="!saving"><i class="fas fa-save"></i> Save Now</span>
                                        <span x-show="saving"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                                    </button>
                                    <template x-if="hintsEnabled && currentQuestion?.has_hint">
                                        <button @click="requestHint()" :disabled="hintLoading || hintLimitReached"
                                                class="bg-amber-600 hover:bg-amber-500 disabled:bg-neutral-700 disabled:opacity-50 px-4 py-2 rounded-lg font-medium flex items-center space-x-2 transition-colors"
                                                :title="hintLimitReached ? 'Hint limit reached for this question' : 'Show hint'">
                                            <span x-show="!hintLoading">
                                                <i class="fas fa-lightbulb"></i> Hint
                                                <template x-if="maxHintsPerQuestion > 0">
                                                    <span class="text-xs opacity-75" x-text="'(' + hintUsedCount + '/' + maxHintsPerQuestion + ')'"></span>
                                                </template>
                                            </span>
                                            <span x-show="hintLoading"><i class="fas fa-spinner fa-spin"></i></span>
                                        </button>
                                    </template>
                                </div>
                                <button @click="resetCode()" class="bg-neutral-700 hover:bg-neutral-800 px-4 py-2 rounded-lg font-medium transition-colors">Reset</button>
                            </div>

                            <div class="h-72 bg-black border-t border-neutral-800 flex flex-col flex-shrink-0">
                                <div class="flex border-b border-neutral-800">
                                    <button @click="activeTab = 'output'" class="px-4 py-2 text-sm font-medium"
                                            :class="activeTab === 'output' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-gray-400 hover:text-white'">Output</button>
                                    <button @click="activeTab = 'testcases'" class="px-4 py-2 text-sm font-medium"
                                            :class="activeTab === 'testcases' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-gray-400 hover:text-white'">Test Results</button>
                                </div>
                                <div class="flex-1 p-4 overflow-auto font-mono text-sm scrollbar-thin">
                                    <div x-show="activeTab === 'output'">
                                        <pre x-show="output" :class="outputStatus === 'success' ? 'text-green-400' : 'text-red-400'" x-text="output"></pre>
                                        <pre x-show="error" class="text-red-400" x-text="error"></pre>
                                        <div x-show="!output && !error" class="text-gray-500">Click "Run Code" to execute your solution</div>
                                    </div>
                                    <div x-show="activeTab === 'testcases'" x-cloak>
                                        <template x-if="testResults.length > 0">
                                            <div>
                                                <div class="flex items-center justify-between mb-3">
                                                    <span class="text-sm text-gray-400">Test Results</span>
                                                    <span class="text-sm font-medium" :class="allTestsPassed ? 'text-green-400' : 'text-red-400'">
                                                        <span x-text="passedCount"></span>/<span x-text="testResults.length"></span> passed
                                                    </span>
                                                </div>
                                                <template x-for="(result, index) in testResults" :key="index">
                                                    <div class="flex items-center justify-between p-2 rounded mb-1" :class="result.is_correct ? 'bg-green-900/30' : 'bg-red-900/30'">
                                                        <span class="text-sm">
                                                            <i :class="result.is_correct ? 'fas fa-check-circle text-green-400' : 'fas fa-times-circle text-red-400'" class="mr-2"></i>
                                                            Test <span x-text="index + 1"></span>
                                                        </span>
                                                        <span class="text-xs text-gray-400" x-text="result.execution_time_ms + 'ms'"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <div x-show="testResults.length === 0" class="text-gray-500">Run code to see test results</div>
                                    </div>
                                </div>
                            </div>
                    </div>

                    {{-- ── NON-CODING: Answer panel ──────────────────────────── --}}
                    <div :style="(currentQuestion && currentQuestion.type !== 'coding') ? {display:'flex',flexDirection:'column',flex:'1',minHeight:'0',overflow:'hidden'} : {display:'none'}"
                         style="display:none">
                            <div class="flex items-center justify-between px-4 py-2 bg-neutral-900 border-b border-neutral-800">
                                <span class="text-sm font-medium">Your Answer</span>
                                <div class="flex items-center space-x-2">
                                    <span x-show="saving" class="text-xs text-gray-400"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                                    <span x-show="saved && !saving" class="text-xs text-green-400"><i class="fas fa-check"></i> Saved</span>
                                    <button @click="autosave(true)" :disabled="saving"
                                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-neutral-700 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                        <span x-show="!saving"><i class="fas fa-save"></i> Save</span>
                                        <span x-show="saving"><i class="fas fa-spinner fa-spin"></i></span>
                                    </button>
                                    <template x-if="hintsEnabled && currentQuestion?.has_hint">
                                        <button @click="requestHint()" :disabled="hintLoading || hintLimitReached"
                                                class="bg-amber-600 hover:bg-amber-500 disabled:bg-neutral-700 disabled:opacity-50 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                                :title="hintLimitReached ? 'Hint limit reached' : 'Show hint'">
                                            <span x-show="!hintLoading">
                                                <i class="fas fa-lightbulb"></i> Hint
                                                <template x-if="maxHintsPerQuestion > 0">
                                                    <span class="text-xs opacity-75" x-text="'(' + hintUsedCount + '/' + maxHintsPerQuestion + ')'"></span>
                                                </template>
                                            </span>
                                            <span x-show="hintLoading"><i class="fas fa-spinner fa-spin"></i></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto p-6 scrollbar-thin">

                                {{-- Multiple Choice --}}
                                <template x-if="currentQuestion.type === 'multiple_choice'">
                                    <div class="space-y-3">
                                        <p class="text-sm text-gray-400 mb-4">Select one correct answer.</p>
                                        <template x-for="option in currentQuestion.options" :key="option.id">
                                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                                                   :class="studentAnswer === String(option.id) ? 'border-indigo-500 bg-indigo-900/30' : 'border-neutral-800 bg-neutral-900 hover:border-gray-500'">
                                                <input type="radio"
                                                       :name="'mc_' + currentAttemptQuestionId"
                                                       :value="String(option.id)"
                                                       :checked="studentAnswer === String(option.id)"
                                                       @change="selectAnswer(String(option.id))"
                                                       class="mt-0.5 accent-indigo-500 flex-shrink-0">
                                                <span class="text-sm text-gray-200" x-text="option.text"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>

                                {{-- Multiple Select --}}
                                <template x-if="currentQuestion.type === 'multiple_select'">
                                    <div class="space-y-3">
                                        <p class="text-sm text-gray-400 mb-4">Select all that apply.</p>
                                        <template x-for="option in currentQuestion.options" :key="option.id">
                                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                                                   :class="msSelected.includes(String(option.id)) ? 'border-indigo-500 bg-indigo-900/30' : 'border-neutral-800 bg-neutral-900 hover:border-gray-500'">
                                                <input type="checkbox"
                                                       :value="String(option.id)"
                                                       :checked="msSelected.includes(String(option.id))"
                                                       @change="toggleMsOption(String(option.id))"
                                                       class="mt-0.5 accent-indigo-500 flex-shrink-0">
                                                <span class="text-sm text-gray-200" x-text="option.text"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>

                                {{-- True / False --}}
                                <template x-if="currentQuestion.type === 'true_false'">
                                    <div class="space-y-3">
                                        <p class="text-sm text-gray-400 mb-4">Select True or False.</p>
                                        <label class="flex items-center gap-3 p-4 rounded-lg border cursor-pointer transition-colors"
                                               :class="studentAnswer === '1' ? 'border-green-500 bg-green-900/30' : 'border-neutral-800 bg-neutral-900 hover:border-gray-500'">
                                            <input type="radio" :name="'tf_' + currentAttemptQuestionId" value="1"
                                                   :checked="studentAnswer === '1'" @change="selectAnswer('1')"
                                                   class="accent-green-500">
                                            <span class="text-sm font-medium text-green-400">True</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-4 rounded-lg border cursor-pointer transition-colors"
                                               :class="studentAnswer === '0' ? 'border-red-500 bg-red-900/30' : 'border-neutral-800 bg-neutral-900 hover:border-gray-500'">
                                            <input type="radio" :name="'tf_' + currentAttemptQuestionId" value="0"
                                                   :checked="studentAnswer === '0'" @change="selectAnswer('0')"
                                                   class="accent-red-500">
                                            <span class="text-sm font-medium text-red-400">False</span>
                                        </label>
                                    </div>
                                </template>

                                {{-- Fill in the Blank --}}
                                <template x-if="currentQuestion.type === 'fill_in_blank'">
                                    <div>
                                        <p class="text-sm text-gray-400 mb-4">Type your answer in the field below.</p>
                                        <input type="text"
                                               :value="studentAnswer"
                                               @input="onTextAnswer($event.target.value)"
                                               class="w-full bg-neutral-900 border border-neutral-700 rounded-lg px-4 py-3 text-white text-sm font-mono focus:border-indigo-500 focus:outline-none"
                                               placeholder="Type your answer here…">
                                    </div>
                                </template>

                                {{-- Essay --}}
                                <template x-if="currentQuestion.type === 'essay'">
                                    <div>
                                        <p class="text-sm text-gray-400 mb-4">Write your essay answer below. Your work is saved automatically.</p>
                                        <textarea
                                            :value="studentAnswer"
                                            @input="onTextAnswer($event.target.value)"
                                            rows="16"
                                            class="w-full bg-neutral-900 border border-neutral-700 rounded-lg px-4 py-3 text-white text-sm resize-none focus:border-indigo-500 focus:outline-none scrollbar-thin"
                                            placeholder="Write your answer here…"></textarea>
                                    </div>
                                </template>

                            </div>
                    </div>

