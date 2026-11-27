<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 dark:bg-slate-950">
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $title ?? 'DevDocs — Personal Technical Documentation' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="h-full flex overflow-hidden font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 selection:bg-indigo-500 selection:text-white transition-colors duration-150">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 bg-slate-900/50 dark:bg-black/60 backdrop-blur-xs md:hidden hidden transition-opacity"></div>

    <!-- Left Sidebar Navigation -->
    @php
        $isDocumentView = request()->routeIs('documents.show', 'documents.edit');
    @endphp
    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between transition-all duration-300 transform -translate-x-full md:relative md:translate-x-0 md:flex shrink-0 shadow-sm md:shadow-none {{ $isDocumentView ? 'md:-ml-64' : '' }}">
        
        <!-- Sidebar Top: Brand & Quick Action -->
        <div class="flex flex-col shrink-0">
            <!-- Brand Header -->
            <div class="h-16 px-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base font-bold tracking-tight text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">DevDocs</span>
                        <span class="text-[10px] font-semibold tracking-wider uppercase text-indigo-600 dark:text-indigo-400 -mt-1">Technical Hub</span>
                    </div>
                </a>
                <button type="button" onclick="toggleSidebar()" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition cursor-pointer" title="Sembunyikan Sidebar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>

            <!-- Primary Action Button -->
            <div class="p-3">
                <a href="{{ route('documents.create') }}" class="flex items-center justify-center gap-2 w-full py-2.5 px-3 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm shadow-indigo-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Tulis Dokumen Baru</span>
                </a>
            </div>

            <!-- Main Navigation Links -->
            <div class="px-3 py-1 space-y-1">
                <div class="px-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Navigasi Utama</div>

                <!-- Dashboard -->
                @php $isDashboard = request()->routeIs('dashboard'); @endphp
                <a href="{{ route('dashboard') }}" class="relative flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition {{ $isDashboard ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-slate-100' }}">
                    @if($isDashboard)
                        <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r-full bg-indigo-600 dark:bg-indigo-400"></span>
                    @endif
                    <svg class="w-4 h-4 shrink-0 {{ $isDashboard ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Daftar Proyek -->
                @php $isProjectsIndex = request()->routeIs('projects.index') || request()->routeIs('projects.create'); @endphp
                <a href="{{ route('projects.index') }}" class="relative flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ $isProjectsIndex ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-slate-100' }}">
                    @if($isProjectsIndex)
                        <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r-full bg-indigo-600 dark:bg-indigo-400"></span>
                    @endif
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0 {{ $isProjectsIndex ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <span>Daftar Proyek</span>
                    </div>
                    @if(isset($sidebarProjects))
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full {{ request()->routeIs('projects.index') ? 'bg-indigo-100 dark:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                            {{ $sidebarProjects->count() }}
                        </span>
                    @endif
                </a>

                <!-- Pencarian Global -->
                @php $isSearch = request()->routeIs('search'); @endphp
                <a href="{{ route('search') }}" class="relative flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition {{ $isSearch ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-slate-100' }}">
                    @if($isSearch)
                        <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r-full bg-indigo-600 dark:bg-indigo-400"></span>
                    @endif
                    <svg class="w-4 h-4 shrink-0 {{ $isSearch ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Pencarian Global</span>
                </a>

                <!-- Manajemen Tim (Khusus Admin) -->
                @if (Auth::check() && Auth::user()->isAdmin())
                    @php $isUsers = request()->routeIs('users.*'); @endphp
                    <a href="{{ route('users.index') }}" class="relative flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition {{ $isUsers ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-slate-100' }}">
                        @if($isUsers)
                            <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r-full bg-indigo-600 dark:bg-indigo-400"></span>
                        @endif
                        <svg class="w-4 h-4 shrink-0 {{ $isUsers ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Manajemen Tim</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Middle: Proyek List (Quick Navigation) -->
        <div class="px-3 py-2 flex-1 overflow-y-auto dd-scroller">
            <div class="flex items-center justify-between px-2 pb-1.5 pt-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Proyek Saya</span>
                <a href="{{ route('projects.create') }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-semibold flex items-center gap-0.5" title="Tambah Proyek Baru">
                    + Baru
                </a>
            </div>

            @if(isset($sidebarProjects) && $sidebarProjects->isNotEmpty())
                <div class="space-y-0.5">
                    @foreach($sidebarProjects as $sp)
                        @php
                            $isCurrentProject = (isset($project) && $project->slug === $sp->slug);
                        @endphp
                        <a href="{{ route('projects.show', $sp->slug) }}"
                           class="relative group flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition {{ $isCurrentProject ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-slate-100' }}"
                           title="{{ $sp->name }}">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $sp->status === 'active' ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                <span class="truncate">{{ $sp->name }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300 shrink-0 ml-1">
                                {{ $sp->documents_count ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="px-2 py-3 text-center bg-slate-50 dark:bg-slate-800/50 border border-dashed border-slate-200 dark:border-slate-700 rounded-xl my-1">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-1">Belum ada proyek</p>
                    <a href="{{ route('projects.create') }}" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        + Buat Proyek
                    </a>
                </div>
            @endif
        </div>

        <!-- Sidebar Bottom: User Profile & Logout -->
        @auth
        @php $isAccount = request()->routeIs('account.*'); @endphp
        <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/90 shrink-0">
            <div class="flex items-center justify-between gap-1.5 p-2 rounded-xl bg-white dark:bg-slate-800/80 border {{ $isAccount ? 'border-indigo-300 dark:border-indigo-500/50 ring-2 ring-indigo-100 dark:ring-indigo-500/15' : 'border-slate-200 dark:border-slate-700/80' }} shadow-2xs">
                <a href="{{ route('account.edit') }}" class="flex items-center gap-2.5 min-w-0 flex-1 group rounded-lg p-0.5 hover:bg-slate-50 dark:hover:bg-slate-800 transition" title="Pengaturan Akun">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs font-semibold text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate leading-tight transition">{{ Auth::user()->name }}</span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-400 capitalize truncate">{{ Auth::user()->role }} &bull; Akun</span>
                    </div>
                </a>
                <div class="flex items-center gap-0.5 shrink-0">
                    <a href="{{ route('account.edit') }}" title="Pengaturan Akun" class="p-1.5 {{ $isAccount ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/15' : 'text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700' }} rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar / Logout" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 rounded-lg transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <div class="mt-2 text-center text-[10px] text-slate-400 dark:text-slate-500">
                DevDocs v2.1 &bull; Local Mode
            </div>
        </div>
        @endauth

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        
        <!-- Top Application Header Bar -->
        <header class="sticky top-0 z-30 bg-white/85 dark:bg-slate-900/85 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6 h-16 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Sidebar Toggle Button (Mobile & Desktop) -->
                <button type="button" onclick="toggleSidebar()" class="p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer" title="Buka/Tutup Sidebar Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Breadcrumbs / Active Context -->
                <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 truncate">
                    <span class="font-semibold text-slate-800 dark:text-slate-100 truncate">
                        @if(request()->routeIs('dashboard'))
                            Dashboard
                        @elseif(request()->routeIs('projects.index'))
                            Daftar Proyek
                        @elseif(request()->routeIs('projects.create'))
                            Buat Proyek Baru
                        @elseif(request()->routeIs('projects.edit'))
                            Pengaturan Proyek
                        @elseif(request()->routeIs('projects.show'))
                            {{ $project->name ?? 'Detail Proyek' }}
                        @elseif(request()->routeIs('documents.create'))
                            Tulis Dokumen
                        @elseif(request()->routeIs('search'))
                            Pencarian Dokumen
                        @elseif(request()->routeIs('account.*'))
                            Pengaturan Akun
                        @elseif(request()->routeIs('users.*'))
                            Manajemen Tim
                        @else
                            DevDocs
                        @endif
                    </span>
                </div>
            </div>

            <!-- Global Search, Theme Toggle & Quick Actions -->
            <div class="flex items-center gap-2.5">
                <form action="{{ route('search') }}" method="GET" class="relative">
                    <input type="text" name="q" id="global-search-input" value="{{ request('q') }}" placeholder="Cari dokumentasi atau API... (Ctrl+K)"
                        class="{{ request()->routeIs('projects.show', 'projects.edit', 'documents.show', 'documents.edit') ? 'w-48 sm:w-64 md:w-72 lg:w-88' : 'w-40 sm:w-60 lg:w-72' }} pl-8 pr-12 py-1.5 bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800 focus:bg-white dark:focus:bg-slate-900 text-xs text-slate-800 dark:text-slate-100 rounded-xl border border-slate-200 dark:border-slate-700 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span class="hidden sm:inline absolute right-2 top-1.5 text-[9px] font-mono text-slate-400 dark:text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-1 py-0.5 rounded shadow-2xs">Ctrl K</span>
                </form>

                <!-- Theme Toggle Button (Light / Dark) -->
                <button type="button" id="theme-toggle-btn"
                    class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-amber-300 hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-95 transition-all shadow-2xs cursor-pointer"
                    title="Ganti Tema (Light / Dark)">
                    <span class="theme-icon-sun items-center justify-center">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="sr-only">Beralih ke tema terang</span>
                    </span>
                    <span class="theme-icon-moon items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <span class="sr-only">Beralih ke tema gelap</span>
                    </span>
                </button>

                @if (!request()->routeIs('projects.show', 'projects.edit', 'documents.show', 'documents.edit'))
                <a href="{{ route('projects.create') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-95 transition-all shadow-2xs cursor-pointer" title="Tambah Proyek Baru">
                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Proyek</span>
                </a>

                <a href="{{ route('documents.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 active:scale-95 rounded-xl shadow-sm shadow-indigo-500/20 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span class="hidden sm:inline">+ Tulis Dokumen</span>
                </a>
                @endif
            </div>
        </header>

        <!-- Flash Alerts -->
        @if (session('success') || $errors->any())
        <div class="px-4 sm:px-6 lg:px-8 w-full mt-3 shrink-0">
            @if (session('success'))
                <div class="flash-message flex items-center justify-between p-3.5 mb-3 text-xs text-emerald-800 dark:text-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 rounded-xl border border-emerald-200 dark:border-emerald-500/30 shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 dark:text-emerald-300 hover:text-emerald-700 dark:hover:text-white cursor-pointer">✕</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="flash-message p-3.5 mb-3 text-xs text-red-800 dark:text-red-200 bg-red-50 dark:bg-red-500/15 rounded-xl border border-red-200 dark:border-red-500/30 shadow-sm">
                    <div class="font-semibold mb-1">Terdapat kesalahan:</div>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        @endif

        <!-- Content Slot -->
        @if (request()->routeIs('documents.edit', 'documents.show'))
            <main class="flex-1 flex flex-col min-h-0 h-full overflow-hidden">
                @yield('content')
            </main>
        @else
            <main class="flex-1 flex flex-col min-h-0 overflow-y-auto dd-scroller">
                <div class="grow shrink-0 flex flex-col">
                    @yield('content')
                </div>

                <!-- Footer -->
                <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-4 text-center text-xs text-slate-400 dark:text-slate-500 mt-auto shrink-0">
                    <p>DevDocs v2.1 — Personal Technical Documentation Hub &bull; Local Network Mode</p>
                </footer>
            </main>
        @endif

    </div>

    <!-- Modal Konfirmasi Hapus Proyek (Global) -->
    <div id="delete-project-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/50 dark:bg-black/70 backdrop-blur-xs transition-opacity duration-200">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl max-w-md w-full p-6 transform transition-all">
            <div class="flex items-center gap-3.5 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/15 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Hapus Proyek</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Konfirmasi penghapusan permanen</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                Apakah Anda yakin ingin menghapus proyek <span id="delete-project-name" class="font-bold text-slate-900 dark:text-white"></span>?
            </p>

            <div class="bg-red-50/70 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl p-3 text-xs text-red-800 dark:text-red-300 mb-5 flex items-start gap-2">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span><strong>Peringatan:</strong> Seluruh folder, dokumen, riwayat revisi, dan endpoint API di dalam proyek ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</span>
            </div>

            <form id="delete-project-form" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeDeleteProjectModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm shadow-red-500/20 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Ya, Hapus Proyek
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts: Sidebar Toggle & Global Delete Modal -->
    <script>
        const IS_DOCUMENT_VIEW = @json($isDocumentView ?? false);

        function toggleSidebar() {
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const isMobile = window.innerWidth < 768;

            if (isMobile) {
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                }
            } else {
                sidebar.classList.toggle('md:-ml-64');
                if (!IS_DOCUMENT_VIEW) {
                    const isCollapsed = sidebar.classList.contains('md:-ml-64');
                    localStorage.setItem('devdocs_sidebar_collapsed', isCollapsed ? 'true' : 'false');
                }
            }
        }

        // Restore desktop sidebar state on load (auto-collapsed on document views)
        document.addEventListener('DOMContentLoaded', () => {
            if (window.innerWidth >= 768 && !IS_DOCUMENT_VIEW) {
                const isCollapsed = localStorage.getItem('devdocs_sidebar_collapsed') === 'true';
                const sidebar = document.getElementById('app-sidebar');
                if (sidebar && isCollapsed) {
                    sidebar.classList.add('md:-ml-64');
                }
            }
        });

        // Global Delete Modal helpers
        function openDeleteProjectModal(slug, name) {
            const modal = document.getElementById('delete-project-modal');
            const form = document.getElementById('delete-project-form');
            const nameSpan = document.getElementById('delete-project-name');
            if (modal && form && nameSpan) {
                form.action = '/projects/' + encodeURIComponent(slug);
                nameSpan.textContent = '"' + name + '"';
                modal.classList.remove('hidden');
            }
        }

        function closeDeleteProjectModal() {
            const modal = document.getElementById('delete-project-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    </script>

</body>
</html>