import { marked } from 'marked';
import DOMPurify from 'dompurify';
import hljs from 'highlight.js';
import mermaid from 'mermaid';

document.addEventListener('DOMContentLoaded', () => {
    const editorArea = document.getElementById('markdown-editor');
    const previewArea = document.getElementById('editor-preview');
    const titleInput = document.getElementById('document-title');
    const versionInput = document.getElementById('document-version');
    const autosaveStatusEl = document.getElementById('autosave-status');
    const autosaveUrl = document.getElementById('autosave-url')?.value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const btnUndo = document.getElementById('btn-undo');
    const btnRedo = document.getElementById('btn-redo');

    function isDarkTheme() {
        const meta = document.querySelector('meta[name="color-scheme"]')?.getAttribute('content');
        if (meta === 'dark') return true;
        if (meta === 'light') return false;
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function initMermaidTheme() {
        mermaid.initialize({
            startOnLoad: false,
            theme: isDarkTheme() ? 'dark' : 'default',
            securityLevel: 'loose',
            fontFamily: 'Instrument Sans, sans-serif'
        });
    }

    // Initialize Mermaid
    initMermaidTheme();

    let renderTimeout = null;
    let autosaveTimeout = null;
    let historyTimeout = null;
    let isSaving = false;

    // --- UNDO / REDO HISTORY ENGINE ---
    const history = [];
    let historyIndex = -1;
    const MAX_HISTORY = 150;

    function saveHistoryState(force = false) {
        if (!editorArea) return;

        const currentVal = editorArea.value;
        const currentStart = editorArea.selectionStart;
        const currentEnd = editorArea.selectionEnd;

        // Avoid pushing exact duplicate value unless forced
        if (!force && historyIndex >= 0 && history[historyIndex].value === currentVal) {
            return;
        }

        // Truncate any redo branch if we are in the middle of history
        if (historyIndex < history.length - 1) {
            history.splice(historyIndex + 1);
        }

        history.push({
            value: currentVal,
            selectionStart: currentStart,
            selectionEnd: currentEnd
        });

        if (history.length > MAX_HISTORY) {
            history.shift();
        } else {
            historyIndex++;
        }

        updateUndoRedoUI();
    }

    function undo() {
        if (historyIndex > 0) {
            historyIndex--;
            const state = history[historyIndex];
            editorArea.value = state.value;
            editorArea.setSelectionRange(state.selectionStart, state.selectionEnd);
            editorArea.focus();
            updatePreview();
            updateUndoRedoUI();

            // Trigger autosave if needed
            triggerAutosave();
        }
    }

    function redo() {
        if (historyIndex < history.length - 1) {
            historyIndex++;
            const state = history[historyIndex];
            editorArea.value = state.value;
            editorArea.setSelectionRange(state.selectionStart, state.selectionEnd);
            editorArea.focus();
            updatePreview();
            updateUndoRedoUI();

            // Trigger autosave if needed
            triggerAutosave();
        }
    }

    function updateUndoRedoUI() {
        if (btnUndo) {
            const canUndo = historyIndex > 0;
            btnUndo.disabled = !canUndo;
            btnUndo.classList.toggle('opacity-30', !canUndo);
            btnUndo.classList.toggle('opacity-100', canUndo);
        }
        if (btnRedo) {
            const canRedo = historyIndex < history.length - 1;
            btnRedo.disabled = !canRedo;
            btnRedo.classList.toggle('opacity-30', !canRedo);
            btnRedo.classList.toggle('opacity-100', canRedo);
        }
    }

    // Save initial state into history
    if (editorArea) {
        saveHistoryState(true);
    }

    // Bind Undo / Redo Buttons
    btnUndo?.addEventListener('click', (e) => {
        e.preventDefault();
        undo();
    });

    btnRedo?.addEventListener('click', (e) => {
        e.preventDefault();
        redo();
    });

    // --- RENDER PREVIEW ---
    async function updatePreview() {
        if (!editorArea || !previewArea) return;

        const raw = editorArea.value || '';
        let mermaidIndex = 0;
        const diagrams = [];

        const renderer = new marked.Renderer();
        const origCode = renderer.code.bind(renderer);

        renderer.code = function ({ text, lang }) {
            if (lang === 'mermaid') {
                const id = `preview-mermaid-${mermaidIndex++}`;
                diagrams.push({ id, code: text });
                return `<div class="mermaid-container"><div id="${id}">Rendering diagram...</div></div>`;
            }
            return origCode({ text, lang });
        };

        marked.setOptions({ renderer, gfm: true, breaks: true });

        const html = DOMPurify.sanitize(marked.parse(raw), {
            ADD_TAGS: ['div', 'svg', 'path', 'g', 'rect', 'circle', 'line', 'polygon', 'text'],
            ADD_ATTR: ['id', 'class', 'style', 'viewBox', 'd', 'fill', 'stroke', 'stroke-width', 'transform']
        });

        previewArea.innerHTML = html;

        // Highlight code & attach Copy buttons
        previewArea.querySelectorAll('pre code').forEach((b) => hljs.highlightElement(b));
        setupCodeBlockCopyButtons(previewArea);

        // Render Mermaid with current theme
        initMermaidTheme();
        for (const d of diagrams) {
            const container = document.getElementById(d.id);
            if (container) {
                try {
                    const { svg } = await mermaid.render(`prev-${d.id}-${Date.now()}`, d.code);
                    container.innerHTML = svg;
                } catch (e) {
                    container.innerHTML = `<div class="mermaid-error text-xs">Mermaid Error: ${e.message}</div>`;
                }
            }
        }
    }

    // Re-render live preview diagrams on theme switch
    window.addEventListener('devdocs:theme-change', () => {
        updatePreview();
    });

    // --- CODE BLOCK COPY BUTTON ENGINE ---
    function setupCodeBlockCopyButtons(container) {
        if (!container) return;

        container.querySelectorAll('pre').forEach((pre) => {
            const code = pre.querySelector('code');
            if (!code) return;

            // Ensure relative wrapper for clean positioning during horizontal scroll
            let wrapper = pre.parentElement;
            if (!wrapper || !wrapper.classList.contains('code-block-wrapper')) {
                wrapper = document.createElement('div');
                wrapper.className = 'code-block-wrapper relative group my-4';
                pre.parentNode.insertBefore(wrapper, pre);
                wrapper.appendChild(pre);
            }

            // Prevent duplicate buttons
            if (wrapper.querySelector('.code-copy-btn')) return;

            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'code-copy-btn absolute top-2.5 right-2.5 px-2.5 py-1 text-xs font-sans font-medium text-slate-300 hover:text-white bg-slate-800/90 hover:bg-slate-700 rounded-lg border border-slate-700/80 shadow-xs transition-all duration-150 flex items-center gap-1.5 cursor-pointer z-10 select-none backdrop-blur-xs';
            copyBtn.title = 'Salin kode ke papan klip';
            copyBtn.setAttribute('aria-label', 'Salin kode ke papan klip');

            const defaultContent = `
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <span>Salin</span>
            `;

            const copiedContent = `
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-emerald-400 font-semibold">Tersalin!</span>
            `;

            copyBtn.innerHTML = defaultContent;

            copyBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                const textToCopy = code.innerText || code.textContent || '';
                let copied = false;

                if (navigator.clipboard && window.isSecureContext) {
                    try {
                        await navigator.clipboard.writeText(textToCopy);
                        copied = true;
                    } catch (_) {}
                }

                if (!copied) {
                    try {
                        const textarea = document.createElement('textarea');
                        textarea.value = textToCopy;
                        textarea.style.position = 'fixed';
                        textarea.style.left = '-9999px';
                        textarea.style.top = '-9999px';
                        document.body.appendChild(textarea);
                        textarea.focus();
                        textarea.select();
                        copied = document.execCommand('copy');
                        document.body.removeChild(textarea);
                    } catch (err) {
                        console.error('Fallback copy failed', err);
                    }
                }

                if (copied) {
                    copyBtn.innerHTML = copiedContent;
                    copyBtn.classList.add('border-emerald-500/60', 'bg-emerald-950/70', 'text-emerald-300');

                    setTimeout(() => {
                        copyBtn.innerHTML = defaultContent;
                        copyBtn.classList.remove('border-emerald-500/60', 'bg-emerald-950/70', 'text-emerald-300');
                    }, 2000);
                }
            });

            wrapper.appendChild(copyBtn);
        });
    }

    // Trigger initial preview
    updatePreview();

    // --- AUTOSAVE ENGINE ---
    function triggerAutosave() {
        if (!autosaveUrl) return;
        setAutosaveBadge('saving', 'Ada perubahan...');
        clearTimeout(autosaveTimeout);
        autosaveTimeout = setTimeout(performAutosave, 2500);
    }

    async function performAutosave() {
        if (!autosaveUrl || !editorArea || !titleInput || !versionInput || isSaving) return;

        isSaving = true;
        setAutosaveBadge('saving', 'Menyimpan draft...');

        try {
            const response = await fetch(autosaveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    title: titleInput.value,
                    content_markdown: editorArea.value,
                    version: parseInt(versionInput.value, 10) || 1
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                versionInput.value = data.version;
                setAutosaveBadge('saved', `Tersimpan (${new Date().toLocaleTimeString()})`);
            } else if (response.status === 409) {
                setAutosaveBadge('conflict', 'Konflik: Dokumen diperbarui di sesi lain!');
                alert('PERINGATAN: Dokumen ini memiliki versi baru di server. Jangan tutup halaman ini, salin perubahan Anda secara manual.');
            } else {
                setAutosaveBadge('error', 'Gagal menyimpan otomatis');
            }
        } catch (err) {
            console.error('Autosave error:', err);
            setAutosaveBadge('error', 'Gagal tersambung');
        } finally {
            isSaving = false;
        }
    }

    function setAutosaveBadge(status, text) {
        if (!autosaveStatusEl) return;
        autosaveStatusEl.innerHTML = '';

        if (status === 'saving') {
            autosaveStatusEl.className = 'text-xs flex items-center gap-1.5 text-amber-600 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/15 px-2.5 py-1 rounded-full border border-amber-200 dark:border-amber-500/30 font-medium';
            autosaveStatusEl.innerHTML = `<svg class="animate-spin w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> ${text}`;
        } else if (status === 'saved') {
            autosaveStatusEl.className = 'text-xs flex items-center gap-1.5 text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-500/30 font-medium';
            autosaveStatusEl.innerHTML = `<svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ${text}`;
        } else if (status === 'conflict') {
            autosaveStatusEl.className = 'text-xs flex items-center gap-1.5 text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-500/20 px-2.5 py-1 rounded-full border border-red-300 dark:border-red-500/40 font-semibold';
            autosaveStatusEl.innerHTML = `⚠️ ${text}`;
        } else {
            autosaveStatusEl.className = 'text-xs flex items-center gap-1.5 text-red-600 dark:text-red-300 bg-red-50 dark:bg-red-500/15 px-2.5 py-1 rounded-full border border-red-200 dark:border-red-500/30 font-medium';
            autosaveStatusEl.innerHTML = `❌ ${text}`;
        }
    }

    // --- INPUT & KEYBOARD LISTENERS ---
    if (editorArea) {
        // Track typing & push to history
        editorArea.addEventListener('input', (e) => {
            clearTimeout(renderTimeout);
            renderTimeout = setTimeout(updatePreview, 300);

            // Debounced history save on typing
            clearTimeout(historyTimeout);
            // If user typed space, enter, or punctuation, save immediately
            if (e.data === ' ' || e.data === '\n' || e.inputType === 'insertLineBreak') {
                saveHistoryState();
            } else {
                historyTimeout = setTimeout(() => saveHistoryState(), 500);
            }

            triggerAutosave();
        });

        // Keydown handling for Ctrl+Z, Ctrl+Y, Tab
        editorArea.addEventListener('keydown', (e) => {
            // Undo: Ctrl + Z (or Cmd + Z)
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
                e.preventDefault();
                if (e.shiftKey) {
                    redo();
                } else {
                    undo();
                }
                return;
            }

            // Redo: Ctrl + Y (or Cmd + Y)
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
                e.preventDefault();
                redo();
                return;
            }

            // Tab key support (insert 2 spaces)
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = editorArea.selectionStart;
                const end = editorArea.selectionEnd;

                // Insert 2 spaces
                editorArea.setRangeText('  ', start, end, 'end');
                saveHistoryState();
                updatePreview();
                triggerAutosave();
                return;
            }
        });
    }

    if (titleInput && autosaveUrl) {
        titleInput.addEventListener('input', () => {
            triggerAutosave();
        });
    }

    // Global shortcut: Ctrl + S to manually submit form
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            const form = document.getElementById('document-form');
            if (form) {
                form.submit();
            }
        }
    });

    // --- TOOLBAR SNIPPET INSERTION ---
    const insertSnippet = (prefix, suffix = '', defaultText = '') => {
        if (!editorArea) return;
        const start = editorArea.selectionStart;
        const end = editorArea.selectionEnd;
        const selected = editorArea.value.substring(start, end) || defaultText;
        const replacement = prefix + selected + suffix;

        editorArea.setRangeText(replacement, start, end, 'select');
        editorArea.focus();

        // Push state into undo history immediately
        saveHistoryState();
        updatePreview();
        triggerAutosave();
    };

    document.getElementById('btn-bold')?.addEventListener('click', () => insertSnippet('**', '**', 'teks tebal'));
    document.getElementById('btn-italic')?.addEventListener('click', () => insertSnippet('*', '*', 'teks miring'));
    document.getElementById('btn-h2')?.addEventListener('click', () => insertSnippet('\n## ', '\n', 'Judul Bagian'));
    document.getElementById('btn-h3')?.addEventListener('click', () => insertSnippet('\n### ', '\n', 'Sub-Judul'));
    document.getElementById('btn-quote')?.addEventListener('click', () => insertSnippet('\n> ', '\n', 'Catatan penting'));
    document.getElementById('btn-code')?.addEventListener('click', () => insertSnippet('`', '`', 'kode_inline'));
    document.getElementById('btn-codeblock')?.addEventListener('click', () => insertSnippet('\n```json\n', '\n```\n', '{\n  "status": "success"\n}'));
    document.getElementById('btn-table')?.addEventListener('click', () => {
        const tableSnippet = "\n| Header 1 | Header 2 | Keterangan |\n| :--- | :--- | :--- |\n| Data 1 | Data 2 | Deskripsi |\n";
        insertSnippet(tableSnippet);
    });

    // Mermaid Templates
    document.getElementById('btn-mermaid-seq')?.addEventListener('click', () => {
        const seqSnippet = "\n```mermaid\nsequenceDiagram\n    autonumber\n    actor Client\n    participant API\n    participant DB\n    Client->>API: POST /request\n    API->>DB: Query data\n    DB-->>API: Result\n    API-->>Client: HTTP 200 OK\n```\n";
        insertSnippet(seqSnippet);
    });

    document.getElementById('btn-mermaid-flow')?.addEventListener('click', () => {
        const flowSnippet = "\n```mermaid\ngraph TD\n    A[Mulai] --> B{Validasi?}\n    B -- Ya --> C[Proses Transaksi]\n    B -- Tidak --> D[Tolak Request]\n    C --> E[Selesai]\n```\n";
        insertSnippet(flowSnippet);
    });

    // --- GEMINI AI ASSISTANT MODAL & API LOGIC ---
    const btnAiModal = document.getElementById('btn-ai-modal');
    const modalAi = document.getElementById('modal-ai-assistant');
    const btnCloseAi = document.getElementById('btn-close-ai-modal');
    const btnCancelAi = document.getElementById('btn-cancel-ai');
    const tabRefine = document.getElementById('tab-ai-refine');
    const tabGenerate = document.getElementById('tab-ai-generate');
    const refinePanel = document.getElementById('ai-refine-panel');
    const generatePanel = document.getElementById('ai-generate-panel');
    const btnTriggerAi = document.getElementById('btn-trigger-ai');
    const loadingIndicator = document.getElementById('ai-loading-indicator');
    const outputContainer = document.getElementById('ai-output-container');
    const resultText = document.getElementById('ai-result-text');
    const btnApplyAi = document.getElementById('btn-apply-ai');
    const apiKeyWarning = document.getElementById('ai-apikey-warning');
    const modelSelect = document.getElementById('ai-model-select');

    let currentAiMode = 'refine'; // 'refine' or 'generate'

    // Load saved model preference
    const savedModel = localStorage.getItem('devdocs_gemini_model');
    if (savedModel && modelSelect) {
        modelSelect.value = savedModel;
    }

    modelSelect?.addEventListener('change', (e) => {
        localStorage.setItem('devdocs_gemini_model', e.target.value);
    });

    async function checkAiStatus() {
        try {
            const res = await fetch('/ai/status');
            const data = await res.json();
            if (apiKeyWarning) {
                if (!data.configured) {
                    apiKeyWarning.classList.remove('hidden');
                } else {
                    apiKeyWarning.classList.add('hidden');
                }
            }

            // Populate models if returned
            if (modelSelect && data.models && data.models.length > 0) {
                const currentVal = localStorage.getItem('devdocs_gemini_model') || data.default_model || modelSelect.value;
                modelSelect.innerHTML = '';
                data.models.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.name;
                    if (m.id === currentVal) {
                        opt.selected = true;
                    }
                    modelSelect.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Failed to check AI status', e);
        }
    }

    btnAiModal?.addEventListener('click', () => {
        modalAi?.classList.remove('hidden');
        checkAiStatus();
    });

    const closeAiModal = () => {
        modalAi?.classList.add('hidden');
        if (loadingIndicator) loadingIndicator.classList.add('hidden');
    };

    btnCloseAi?.addEventListener('click', closeAiModal);
    btnCancelAi?.addEventListener('click', closeAiModal);

    // Switch Tabs
    tabRefine?.addEventListener('click', () => {
        currentAiMode = 'refine';
        tabRefine.className = 'px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 transition font-semibold';
        tabGenerate.className = 'px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition font-semibold';
        refinePanel?.classList.remove('hidden');
        generatePanel?.classList.add('hidden');
        btnTriggerAi.querySelector('span').textContent = 'Mulai Proses dengan Gemini';
    });

    tabGenerate?.addEventListener('click', () => {
        currentAiMode = 'generate';
        tabGenerate.className = 'px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 transition font-semibold';
        tabRefine.className = 'px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition font-semibold';
        generatePanel?.classList.remove('hidden');
        refinePanel?.classList.add('hidden');
        btnTriggerAi.querySelector('span').textContent = 'Generate TSD dari Catatan';
    });

    // Trigger AI Request
    btnTriggerAi?.addEventListener('click', async () => {
        if (!editorArea) return;

        const selectedModel = modelSelect ? modelSelect.value : null;
        let endpoint = '/ai/refine';
        let payload = {};

        if (currentAiMode === 'refine') {
            const content = editorArea.value.trim();
            if (!content) {
                alert('Editor masih kosong! Ketikkan catatan atau teks terlebih dahulu agar bisa dirapikan.');
                return;
            }
            endpoint = '/ai/refine';
            payload = {
                content: content,
                instructions: document.getElementById('ai-refine-instructions')?.value || '',
                model: selectedModel
            };
        } else {
            const prompt = document.getElementById('ai-generate-prompt')?.value.trim();
            if (!prompt) {
                alert('Tuliskan catatan atau ide fitur yang ingin di-generate menjadi TSD.');
                return;
            }
            endpoint = '/ai/generate';
            payload = {
                prompt: prompt,
                model: selectedModel
            };
        }

        btnTriggerAi.disabled = true;
        btnTriggerAi.classList.add('opacity-50');
        loadingIndicator?.classList.remove('hidden');


        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.success) {
                resultText.value = data.result;
                outputContainer?.classList.remove('hidden');
                btnApplyAi.disabled = false;
            } else {
                alert(data.message || 'Gagal memproses dengan Gemini AI.');
            }
        } catch (err) {
            console.error('AI Error:', err);
            alert('Terjadi kesalahan jaringan saat memanggil Gemini API: ' + err.message);
        } finally {
            btnTriggerAi.disabled = false;
            btnTriggerAi.classList.remove('opacity-50');
            loadingIndicator?.classList.add('hidden');
        }
    });

    // Apply to Editor
    btnApplyAi?.addEventListener('click', () => {
        if (!resultText || !editorArea) return;

        editorArea.value = resultText.value;
        saveHistoryState(true); // Push to undo stack
        updatePreview();
        triggerAutosave();

        closeAiModal();
        alert('✨ Sukses! Dokumentasi hasil polesan Gemini telah diterapkan ke editor. Anda bisa menekan Ctrl + Z jika ingin membatalkannya.');
    });

    // --- DRAGGABLE SPLIT PANE RESIZER ---
    const splitContainer = document.getElementById('split-container');
    const editorPane = document.getElementById('editor-pane');
    const previewPane = document.getElementById('preview-pane');
    const splitResizer = document.getElementById('split-resizer');
    const btnResetSplit = document.getElementById('btn-reset-split');

    if (splitContainer && editorPane && splitResizer) {
        const STORAGE_KEY = 'devdocs_editor_split_pct';
        const MIN_PX = 260; // Minimum width in pixels for each pane
        let isDragging = false;
        let currentPct = 50;

        const updateResetButtonVisibility = (pct) => {
            if (btnResetSplit) {
                // Show reset button if split deviates from 50% by more than 1.5%
                if (Math.abs(pct - 50) > 1.5) {
                    btnResetSplit.classList.remove('hidden');
                } else {
                    btnResetSplit.classList.add('hidden');
                }
            }
        };

        const setSplitPercentage = (pct, save = true) => {
            if (!splitContainer || !editorPane) return;

            // In mobile screen (<768px), maintain full width
            if (window.innerWidth < 768) {
                editorPane.style.width = '100%';
                updateResetButtonVisibility(50);
                return;
            }

            const containerWidth = splitContainer.getBoundingClientRect().width;
            if (containerWidth <= 0) return;

            const minPct = (MIN_PX / containerWidth) * 100;
            const maxPct = 100 - minPct;

            // Clamp between min and max bounds
            const clamped = Math.max(minPct, Math.min(maxPct, pct));
            currentPct = clamped;

            editorPane.style.width = `${clamped.toFixed(2)}%`;
            splitResizer.setAttribute('aria-valuenow', Math.round(clamped).toString());

            updateResetButtonVisibility(clamped);

            if (save) {
                try {
                    localStorage.setItem(STORAGE_KEY, clamped.toFixed(2));
                } catch (e) {
                    // Ignore localStorage error
                }
            }
        };

        // Restore split preference from localStorage if available
        try {
            const savedPct = localStorage.getItem(STORAGE_KEY);
            if (savedPct) {
                const parsed = parseFloat(savedPct);
                if (!isNaN(parsed) && parsed >= 10 && parsed <= 90) {
                    setSplitPercentage(parsed, false);
                }
            }
        } catch (e) {
            console.warn('Could not read split preference from localStorage', e);
        }

        // Pointer Events (Mouse, Touch, Stylus) with pointer capture
        splitResizer.addEventListener('pointerdown', (e) => {
            if (e.button !== 0) return; // Primary button only
            e.preventDefault();

            isDragging = true;
            try {
                splitResizer.setPointerCapture(e.pointerId);
            } catch (_) {}

            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            splitContainer.classList.add('select-none');
            splitResizer.classList.add('bg-indigo-200', 'dark:bg-indigo-500/30', 'border-indigo-400', 'dark:border-indigo-500');
        });

        splitResizer.addEventListener('pointermove', (e) => {
            if (!isDragging) return;

            const rect = splitContainer.getBoundingClientRect();
            if (rect.width <= 0) return;

            const offset = e.clientX - rect.left;
            const pct = (offset / rect.width) * 100;
            setSplitPercentage(pct, false);
        });

        const stopDragging = (e) => {
            if (!isDragging) return;
            isDragging = false;

            try {
                splitResizer.releasePointerCapture(e.pointerId);
            } catch (_) {}

            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            splitContainer.classList.remove('select-none');
            splitResizer.classList.remove('bg-indigo-200', 'dark:bg-indigo-500/30', 'border-indigo-400', 'dark:border-indigo-500');

            // Persist final position
            setSplitPercentage(currentPct, true);
        };

        splitResizer.addEventListener('pointerup', stopDragging);
        splitResizer.addEventListener('pointercancel', stopDragging);

        // Double click to quickly reset 50:50
        splitResizer.addEventListener('dblclick', (e) => {
            e.preventDefault();
            setSplitPercentage(50, true);
        });

        // Click reset button
        btnResetSplit?.addEventListener('click', (e) => {
            e.preventDefault();
            setSplitPercentage(50, true);
        });

        // Keyboard accessibility (Arrow keys to adjust, Enter/Home to reset)
        splitResizer.addEventListener('keydown', (e) => {
            const step = e.shiftKey ? 5 : 2;
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                setSplitPercentage(currentPct - step, true);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                setSplitPercentage(currentPct + step, true);
            } else if (e.key === 'Enter' || e.key === ' ' || e.key === 'Home') {
                e.preventDefault();
                setSplitPercentage(50, true);
            }
        });

        // Responsive resize listener
        window.addEventListener('resize', () => {
            if (!isDragging) {
                setSplitPercentage(currentPct, false);
            }
        });
    }
});
