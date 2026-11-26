<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <script>
    try {
        const s = localStorage.getItem('color-scheme');
        if (s === 'light' || s === 'dark') {
            document.querySelector('meta[name="color-scheme"]').content = s;
        }
    } catch (e) {}
    </script>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $title ?? 'DevDocs — Public Documentation' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="h-full flex flex-col overflow-hidden font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 selection:bg-indigo-500 selection:text-white transition-colors duration-150">

    <!-- Public Read-Only Header -->
    <header class="z-30 h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shrink-0">
        <div class="max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('public.projects.show', ['token' => $project->share_token]) }}" class="flex items-center gap-2.5 group min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 shrink-0 group-hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-bold tracking-tight text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition truncate">{{ $project->name }}</span>
                        <span class="text-[10px] font-semibold tracking-wider uppercase text-slate-400 dark:text-slate-500 -mt-0.5">DevDocs</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Publik &bull; Hanya Lihat</span>
                </span>

                <button type="button" onclick="togglePublicTheme()" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer" title="Ganti Tema Terang / Gelap">
                    <span class="theme-icon-sun">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </span>
                    <span class="theme-icon-moon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    @php
        $isPublicDocumentView = request()->routeIs('public.projects.document');
    @endphp
    <div class="flex-1 flex flex-col min-h-0 bg-slate-50 dark:bg-slate-950 {{ $isPublicDocumentView ? 'overflow-hidden' : 'overflow-y-auto dd-scroller' }}">
        @yield('content')
    </div>

    <script>
        function togglePublicTheme() {
            const meta = document.querySelector('meta[name="color-scheme"]');
            const current = meta.content;
            const isDark = current === 'dark' || (current === 'light dark' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            const next = isDark ? 'light' : 'dark';
            meta.content = next;
            try {
                localStorage.setItem('color-scheme', next);
            } catch (e) {}
            window.dispatchEvent(new CustomEvent('devdocs:theme-change'));
        }
    </script>
</body>
</html>
