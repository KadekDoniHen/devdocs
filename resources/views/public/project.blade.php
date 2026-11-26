@extends('layouts.public', ['title' => $project->name . ' — DevDocs'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Project Header -->
    <div class="pb-6 border-b border-slate-200 dark:border-slate-800 mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $project->name }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 max-w-3xl leading-relaxed">
            {{ $project->description ?? 'Dokumentasi teknis publik (hanya lihat).' }}
        </p>
    </div>

    <!-- Layout: Left Folder Tree, Right Published Documents -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        <!-- Sidebar Folder Tree -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-2xs sticky top-6">
                <div class="pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Struktur Dokumen</h3>
                </div>

                <div class="space-y-1 text-xs">
                    @foreach ($project->rootDocuments as $rd)
                        <a href="{{ route('public.projects.document', ['token' => $project->share_token, 'documentSlug' => $rd->slug]) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="truncate">{{ $rd->title }}</span>
                        </a>
                    @endforeach

                    @foreach ($project->rootFolders as $folder)
                        @php
                            $hasVisibleDocs = $folder->documents->isNotEmpty() || $folder->children->contains(fn ($sub) => $sub->documents->isNotEmpty());
                        @endphp
                        @if ($hasVisibleDocs)
                            <div class="pt-1">
                                <div class="flex items-center gap-1.5 px-2 py-1 font-semibold text-slate-800 dark:text-slate-200 truncate">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                    <span class="truncate">{{ $folder->name }}</span>
                                </div>

                                <div class="pl-4 space-y-0.5 border-l border-slate-100 dark:border-slate-800 ml-3 mt-1">
                                    @foreach ($folder->documents as $fdoc)
                                        <a href="{{ route('public.projects.document', ['token' => $project->share_token, 'documentSlug' => $fdoc->slug]) }}" class="flex items-center gap-2 px-2 py-1 rounded-md text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                                            <svg class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span class="truncate">{{ $fdoc->title }}</span>
                                        </a>
                                    @endforeach

                                    @foreach ($folder->children as $subfolder)
                                        @if ($subfolder->documents->isNotEmpty())
                                            <div class="pt-1">
                                                <div class="flex items-center gap-1.5 px-2 py-1 font-medium text-slate-700 dark:text-slate-300 truncate">
                                                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                                    <span class="truncate">{{ $subfolder->name }}</span>
                                                </div>
                                                <div class="pl-3 space-y-0.5 border-l border-slate-100 dark:border-slate-800 ml-2 mt-0.5">
                                                    @foreach ($subfolder->documents as $sdoc)
                                                        <a href="{{ route('public.projects.document', ['token' => $project->share_token, 'documentSlug' => $sdoc->slug]) }}" class="flex items-center gap-2 px-2 py-1 rounded-md text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                                                            <svg class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                            <span class="truncate">{{ $sdoc->title }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if ($documents->isEmpty())
                        <p class="text-xs text-slate-400 dark:text-slate-500 px-2 py-2 italic">Belum ada dokumen yang dipublikasikan.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content: Published Document List -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Dokumen</h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $documents->count() }} dokumen tersedia untuk dibaca</p>
                </div>

                @if ($documents->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Belum ada dokumen publik</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pemilik proyek belum mempublikasikan dokumen pada proyek ini.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($documents as $doc)
                            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <a href="{{ route('public.projects.document', ['token' => $project->share_token, 'documentSlug' => $doc->slug]) }}" class="text-sm font-semibold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition block truncate mb-1">
                                        {{ $doc->title }}
                                    </a>
                                    @if ($doc->summary)
                                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mb-1.5">{{ $doc->summary }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                                        @if ($doc->folder)
                                            <span class="flex items-center gap-1 text-slate-600 dark:text-slate-300">
                                                <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                                {{ $doc->folder->name }}
                                            </span>
                                            <span>&bull;</span>
                                        @endif
                                        <span>Revisi v{{ $doc->version }}</span>
                                        <span>&bull;</span>
                                        <span>Diperbarui {{ $doc->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <a href="{{ route('public.projects.document', ['token' => $project->share_token, 'documentSlug' => $doc->slug]) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/20 hover:text-indigo-600 dark:hover:text-indigo-300 rounded-lg transition">
                                        Baca &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
