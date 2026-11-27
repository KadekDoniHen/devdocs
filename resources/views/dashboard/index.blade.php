@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Header & Welcome Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard Dokumentasi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola Technical Specification, API Reference, dan Arsitektur Sistem.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-2xs">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Proyek Baru
            </a>
            <a href="{{ route('documents.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl transition shadow-sm shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Tulis Dokumen
            </a>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-indigo-200 dark:hover:border-indigo-500/40 shadow-2xs transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Proyek</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ $stats['total_projects'] }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Grup sistem terdaftar</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-sky-200 dark:hover:border-sky-500/40 shadow-2xs transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Dokumen</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ $stats['total_documents'] }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">TSD, API, & Catatan</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-emerald-200 dark:hover:border-emerald-500/40 shadow-2xs transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Published</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-2">{{ $stats['published_documents'] }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Dokumentasi aktif</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-amber-200 dark:hover:border-amber-500/40 shadow-2xs transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Draft Dokumen</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-amber-700 dark:text-amber-300 mt-2">{{ $stats['draft_documents'] }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Sedang dikerjakan</div>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Proyek Aktif</h2>
            <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition">Lihat Semua Proyek &rarr;</a>
        </div>

        @if ($projects->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Belum ada proyek</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">Mulai dengan membuat proyek dokumentasi pertama Anda.</p>
                <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-lg transition">
                    + Buat Proyek
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($projects as $project)
                    <a href="{{ route('projects.show', $project->slug) }}" class="group bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-500/60 hover:shadow-md dark:hover:shadow-indigo-950/30 transition duration-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:bg-indigo-600 dark:group-hover:bg-indigo-500 group-hover:text-white transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $project->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $project->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 line-clamp-2 leading-relaxed">
                                {{ $project->description ?? 'Tidak ada deskripsi proyek.' }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 dark:text-slate-500">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center gap-1 font-medium text-slate-600 dark:text-slate-300">
                                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    {{ $project->documents_count }} dokumen
                                </span>
                                <span>&bull;</span>
                                <span>{{ $project->folders_count }} folder</span>
                            </div>
                            <span class="text-[11px]">{{ $project->updated_at->diffForHumans() }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Recent Documents Table -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Dokumentasi Terakhir Diedit</h2>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
            @if ($recentDocuments->isEmpty())
                <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                    Belum ada dokumen yang dibuat. Klik tombol "+ Tulis Dokumen" di atas untuk membuat dokumen pertama.
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($recentDocuments as $doc)
                        <div class="p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('documents.show', ['project' => $doc->project->slug, 'document' => $doc->slug]) }}" class="text-sm font-semibold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate block">
                                        {{ $doc->title }}
                                    </a>
                                    <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                        <span class="text-indigo-600 dark:text-indigo-400 font-medium">{{ $doc->project->name }}</span>
                                        <span>&bull;</span>
                                        <span>Oleh {{ $doc->author->name }}</span>
                                        <span>&bull;</span>
                                        <span>Revisi #{{ $doc->version }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $doc->status === 'published' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30' }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                                <span class="text-xs text-slate-400 dark:text-slate-500 hidden sm:inline">{{ $doc->updated_at->diffForHumans() }}</span>
                                @if ($doc->project->canEdit(Auth::user()))
                                    <a href="{{ route('documents.edit', ['project' => $doc->project->slug, 'document' => $doc->slug]) }}" class="px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition">
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Team Activity Feed -->
    @if (isset($recentActivities) && $recentActivities->isNotEmpty())
        <div class="mt-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Aktivitas Tim Terbaru</h2>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xs divide-y divide-slate-100 dark:divide-slate-800 overflow-hidden">
                @foreach ($recentActivities as $activity)
                    <div class="px-4 py-3 flex items-center justify-between gap-4 text-xs">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-bold text-[10px] flex items-center justify-center shrink-0">
                                {{ $activity->user ? strtoupper(substr($activity->user->name, 0, 2)) : 'SY' }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-slate-700 dark:text-slate-200 truncate">{{ $activity->description }}</p>
                                @if ($activity->project)
                                    <a href="{{ route('projects.show', $activity->project->slug) }}" class="text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $activity->project->name }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 shrink-0">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection