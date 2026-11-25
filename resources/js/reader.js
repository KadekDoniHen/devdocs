import { marked } from 'marked';
import DOMPurify from 'dompurify';
import hljs from 'highlight.js';
import mermaid from 'mermaid';

document.addEventListener('DOMContentLoaded', async () => {
    const rawMarkdownEl = document.getElementById('raw-markdown');
    const targetEl = document.getElementById('doc-render-target');
    const tocContainer = document.getElementById('toc-list');

    if (!rawMarkdownEl || !targetEl) return;

    const rawMarkdown = rawMarkdownEl.value || rawMarkdownEl.textContent || '';

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

    // Custom marked renderer to intercept mermaid code fences
    const renderer = new marked.Renderer();
    const originalCodeRenderer = renderer.code.bind(renderer);

    let mermaidIndex = 0;
    const mermaidDiagrams = [];

    renderer.code = function ({ text, lang }) {
        if (lang === 'mermaid') {
            const id = `mermaid-svg-${mermaidIndex++}`;
            mermaidDiagrams.push({ id, code: text });
            return `<div class="mermaid-container"><div id="${id}" class="mermaid-target">Memuat diagram...</div></div>`;
        }
        return originalCodeRenderer({ text, lang });
    };

    marked.setOptions({
        renderer: renderer,
        gfm: true,
        breaks: true
    });

    // Parse Markdown
    const rawHtml = marked.parse(rawMarkdown);
    // Sanitize HTML
    const cleanHtml = DOMPurify.sanitize(rawHtml, {
        ADD_TAGS: ['div', 'svg', 'path', 'g', 'rect', 'circle', 'line', 'polygon', 'text'],
        ADD_ATTR: ['id', 'class', 'style', 'viewBox', 'd', 'fill', 'stroke', 'stroke-width', 'transform']
    });

    targetEl.innerHTML = cleanHtml;

    // Syntax Highlight & attach Copy buttons for code blocks
    targetEl.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightElement(block);

        const pre = block.parentElement;
        if (!pre) return;

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
            <svg class="w-3.5 h-3.5 text-slate-400 group-hover/btn:text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            const textToCopy = block.innerText || block.textContent || '';
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

    // Render Mermaid diagrams safely (supports theme switching)
    let renderCycle = 0;
    async function renderMermaidDiagrams() {
        initMermaidTheme();
        renderCycle++;
        for (const item of mermaidDiagrams) {
            const container = document.getElementById(item.id);
            if (container) {
                try {
                    const { svg } = await mermaid.render(`render-${item.id}-${renderCycle}`, item.code);
                    container.innerHTML = svg;
                } catch (err) {
                    console.error('Mermaid render error:', err);
                    container.innerHTML = `<div class="mermaid-error"><strong>Gagal merender Mermaid diagram:</strong> ${err.message || 'Syntax diagram tidak valid.'}<pre class="text-xs mt-2 bg-red-100 p-2 rounded text-red-900">${item.code}</pre></div>`;
                }
            }
        }
    }

    await renderMermaidDiagrams();

    // Re-render Mermaid diagrams whenever the color scheme changes
    window.addEventListener('devdocs:theme-change', () => {
        if (mermaidDiagrams.length > 0) {
            renderMermaidDiagrams();
        }
    });

    // Build Table of Contents dynamically
    if (tocContainer) {
        const headings = targetEl.querySelectorAll('h2, h3');
        if (headings.length === 0) {
            tocContainer.innerHTML = '<p class="text-xs text-slate-400 dark:text-slate-500 italic">Tidak ada sub-heading.</p>';
        } else {
            tocContainer.innerHTML = '';
            headings.forEach((heading, idx) => {
                const headingId = `section-${idx}`;
                heading.id = headingId;

                const link = document.createElement('a');
                link.href = `#${headingId}`;
                link.textContent = heading.textContent;
                link.className = `block text-xs py-1 transition hover:text-indigo-600 dark:hover:text-indigo-400 truncate ${
                    heading.tagName === 'H2'
                        ? 'font-medium text-slate-700 dark:text-slate-300'
                        : 'pl-3 text-slate-500 dark:text-slate-400'
                }`;
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
                tocContainer.appendChild(link);
            });
        }
    }

    // Signal that document rendering (Markdown, syntax highlighting, and all Mermaid diagrams) is fully finished
    window.__devdocs_rendered = true;
    window.dispatchEvent(new CustomEvent('devdocs:ready', {
        detail: {
            diagramCount: mermaidDiagrams.length
        }
    }));
});
