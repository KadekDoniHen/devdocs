@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/reader.js'])
@endpush

@section('content')
@php
    $currentUser = Auth::user();
    $canEditDoc = $project->canEdit($currentUser);
    $canManageProject = $project->canManage($currentUser);
@endphp
<div class="flex-1 flex min-h-0 h-full overflow-hidden">

    <!-- Left Sidebar: Project & Folder Tree Navigation -->
    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 hidden md:flex flex-col shrink-0 min-h-0 h-full dd-scroller">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <a href="{{ route('projects.show', $project->slug) }}" class="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                <span class="truncate">{{ $project->name }}</span>
            </a>
            @if ($canEditDoc)
                <a href="{{ route('documents.create', ['project_id' => $project->id]) }}" title="Tambah Dokumen" class="text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </a>
            @endif
        </div>

        <nav class="p-3 space-y-1 text-xs flex-1">
            <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Navigasi Dokumen</div>

            <!-- Root Documents -->
            @foreach ($project->rootDocuments as $rd)
                <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $rd->slug]) }}"
                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg transition truncate {{ $rd->id === $document->id ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100' }}">
                    <svg class="w-3.5 h-3.5 shrink-0 {{ $rd->id === $document->id ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="truncate">{{ $rd->title }}</span>
                </a>
            @endforeach

            <!-- Root Folders -->
            @foreach ($project->rootFolders as $f)
                <div class="pt-1.5">
                    <div class="flex items-center gap-1.5 px-2 py-1 font-semibold text-slate-700 dark:text-slate-300 truncate">
                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        <span class="truncate">{{ $f->name }}</span>
                    </div>

                    <div class="pl-3 space-y-0.5 border-l border-slate-100 dark:border-slate-800 ml-3.5 mt-0.5">
                        @foreach ($f->documents as $fdoc)
                            <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $fdoc->slug]) }}"
                               class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition truncate {{ $fdoc->id === $document->id ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100' }}">
                                <svg class="w-3.5 h-3.5 shrink-0 {{ $fdoc->id === $document->id ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="truncate">{{ $fdoc->title }}</span>
                            </a>
                        @endforeach

                        @foreach ($f->children as $sub)
                            <div class="pt-1">
                                <div class="flex items-center gap-1.5 px-2 py-0.5 font-medium text-slate-600 dark:text-slate-300 truncate">
                                    <svg class="w-3 h-3 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                    <span class="truncate">{{ $sub->name }}</span>
                                </div>
                                <div class="pl-2 space-y-0.5 border-l border-slate-100 dark:border-slate-800 ml-2 mt-0.5">
                                    @foreach ($sub->documents as $sdoc)
                                        <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $sdoc->slug]) }}"
                                           class="flex items-center gap-1.5 px-2 py-1 rounded-md transition truncate {{ $sdoc->id === $document->id ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100' }}">
                                            <span class="truncate">{{ $sdoc->title }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>

    <!-- Center: Document Content Reader -->
    <main id="doc-reader-main" class="flex-1 min-h-0 min-w-0 h-full px-6 lg:px-12 py-8 bg-white dark:bg-slate-950">
        <div class="max-w-4xl mx-auto">

            <!-- Top Action Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-6 border-b border-slate-200 dark:border-slate-800 mb-8">
                <!-- Breadcrumbs -->
                <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                    <a href="{{ route('projects.show', $project->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ $project->name }}</a>
                    @if ($document->folder)
                        <span>/</span>
                        <span>{{ $document->folder->name }}</span>
                    @endif
                    <span>/</span>
                    <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $document->title }}</span>
                </div>

                <!-- Action Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    @if ($canEditDoc)
                        <form action="{{ route('documents.publish', ['project' => $project->slug, 'document' => $document->slug]) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition {{ $document->status === 'published' ? 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/15 border-amber-200 dark:border-amber-500/30 hover:bg-amber-100 dark:hover:bg-amber-500/25' : 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-100 dark:hover:bg-emerald-500/25' }}">
                                {{ $document->status === 'published' ? 'Unpublish (Jadikan Draft)' : 'Publikasikan' }}
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('documents.revisions', ['project' => $project->slug, 'document' => $document->slug]) }}" class="px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition" title="Riwayat Versi">
                        Revisi v{{ $document->version }}
                    </a>

                    <a href="{{ route('documents.export', ['project' => $project->slug, 'document' => $document->slug]) }}" target="_blank" class="px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition" title="Export HTML / Print PDF">
                        <svg class="w-4 h-4 inline-block -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Export
                    </a>

                    @if ($canEditDoc)
                        <a href="{{ route('documents.edit', ['project' => $project->slug, 'document' => $document->slug]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-lg shadow-sm shadow-indigo-500/20 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit Dokumen
                        </a>

                        <button type="button" onclick="document.getElementById('modal-move-doc').classList.remove('hidden')"
                            class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/15 rounded-lg transition cursor-pointer active:scale-95" title="Pindah Folder">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </button>

                        <form action="{{ route('documents.destroy', ['project' => $project->slug, 'document' => $document->slug]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 rounded-lg transition cursor-pointer" title="Hapus Dokumen">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Document Meta Info -->
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $document->status === 'published' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30' }}">
                        {{ ucfirst($document->status) }}
                    </span>
                    <span class="text-xs text-slate-400 dark:text-slate-600">&bull;</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Versi Dokumen: <strong class="text-slate-800 dark:text-slate-200">#{{ $document->version }}</strong></span>
                    <span class="text-xs text-slate-400 dark:text-slate-600">&bull;</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">Terakhir diedit {{ $document->updated_at->format('d M Y, H:i') }}</span>
                </div>

                @if ($document->summary)
                    <p class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 leading-relaxed italic">
                        {{ $document->summary }}
                    </p>
                @endif
            </div>

            <!-- Raw Markdown source for reader.js to process -->
            <textarea id="raw-markdown" class="hidden">{{ $document->content_markdown }}</textarea>

            <!-- Render Target for Markdown & Mermaid -->
            <article id="doc-render-target" class="markdown-body">
                <p class="text-sm text-slate-400 dark:text-slate-500 animate-pulse">Merender isi dokumentasi teknis...</p>
            </article>

            <!-- Linked API Endpoints Section (Swagger/Apidog style card) -->
            @if ($document->apiEndpoints->isNotEmpty())
                <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Spesifikasi Endpoint API Terkait</h2>
                    </div>

                    <div class="space-y-6">
                        @foreach ($document->apiEndpoints as $api)
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-2xs">
                                <!-- Endpoint Header -->
                                <div class="p-4 bg-slate-900 dark:bg-slate-900/95 border-b border-slate-800 text-white flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-md uppercase tracking-wider {{ $api->method === 'POST' ? 'bg-emerald-500 text-white' : ($api->method === 'GET' ? 'bg-sky-500 text-white' : ($api->method === 'DELETE' ? 'bg-red-500 text-white' : 'bg-amber-500 text-white')) }}">
                                            {{ $api->method }}
                                        </span>
                                        <code class="font-mono text-sm text-indigo-300 font-semibold">{{ $api->path }}</code>
                                    </div>
                                    <span class="text-xs text-slate-400">{{ $api->name }}</span>
                                </div>

                                <div class="p-5 space-y-4 text-xs">
                                    @if ($api->description)
                                        <p class="text-slate-600 dark:text-slate-300">{{ $api->description }}</p>
                                    @endif

                                    <!-- Headers -->
                                    @if (!empty($api->headers))
                                        <div>
                                            <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-2 uppercase tracking-wider text-[11px]">Request Headers</h4>
                                            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                                                <table class="w-full text-left">
                                                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                                        <tr>
                                                            <th class="p-2.5 border-b border-slate-200 dark:border-slate-700">Header Key</th>
                                                            <th class="p-2.5 border-b border-slate-200 dark:border-slate-700">Contoh Value</th>
                                                            <th class="p-2.5 border-b border-slate-200 dark:border-slate-700">Wajib</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                                        @foreach ($api->headers as $h)
                                                            <tr>
                                                                <td class="p-2.5 font-mono text-slate-800 dark:text-slate-200 font-medium">{{ $h['key'] ?? '' }}</td>
                                                                <td class="p-2.5 font-mono text-slate-500 dark:text-slate-400">{{ $h['value'] ?? '' }}</td>
                                                                <td class="p-2.5 text-slate-600 dark:text-slate-300">{{ !empty($h['required']) ? 'Ya' : 'Opsional' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Request Body Sample -->
                                    @if (!empty($api->request_body))
                                        <div>
                                            <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-1.5 uppercase tracking-wider text-[11px]">Payload Schema</h4>
                                            <pre class="bg-slate-900 text-indigo-300 p-3 rounded-xl font-mono text-xs overflow-x-auto border border-slate-800"><code>{{ json_encode($api->request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Team Discussion & Comments -->
            <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                        Diskusi & Catatan Tim ({{ $document->comments->count() }})
                    </h2>
                </div>

                @if ($document->comments->isNotEmpty())
                    <div class="space-y-3 mb-5">
                        @foreach ($document->comments as $comment)
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-md bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold flex items-center justify-center">
                                            {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                                        </span>
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-100">{{ $comment->user->name }}</span>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">&bull; {{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if ($comment->user_id === $currentUser->id || $canManageProject)
                                        <form action="{{ route('documents.comments.destroy', ['project' => $project->slug, 'document' => $document->slug, 'comment' => $comment->id]) }}" method="POST" onsubmit="return confirm('Hapus komentar ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[11px] text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $comment->body }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('documents.comments.store', ['project' => $project->slug, 'document' => $document->slug]) }}" method="POST" class="space-y-2.5">
                    @csrf
                    <textarea name="body" rows="2" required placeholder="Tulis catatan, review, atau pertanyaan untuk tim..."
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-xs text-slate-900 dark:text-slate-100 outline-none transition"></textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm transition cursor-pointer">
                            Kirim Komentar
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <!-- Right Sidebar: Table of Contents & Info -->
    <aside class="w-64 bg-slate-50/70 dark:bg-slate-900/70 border-l border-slate-200 dark:border-slate-800 hidden xl:flex flex-col shrink-0 p-5 min-h-0 h-full dd-scroller">
        <div class="sticky top-20 space-y-6">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2.5">Daftar Isi (TOC)</h3>
                <nav id="toc-list" class="space-y-1">
                    <p class="text-xs text-slate-400 dark:text-slate-500 italic">Menganalisis heading...</p>
                </nav>
            </div>

            <div class="pt-5 border-t border-slate-200 dark:border-slate-800 space-y-3 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex justify-between">
                    <span>Penulis:</span>
                    <strong class="text-slate-700 dark:text-slate-200">{{ $document->author->name }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>Editor Terakhir:</span>
                    <strong class="text-slate-700 dark:text-slate-200">{{ $document->lastEditor?->name ?? '-' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>Versi:</span>
                    <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-mono text-[11px]">v{{ $document->version }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Dibuat:</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ $document->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                <a href="{{ route('documents.export', ['project' => $project->slug, 'document' => $document->slug]) }}" target="_blank" class="w-full py-2 px-3 text-xs font-semibold text-center text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl block transition shadow-2xs">
                    Cetak / Simpan PDF &rarr;
                </a>
            </div>
        </div>
    </aside>

</div>

{{-- Modal: Pindah Folder --}}
<div id="modal-move-doc" class="hidden fixed inset-0 z-50 bg-slate-900/40 dark:bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pindah Dokumen</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih folder tujuan dalam proyek <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $project->name }}</span></p>
            </div>
            <button type="button" onclick="document.getElementById('modal-move-doc').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('documents.move', ['project' => $project->slug, 'document' => $document->slug]) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label for="move-folder-id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pindahkan ke Folder</label>
                <select name="folder_id" id="move-folder-id"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">
                    <option value="">— Root Proyek (Tanpa Folder) —</option>
                    @foreach ($project->folders as $f)
                        <option value="{{ $f->id }}" {{ $document->folder_id === $f->id ? 'selected' : '' }}>
                            {{ $f->name }}
                        </option>
                    @endforeach
                </select>
                @if ($document->folder)
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1.5">Lokasi saat ini: <span class="text-slate-600 dark:text-slate-300 font-medium">{{ $document->folder->name }}</span></p>
                @else
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1.5">Lokasi saat ini: <span class="text-slate-600 dark:text-slate-300 font-medium">Root Proyek</span></p>
                @endif
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modal-move-doc').classList.add('hidden')"
                    class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
                    Batal
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 active:scale-95 rounded-xl shadow-sm transition-all duration-150 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    Pindahkan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection