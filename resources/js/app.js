// DevDocs Global Application JS

document.addEventListener('DOMContentLoaded', () => {
    // 0. Smart Two-State Color Scheme Toggle (System + Literal Override)
    const colorSchemeMeta = document.querySelector('meta[name="color-scheme"]');
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    const darkMedia = window.matchMedia('(prefers-color-scheme: dark)');

    function getOsTheme() {
        return darkMedia.matches ? 'dark' : 'light';
    }

    function getResolvedTheme() {
        const content = colorSchemeMeta?.getAttribute('content');
        if (content === 'dark' || content === 'light') {
            return content;
        }
        return getOsTheme();
    }

    function applyThemeOverride(storedValue) {
        if (colorSchemeMeta) {
            colorSchemeMeta.setAttribute('content', storedValue || 'light dark');
        }
        window.dispatchEvent(new CustomEvent('devdocs:theme-change', {
            detail: { theme: getResolvedTheme() }
        }));
    }

    themeToggleBtn?.addEventListener('click', () => {
        const targetTheme = getResolvedTheme() === 'dark' ? 'light' : 'dark';
        const osTheme = getOsTheme();

        if (targetTheme === osTheme) {
            try {
                localStorage.removeItem('color-scheme');
            } catch (_) {}
            applyThemeOverride(null);
        } else {
            try {
                localStorage.setItem('color-scheme', targetTheme);
            } catch (_) {}
            applyThemeOverride(targetTheme);
        }
    });

    window.addEventListener('storage', (e) => {
        if (e.key === 'color-scheme') {
            const val = (e.newValue === 'light' || e.newValue === 'dark') ? e.newValue : null;
            applyThemeOverride(val);
        }
    });

    darkMedia.addEventListener('change', () => {
        const content = colorSchemeMeta?.getAttribute('content');
        if (content !== 'light' && content !== 'dark') {
            window.dispatchEvent(new CustomEvent('devdocs:theme-change', {
                detail: { theme: getResolvedTheme() }
            }));
        }
    });

    // 1. Keyboard Shortcut: Ctrl + K / Cmd + K to focus global search
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            const searchInput = document.getElementById('global-search-input');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        }
    });

    // 2. Auto-dismiss flash alerts after 5s
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach((el) => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 400);
        }, 5000);
    });

    // 3. Copy Code Buttons
    document.querySelectorAll('.copy-code-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const code = btn.getAttribute('data-code');
            if (code) {
                navigator.clipboard.writeText(code).then(() => {
                    const originalText = btn.innerHTML;
                    btn.innerHTML = `<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersalin!`;
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                    }, 2000);
                });
            }
        });
    });
});
