@extends('layouts.public', ['title' => $document->title . ' — ' . $project->name])

@push('scripts')
    @vite(['resources/js/reader.js'])
@endpush

@section('content')
<div class="flex-1 flex min-h-0 h-full overflow-hidden">

    <!-- Left Sidebar: Project & Folder Tree Navigation (Read-Only) -->
    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 hidden md:flex flex-col shrink-0 min-h-0 h-full dd-scroller">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800">
            <a href="{{ route('public.projects.show', ['token' => $project->share_token]) }}" class="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span class="truncate">Semua Dokumen</span>
            </a>
        </div>

        <nav class="p-3 space-y-1 text-xs flex-1">
            <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Navigasi Dokumen</div>

            @foreach ($project->rootDocuments as $rd)
                <a href="{{ route('public.projects.document', ['token' => $project->share_token, 'documentSlug' => $rd->slug]) }}"
                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg transition truncate {{ $rd->id === $document->id ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100' }}">
                    <svg class="w-3.5 h-3.5 shrink-0 {{ $rd->id === $document->id ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="truncate">{{ $rd->title }}</span>
                </a>
            @endforeach

            @foreach ($project->rootFolders as $f)
                @php
                    $hasVisibleDocs = $f->documents->isNotEmpty() || $f->children->contains(fn ($sub) => $sub->documents->isNotEmpty());
                @endphp
                @if ($hasVisibleDocs)
                    <div class="pt-1.5">
                        <div class="flex items-center gap-1.5 px-2 py-1 font-semibold text-slate-700 dark:text-slate-300 truncate">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            <span class="truncate">{{ $f->name }}</span>
                        </div>

                        <div class="pl-3 space-y-0.5 border-l border-slate-100 dark:border-slate-800 ml-3.5 mt-0.5">
                            @foreach ($f->documents as $fdoc)
                                <a href="{{ route('public.projects.document', ['token' => $project->share_token, 'documentSlug' => $fdoc->slug]) }}"
                                   class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition truncate {{ $fdoc->id === $document->id ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0 {{ $fdoc->id === $document->id ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span class="truncate">{{ $fdoc->title }}</span>
                                </a>
                            @endforeach

                            @foreach ($f->children as $sub)
                                @if ($sub->documents->isNotEmpty())
                                    <div class="pt-1">
                                        <div class="flex items-center gap-1.5 px-2 py-0.5 font-medium text-slate-600 dark:text-slate-300 truncate">
                                            <svg class="w-3 h-3 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                            <span class="truncate">{{ $sub->name }}</span>
                                        </div>
                                        <div class="pl-2 space-y-0.5 border-l border-slate-100 dark:border-slate-800 ml-2 mt-0.5">
                                            @foreach ($sub->documents as $sdoc)
                                                <a href="{{ route('public.projects.document', ['token' => $project->share_token, 'documentSlug' => $sdoc->slug]) }}"
                                                   class="flex items-center gap-1.5 px-2 py-1 rounded-md transition truncate {{ $sdoc->id === $document->id ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-semibold' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100' }}">
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
        </nav>
    </aside>

    <!-- Center: Read-Only Document Content -->
    <main id="doc-reader-main" class="flex-1 min-h-0 min-w-0 h-full px-6 lg:px-12 py-8 bg-white dark:bg-slate-950">
        <div class="max-w-4xl mx-auto">

            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 pb-6 border-b border-slate-200 dark:border-slate-800 mb-8">
                <a href="{{ route('public.projects.show', ['token' => $project->share_token]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ $project->name }}</a>
                @if ($document->folder)
                    <span>/</span>
                    <span>{{ $document->folder->name }}</span>
                @endif
                <span>/</span>
                <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $document->title }}</span>
            </div>

            <!-- Document Meta -->
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-2 text-xs text-slate-500 dark:text-slate-400">
                    <span>Versi Dokumen: <strong class="text-slate-800 dark:text-slate-200">#{{ $document->version }}</strong></span>
                    <span class="text-slate-400 dark:text-slate-600">&bull;</span>
                    <span>Terakhir diperbarui {{ $document->updated_at->format('d M Y, H:i') }}</span>
                </div>

                @if ($document->summary)
                    <p class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 leading-relaxed italic">
                        {{ $document->summary }}
                    </p>
                @endif
            </div>

            <!-- Raw Markdown source for reader.js -->
            <textarea id="raw-markdown" class="hidden">{{ $document->content_markdown }}</textarea>

            <!-- Render Target for Markdown & Mermaid -->
            <article id="doc-render-target" class="markdown-body">
                <p class="text-sm text-slate-400 dark:text-slate-500 animate-pulse">Merender isi dokumentasi teknis...</p>
            </article>

            <!-- Linked API Endpoints Section -->
            @if ($document->apiEndpoints->isNotEmpty())
                <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Spesifikasi Endpoint API Terkait</h2>
                    </div>

                    <div class="space-y-6">
                        @foreach ($document->apiEndpoints as $api)
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-2xs">
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

        </div>
    </main>

    <!-- Right Sidebar: Table of Contents & Meta -->
    <aside class="w-64 bg-slate-50/70 dark:bg-slate-900/70 border-l border-slate-200 dark:border-slate-800 hidden xl:flex flex-col shrink-0 p-5 min-h-0 h-full dd-scroller">
        <div class="space-y-6">
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
                    <span>Versi:</span>
                    <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-mono text-[11px]">v{{ $document->version }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Diperbarui:</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ $document->updated_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </aside>

</div>
@endsection
