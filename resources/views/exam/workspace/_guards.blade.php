    @if($exam->disable_inspect)
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
    @endif

    @if($exam->detect_copy_paste)
    <script>
        (function() {
            const logUrl   = '{{ route('exam.log-clipboard', $attempt->id) }}';
            const csrfToken = '{{ csrf_token() }}';

            function getCurrentAttemptQuestionId() {
                // Try to get from Alpine/examApp state if available
                try {
                    return window.__currentAttemptQuestionId ?? null;
                } catch(e) { return null; }
            }

            function sendLog(eventType, content) {
                navigator.sendBeacon
                    ? navigator.sendBeacon(logUrl, (() => {
                        const fd = new FormData();
                        fd.append('_token', csrfToken);
                        fd.append('event_type', eventType);
                        fd.append('content', content ?? '');
                        const aqId = getCurrentAttemptQuestionId();
                        if (aqId) fd.append('attempt_question_id', aqId);
                        return fd;
                    })())
                    : fetch(logUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ event_type: eventType, content: content ?? '', attempt_question_id: getCurrentAttemptQuestionId() }),
                        keepalive: true,
                    });
            }

            // Detect copy & cut — log selected text
            document.addEventListener('copy', function(e) {
                const selected = window.getSelection()?.toString() ?? '';
                sendLog('copy', selected.slice(0, 2000));
            });
            document.addEventListener('cut', function(e) {
                const selected = window.getSelection()?.toString() ?? '';
                sendLog('cut', selected.slice(0, 2000));
            });

            // Detect paste — log pasted text
            document.addEventListener('paste', function(e) {
                const text = e.clipboardData?.getData('text') ?? '';
                sendLog('paste', text.slice(0, 2000));
            });

            // Detect Ctrl+C / Ctrl+X / Ctrl+V via keyboard (backup, covers edge cases)
            document.addEventListener('keydown', function(e) {
                if (!e.ctrlKey && !e.metaKey) return;
                if (e.key === 'c') sendLog('copy', window.getSelection()?.toString()?.slice(0, 2000) ?? '');
                if (e.key === 'x') sendLog('cut',  window.getSelection()?.toString()?.slice(0, 2000) ?? '');
            });
        })();
    </script>
    @endif
