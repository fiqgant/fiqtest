            <header class="h-16 bg-neutral-900 border-b border-neutral-800 flex items-center justify-between px-6 flex-shrink-0">
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

                    <button @click="toggleTheme()"
                            class="bg-neutral-700 hover:bg-neutral-600 w-9 h-9 rounded-lg flex items-center justify-center transition-colors duration-200"
                            :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                        <i class="fas fa-sun text-amber-400 text-sm" x-show="isDark"></i>
                        <i class="fas fa-moon text-indigo-400 text-sm" x-show="!isDark"></i>
                    </button>

                    <button @click="toggleFullscreen()"
                            class="bg-neutral-700 hover:bg-neutral-800 px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center space-x-2"
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
