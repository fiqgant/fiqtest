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

        // Configure marked once (not inside render function)
        marked.use({
            breaks: true,
            gfm: true,
            renderer: {
                code({ text, lang }) {
                    if (lang === 'mermaid') {
                        return `<div class="mermaid">${text}</div>`;
                    }
                    return false;
                },
            }
        });

        // Markdown + LaTeX + Mermaid renderer
        function renderMarkdown(el, raw) {
            if (!el) return;

            if (!raw || !raw.trim()) {
                el.innerHTML = '';
                return;
            }

            // Step 1: extract LaTeX before marked touches it
            const { out: safeRaw, store } = extractLatex(raw);

            // Step 2: parse markdown
            let html = marked.parse(safeRaw);

            // Step 3: restore LaTeX as rendered KaTeX HTML
            html = restoreLatex(html, store);

            el.innerHTML = html;

            // Step 4: Mermaid
            const mermaidNodes = el.querySelectorAll('.mermaid');
            if (mermaidNodes.length) {
                mermaid.run({ nodes: mermaidNodes });
            }
        }

        // Expose to Alpine scope
        document.addEventListener('alpine:init', () => {
            Alpine.magic('renderMarkdown', () => renderMarkdown);
        });

        // Prevent back button from showing stale exam after submission
        // pageshow fires when page is restored from bfcache (browser back) — does NOT affect
        // in-page navigation, fullscreen, question switching, or autosave.
        window.addEventListener('pageshow', function (e) {
            const status = '{{ $attempt->status }}';
            if (e.persisted || (window.performance && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
                if (status !== 'in_progress') {
                    window.location.replace('{{ route('exam.submitted', $attempt->id) }}');
                }
            }
        });
    </script>
