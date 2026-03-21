    <div class="flex h-full">
        <aside class="w-64 bg-neutral-900 border-r border-neutral-800 flex flex-col flex-shrink-0">
            <div class="p-4 border-b border-neutral-800">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Questions</h3>
                <div class="text-xs text-gray-500">
                    {{ $attempt->attemptQuestions->count() }} questions
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-2 scrollbar-thin">
                <template x-for="(aq, index) in allQuestions" :key="aq.id">
                    <button @click="switchQuestion(aq.id)"
                       class="block w-full text-left p-3 rounded-lg mb-2 transition-all duration-200"
                       :class="currentAttemptQuestionId === aq.id ? 'bg-indigo-600 ring-2 ring-indigo-400' : 'bg-neutral-800 hover:bg-neutral-700'">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium" x-text="'Q' + (index + 1)"></span>
                            <span x-show="aq.has_answer" class="text-xs text-green-400"><i class="fas fa-check-circle"></i></span>
                        </div>
                        <div class="text-xs mt-1"
                             :class="aq.difficulty === 'easy' ? 'text-green-400' : (aq.difficulty === 'medium' ? 'text-yellow-400' : 'text-red-400')"
                             x-text="aq.difficulty.charAt(0).toUpperCase() + aq.difficulty.slice(1)"></div>
                        <div class="text-xs text-gray-400 mt-1 truncate" x-text="aq.title.substring(0, 25) + (aq.title.length > 25 ? '...' : '')"></div>
                    </button>
                </template>
            </div>
        </aside>
