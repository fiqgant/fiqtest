<script>
    (() => {
        const root = document.getElementById('language-combobox');
        if (!root) {
            return;
        }

        const hiddenInput = document.getElementById('language-value');
        const searchInput = document.getElementById('language-search');
        const optionsPanel = document.getElementById('language-options');
        const raw = root.dataset.languages || '[]';
        const initial = (root.dataset.initial || '').trim();

        let languages = [];
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                languages = parsed
                    .map((row) => ({
                        id: Number(row.id),
                        name: String(row.name || '').trim(),
                    }))
                    .filter((row) => row.name !== '');
            }
        } catch (_) {
            languages = [];
        }

        let filtered = [...languages];
        let activeIndex = -1;
        let isOpen = false;

        const normalize = (value) => String(value || '').toLowerCase();
        const escapeHtml = (value) => String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const openPanel = () => {
            if (!isOpen) {
                isOpen = true;
                optionsPanel.classList.remove('hidden');
                searchInput.setAttribute('aria-expanded', 'true');
            }
        };

        const closePanel = () => {
            if (isOpen) {
                isOpen = false;
                optionsPanel.classList.add('hidden');
                searchInput.setAttribute('aria-expanded', 'false');
            }
        };

        const updateHiddenByExact = (value) => {
            const exact = languages.find((row) => normalize(row.name) === normalize(value));
            hiddenInput.value = exact ? exact.name : '';
        };

        const highlightMatch = (text, query) => {
            const escapedText = escapeHtml(text);
            if (!query) {
                return escapedText;
            }

            const source = text.toLowerCase();
            const needle = query.toLowerCase();
            const index = source.indexOf(needle);

            if (index < 0) {
                return escapedText;
            }

            const start = escapeHtml(text.slice(0, index));
            const match = escapeHtml(text.slice(index, index + query.length));
            const end = escapeHtml(text.slice(index + query.length));

            return `${start}<span class="bg-amber-100 text-amber-900 rounded px-0.5">${match}</span>${end}`;
        };

        const renderOptions = () => {
            const query = searchInput.value.trim();
            const normalizedQuery = normalize(query);

            filtered = languages.filter((row) => normalize(row.name).includes(normalizedQuery));

            if (filtered.length === 0) {
                activeIndex = -1;
                optionsPanel.innerHTML = '<div class="px-3 py-2 text-sm text-slate-500">No matching language found.</div>';
                return;
            }

            if (activeIndex < 0 || activeIndex >= filtered.length) {
                activeIndex = 0;
            }

            optionsPanel.innerHTML = filtered.map((row, index) => {
                const activeClass = index === activeIndex ? 'bg-indigo-50 text-indigo-900' : 'text-slate-700';
                return `<button type="button" class="language-option flex w-full items-center justify-between px-3 py-2 text-left text-sm ${activeClass}" data-index="${index}" role="option" aria-selected="${index === activeIndex ? 'true' : 'false'}"><span>${highlightMatch(row.name, query)}</span><span class="ml-3 text-xs text-slate-400 font-mono">${row.id}</span></button>`;
            }).join('');

            optionsPanel.querySelectorAll('.language-option').forEach((option) => {
                option.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                });
                option.addEventListener('click', () => {
                    const index = Number(option.dataset.index);
                    if (!Number.isNaN(index)) {
                        selectByIndex(index);
                    }
                });
            });
        };

        const selectByIndex = (index) => {
            if (index < 0 || index >= filtered.length) {
                return;
            }

            const selected = filtered[index];
            activeIndex = index;
            searchInput.value = selected.name;
            hiddenInput.value = selected.name;
            closePanel();
        };

        const moveActive = (direction) => {
            if (filtered.length === 0) {
                return;
            }

            if (activeIndex < 0) {
                activeIndex = direction > 0 ? 0 : filtered.length - 1;
            } else {
                activeIndex = (activeIndex + direction + filtered.length) % filtered.length;
            }

            renderOptions();
        };

        const applyInitial = () => {
            if (!initial) {
                hiddenInput.value = '';
                searchInput.value = '';
                return;
            }

            const exact = languages.find((row) => normalize(row.name) === normalize(initial));
            if (exact) {
                hiddenInput.value = exact.name;
                searchInput.value = exact.name;
                return;
            }

            const startsWithMatch = languages.find((row) => normalize(row.name).startsWith(normalize(initial)));
            if (startsWithMatch) {
                hiddenInput.value = startsWithMatch.name;
                searchInput.value = startsWithMatch.name;
                return;
            }

            hiddenInput.value = '';
            searchInput.value = initial;
        };

        applyInitial();
        renderOptions();

        searchInput.addEventListener('focus', () => {
            renderOptions();
            openPanel();
        });

        searchInput.addEventListener('input', () => {
            activeIndex = 0;
            updateHiddenByExact(searchInput.value);
            renderOptions();
            openPanel();
        });

        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                openPanel();
                moveActive(1);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                openPanel();
                moveActive(-1);
                return;
            }

            if (event.key === 'Enter') {
                if (isOpen && activeIndex >= 0) {
                    event.preventDefault();
                    selectByIndex(activeIndex);
                } else {
                    updateHiddenByExact(searchInput.value);
                }
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closePanel();
                return;
            }

            if (event.key === 'Tab') {
                if (isOpen && activeIndex >= 0) {
                    selectByIndex(activeIndex);
                } else {
                    updateHiddenByExact(searchInput.value);
                }
            }
        });

        searchInput.addEventListener('blur', () => {
            window.setTimeout(() => {
                updateHiddenByExact(searchInput.value);
                closePanel();
            }, 120);
        });

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                closePanel();
            }
        });
    })();
</script>
