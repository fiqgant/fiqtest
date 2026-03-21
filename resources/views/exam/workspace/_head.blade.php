<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} - Coding Exam</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

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
            background: #111111;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #333333;
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
        .md-body img { max-width: 100%; height: auto; border-radius: 6px; margin: 0.75rem 0; display: block; border: 1px solid #334155; }
        .md-body .mermaid { background: #1e293b; border-radius: 8px; padding: 1rem; margin: 0.75rem 0; text-align: center; }
        .md-body .katex { font-size: 1em; }
        .md-body .katex-display { overflow-x: auto; margin: 0.75rem 0; }

        /* ── Light mode overrides ───────────────── */
        body.ws-light { background: #f8fafc !important; color: #0f172a !important; }

        /* backgrounds */
        body.ws-light .bg-black   { background-color: #f1f5f9 !important; }
        body.ws-light .bg-neutral-900 { background-color: #ffffff !important; }
        body.ws-light .bg-neutral-800 { background-color: #f1f5f9 !important; }
        body.ws-light .bg-neutral-700 { background-color: #e2e8f0 !important; }

        /* borders */
        body.ws-light .border-neutral-800 { border-color: #e2e8f0 !important; }
        body.ws-light .border-neutral-700 { border-color: #cbd5e1 !important; }

        /* ALL text — nuclear override so nothing stays invisible */
        body.ws-light * { color: inherit; }
        body.ws-light { color: #1e293b; }
        body.ws-light .text-white  { color: #0f172a !important; }
        body.ws-light .text-gray-100, body.ws-light .text-gray-200 { color: #1e293b !important; }
        body.ws-light .text-gray-300 { color: #334155 !important; }
        body.ws-light .text-gray-400 { color: #475569 !important; }
        body.ws-light .text-gray-500 { color: #64748b !important; }
        body.ws-light .text-gray-600 { color: #64748b !important; }
        body.ws-light .font-mono    { color: #0f172a !important; }

        /* keep accent colors legible on light bg */
        body.ws-light .text-green-400  { color: #16a34a !important; }
        body.ws-light .text-yellow-400 { color: #b45309 !important; }
        body.ws-light .text-red-400    { color: #dc2626 !important; }
        body.ws-light .text-red-500    { color: #dc2626 !important; }
        body.ws-light .text-rose-300   { color: #e11d48 !important; }
        body.ws-light .text-rose-400   { color: #e11d48 !important; }
        body.ws-light .text-rose-500   { color: #e11d48 !important; }
        body.ws-light .text-amber-400  { color: #d97706 !important; }
        body.ws-light .text-amber-500  { color: #b45309 !important; }
        body.ws-light .text-indigo-400 { color: #4f46e5 !important; }
        body.ws-light .text-blue-400   { color: #2563eb !important; }

        /* timer white when not warning */
        body.ws-light .font-mono.text-white { color: #0f172a !important; }

        /* colored action buttons — keep white text */
        body.ws-light .bg-indigo-600,
        body.ws-light .bg-indigo-700 { background-color: #4f46e5 !important; color: #ffffff !important; }
        body.ws-light .bg-indigo-600 *, body.ws-light .bg-indigo-700 * { color: #ffffff !important; }
        body.ws-light .hover\:bg-indigo-700:hover { background-color: #4338ca !important; color: #ffffff !important; }
        body.ws-light .hover\:bg-indigo-700:hover * { color: #ffffff !important; }

        body.ws-light .bg-green-600,
        body.ws-light .bg-green-700 { background-color: #16a34a !important; color: #ffffff !important; }
        body.ws-light .bg-green-600 *, body.ws-light .bg-green-700 * { color: #ffffff !important; }
        body.ws-light .hover\:bg-green-700:hover { background-color: #15803d !important; color: #ffffff !important; }
        body.ws-light .hover\:bg-green-700:hover * { color: #ffffff !important; }

        body.ws-light .bg-blue-600,
        body.ws-light .bg-blue-700 { background-color: #2563eb !important; color: #ffffff !important; }
        body.ws-light .bg-blue-600 *, body.ws-light .bg-blue-700 * { color: #ffffff !important; }
        body.ws-light .hover\:bg-blue-700:hover { background-color: #1d4ed8 !important; color: #ffffff !important; }
        body.ws-light .hover\:bg-blue-700:hover * { color: #ffffff !important; }

        body.ws-light .bg-amber-600,
        body.ws-light .bg-amber-500 { background-color: #d97706 !important; color: #ffffff !important; }
        body.ws-light .bg-amber-600 *, body.ws-light .bg-amber-500 * { color: #ffffff !important; }
        body.ws-light .hover\:bg-amber-500:hover { background-color: #f59e0b !important; color: #1e293b !important; }
        body.ws-light .hover\:bg-amber-500:hover * { color: #1e293b !important; }

        /* neutral buttons — dark text */
        body.ws-light .bg-neutral-700 { color: #1e293b !important; }
        body.ws-light .bg-neutral-700 * { color: #1e293b !important; }

        /* disabled colored buttons */
        body.ws-light .disabled\:bg-neutral-700:disabled { background-color: #e2e8f0 !important; color: #94a3b8 !important; }

        /* hover states */
        body.ws-light .hover\:bg-neutral-700:hover { background-color: #e2e8f0 !important; }
        body.ws-light .hover\:bg-neutral-800:hover { background-color: #f1f5f9 !important; }
        body.ws-light .hover\:bg-neutral-600:hover { background-color: #e2e8f0 !important; }

        /* separator */
        body.ws-light .text-gray-500[class*="|"] { color: #94a3b8 !important; }

        /* scrollbar */
        body.ws-light .scrollbar-thin::-webkit-scrollbar-track { background: #f1f5f9; }
        body.ws-light .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; }

        /* markdown */
        body.ws-light .md-body { color: #334155 !important; }
        body.ws-light .md-body h1, body.ws-light .md-body h2, body.ws-light .md-body h3 { color: #0f172a !important; }
        body.ws-light .md-body p, body.ws-light .md-body li, body.ws-light .md-body span { color: #334155 !important; }
        body.ws-light .md-body code { background: #e2e8f0 !important; color: #0e7490 !important; }
        body.ws-light .md-body pre { background: #f1f5f9 !important; border-color: #e2e8f0 !important; }
        body.ws-light .md-body pre code { color: #334155 !important; }
        body.ws-light .md-body blockquote { color: #64748b !important; }
        body.ws-light .md-body th { background: #e2e8f0 !important; color: #0f172a !important; border-color: #cbd5e1 !important; }
        body.ws-light .md-body td { border-color: #e2e8f0 !important; color: #334155 !important; }
        body.ws-light .md-body tr:nth-child(even) td { background: #f8fafc !important; }
        body.ws-light .md-body strong { color: #0f172a !important; }
        body.ws-light .md-body a { color: #4f46e5 !important; }
        body.ws-light .md-body .mermaid { background: #f1f5f9 !important; }
    </style>
</head>
