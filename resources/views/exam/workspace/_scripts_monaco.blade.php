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
        window.__currentAttemptQuestionId = currentAttemptQuestionId;
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
            textArea.className = 'h-full w-full resize-none bg-black p-4 font-mono text-sm text-white outline-none';
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

                const savedWsTheme = localStorage.getItem('exam-ws-theme');
                editor = monaco.editor.create(container, {
                    value: currentCode || starterCode || '# Write your code here\n',
                    language: monacoLanguage,
                    theme: savedWsTheme === 'light' ? 'vs' : 'vs-dark',
                    automaticLayout: true,
                    minimap: { enabled: false },
                    fontSize: 14,
                    lineNumbers: 'on',
                    scrollBeyondLastLine: false,
                    padding: { top: 16 },
                    tabSize: 4,
                    insertSpaces: true,
                });

                window.monacoEditor = editor;

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
                showTimeUpModal: false,
                timeUpCountdown: 5,
                showTabWarningModal: false,
                showInactivityWarningModal: false,
                showDisqualificationModal: false,
                showHintModal: false,
                hintLoading: false,
                hintUsedCount: 0,
                hintsEnabled: workspaceConfig.hintsEnabled || false,
                maxHintsPerQuestion: Number(workspaceConfig.maxHintsPerQuestion || 1),
                get hintLimitReached() {
                    return this.maxHintsPerQuestion > 0 && this.hintUsedCount >= this.maxHintsPerQuestion;
                },
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
                // Non-coding answer state
                studentAnswer: '',
                msSelected: [],
                tabSwitchCount: Number(workspaceConfig.initialTabSwitchCount || 0),
                maxTabSwitches: Number(workspaceConfig.maxTabSwitches || 0),
                tabSwitchWarningCount: Number(workspaceConfig.tabSwitchWarningCount || 1),
                inactivityLimitSeconds: Number(workspaceConfig.inactivityLimitSeconds || 0),
                inactivityWarningSeconds: Number(workspaceConfig.inactivityWarningSeconds || 15),
                inactivityWarningRemainingSeconds: 0,
                inactivityWarningTickHandle: null,
                inactivityWarningShown: false,
                inactivityWarningPending: false,
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
                isDark: localStorage.getItem('exam-ws-theme') !== 'light',

                init() {
                    if (this.monitoringInitialized) {
                        return;
                    }

                    // Apply saved theme
                    if (!this.isDark) document.body.classList.add('ws-light');

                    this.monitoringInitialized = true;
                    examAppInstance = this;

                    // Init hint count + student_answer for first question
                    const firstQ = this.allQuestions.find(q => q.id === this.currentAttemptQuestionId);
                    if (firstQ) {
                        this.hintUsedCount  = firstQ.hint_used_count || 0;
                        this.studentAnswer  = firstQ.student_answer  || '';
                        this.msSelected     = this.studentAnswer ? this.studentAnswer.split(',').filter(Boolean) : [];
                    }
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
                            this.inactivityWarningPending = true;
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

                    // If inactivity warning is showing, ignore activity until user manually dismisses
                    if (this.inactivityWarningPending) {
                        return;
                    }

                    this.lastUserActivityAtMs = Date.now();

                    this.scheduleInactivityTimeout();

                    const nowMs = Date.now();
                    if (nowMs - this.lastActivitySyncAt >= 15000) {
                        this.lastActivitySyncAt = nowMs;
                        this.reportActivityEvent('activity');
                    }
                },

                dismissInactivityWarning() {
                    this.inactivityWarningPending = false;
                    this.inactivityWarningShown = false;
                    this.showInactivityWarningModal = false;
                    stopWarningLoop();
                    this.lastUserActivityAtMs = Date.now();
                    this.scheduleInactivityTimeout();
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

                    // Autosave current answer before switching
                    await this.autosave(true);

                    // Save current answer back to allQuestions cache
                    const prev = this.allQuestions.find(q => q.id === this.currentAttemptQuestionId);
                    if (prev) {
                        if (prev.type === 'coding') {
                            prev.code       = getCodeValue();
                            prev.has_answer = prev.code && prev.code.length > prev.starter_code.length;
                        } else {
                            prev.student_answer = this.studentAnswer;
                            prev.has_answer     = this.studentAnswer !== '';
                        }
                    }

                    // Switch to new question
                    this.currentAttemptQuestionId = aqId;
                    currentAttemptQuestionId      = aqId;
                    window.__currentAttemptQuestionId = aqId;
                    this.showHintModal = false;

                    const next = this.allQuestions.find(q => q.id === aqId);
                    if (next) {
                        this.hintUsedCount = next.hint_used_count || 0;

                        if (next.type === 'coding') {
                            const nextCode = next.code || next.starter_code || '';
                            setCodeValue(nextCode);

                            if (editor && window.monaco) {
                                const newLang = resolveMonacoLanguage(next.language);
                                const model   = editor.getModel();
                                if (model) {
                                    monaco.editor.setModelLanguage(model, newLang);
                                }
                            }

                            starterCode                     = next.starter_code || '';
                            workspaceConfig.attemptQuestionId = aqId;
                            workspaceConfig.starterCode       = next.starter_code || '';

                            // Re-measure Monaco after x-show makes the panel visible
                            this.$nextTick(() => {
                                setTimeout(() => { if (editor) editor.layout(); }, 50);
                            });
                        } else {
                            this.studentAnswer = next.student_answer || '';
                            this.msSelected    = this.studentAnswer ? this.studentAnswer.split(',').filter(Boolean) : [];
                        }
                    }

                    // Clear coding output panel
                    this.output      = '';
                    this.error       = '';
                    this.testResults = [];
                    this.activeTab   = 'output';
                },

                toggleTheme() {
                    this.isDark = !this.isDark;
                    document.body.classList.toggle('ws-light', !this.isDark);
                    localStorage.setItem('exam-ws-theme', this.isDark ? 'dark' : 'light');
                    // Update Monaco editor theme if loaded
                    if (window.monacoEditor) {
                        window.monaco.editor.setTheme(this.isDark ? 'vs-dark' : 'vs');
                    }
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
                    this.inactivityWarningPending = false;
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
                    if (this.saving) return;

                    const q = this.currentQuestion;
                    if (!q) return;

                    const isCoding = q.type === 'coding';

                    // For coding: need editor; for others: always can save
                    if (isCoding && !editor && !fallbackEditor) return;

                    this.saving = true;
                    this.saved  = false;

                    try {
                        let body;
                        if (isCoding) {
                            const codeValue = getCodeValue();
                            if (!force && codeValue === currentCode) {
                                this.saved = true;
                                setTimeout(() => { this.saved = false; }, 1200);
                                return;
                            }
                            body = { attempt_question_id: currentAttemptQuestionId, code: codeValue };
                            currentCode = codeValue;
                        } else {
                            body = { attempt_question_id: currentAttemptQuestionId, student_answer: this.studentAnswer };
                        }

                        await fetch(String(workspaceConfig.autosaveUrl || ''), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': String(workspaceConfig.csrfToken || '') },
                            body: JSON.stringify(body),
                        });

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

                async requestHint() {
                    if (this.hintLoading || this.hintLimitReached || !this.currentQuestion) return;

                    this.hintLoading = true;
                    try {
                        const baseUrl = workspaceConfig.hintBaseUrl || '';
                        const url = baseUrl.replace('/0', '/' + this.currentAttemptQuestionId);

                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': workspaceConfig.csrfToken,
                                'Accept': 'application/json',
                            },
                        });

                        const data = await res.json();

                        if (!res.ok) {
                            alert(data.error || 'Failed to load hint.');
                            return;
                        }

                        // Update count in local cache
                        this.hintUsedCount = data.used;
                        const q = this.allQuestions.find(q => q.id === this.currentAttemptQuestionId);
                        if (q) q.hint_used_count = data.used;

                        // Render hint markdown into modal element
                        const el = this.$refs.hintContentEl;
                        if (el) {
                            renderMarkdown(el, data.hint);
                        }
                        this.showHintModal = true;
                    } catch (e) {
                        alert('Failed to load hint. Please try again.');
                    } finally {
                        this.hintLoading = false;
                    }
                },

                // ── Non-coding answer helpers ─────────────────────────
                selectAnswer(value) {
                    this.studentAnswer = value;
                    const q = this.currentQuestion;
                    if (q) q.student_answer = value;
                    scheduleAutosave();
                },

                toggleMsOption(id) {
                    if (this.msSelected.includes(id)) {
                        this.msSelected = this.msSelected.filter(x => x !== id);
                    } else {
                        this.msSelected = [...this.msSelected, id];
                    }
                    this.studentAnswer = this.msSelected.join(',');
                    const q = this.currentQuestion;
                    if (q) q.student_answer = this.studentAnswer;
                    scheduleAutosave();
                },

                onTextAnswer(value) {
                    this.studentAnswer = value;
                    const q = this.currentQuestion;
                    if (q) q.student_answer = value;
                    scheduleAutosave();
                },

                async submitExam() {
                    // Save current answer first
                    await this.autosave(true);
                    this.isPageNavigating = true;
                    document.getElementById('submit-exam-form').submit();
                },

                handleTimeUp() {
                    if (this.showTimeUpModal) return;
                    this.showTimeUpModal = true;
                    this.timeUpCountdown = 5;
                    const interval = setInterval(() => {
                        this.timeUpCountdown--;
                        if (this.timeUpCountdown <= 0) {
                            clearInterval(interval);
                            this.submitExam();
                        }
                    }, 1000);
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
