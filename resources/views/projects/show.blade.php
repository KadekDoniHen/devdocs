@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Project Breadcrumbs & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800 mb-8">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 mb-1">
                <a href="{{ route('projects.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Proyek</a>
                <span>/</span>
                <span class="text-slate-600 dark:text-slate-300 font-medium">{{ $project->name }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $project->name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">{{ $project->description ?? 'Tidak ada deskripsi.' }}</p>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" onclick="document.getElementById('modal-add-folder').classList.remove('hidden')" class="group inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 bg-white dark:bg-slate-900 hover:bg-indigo-50/60 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-500/50 rounded-xl shadow-2xs hover:shadow-xs active:scale-95 transition-all duration-150 cursor-pointer">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                <span>+ Folder</span>
            </button>
            <a href="{{ route('documents.create', ['project_id' => $project->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 active:scale-95 rounded-xl shadow-sm shadow-indigo-500/20 transition-all duration-150 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>+ Tulis Dokumen</span>
            </a>
            <a href="{{ route('projects.edit', $project->slug) }}" class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-95 rounded-xl transition-all duration-150 cursor-pointer" title="Pengaturan Proyek">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </a>
            <button type="button" 
                onclick="openDeleteProjectModal('{{ $project->slug }}', '{{ addslashes($project->name) }}')"
                class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 active:scale-95 rounded-xl transition-all duration-150 cursor-pointer" 
                title="Hapus Proyek">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Layout: Left Folder Tree, Right Documents -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        <!-- Sidebar Folder Tree -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-2xs sticky top-24">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Struktur Dokumen</h3>
                    <button type="button" onclick="document.getElementById('modal-add-folder').classList.remove('hidden')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 bg-indigo-50/60 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 border border-indigo-100 dark:border-indigo-500/30 rounded-lg transition-all duration-150 cursor-pointer active:scale-95 shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Folder</span>
                    </button>
                </div>

                <div class="space-y-1 text-xs">
                    <!-- Root Documents -->
                    @foreach ($project->rootDocuments as $rd)
                        <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $rd->slug]) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="truncate">{{ $rd->title }}</span>
                        </a>
                    @endforeach

                    <!-- Root Folders -->
                    @foreach ($project->rootFolders as $folder)
                        <div class="pt-1">
                            <div class="flex items-center justify-between px-2 py-1 font-semibold text-slate-800 dark:text-slate-200">
                                <div class="flex items-center gap-1.5 truncate">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                    <span class="truncate">{{ $folder->name }}</span>
                                </div>
                                <div class="flex items-center gap-0.5">
                                    <a href="{{ route('documents.create', ['project_id' => $project->id, 'folder_id' => $folder->id]) }}" title="Tambah dokumen ke folder ini" class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition cursor-pointer active:scale-90 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </a>
                                    <form action="{{ route('projects.folders.destroy', ['project' => $project->slug, 'folder' => $folder->id]) }}" method="POST" onsubmit="return confirm('Hapus folder \'{{ $folder->name }}\'? Dokumen di dalamnya akan dipindahkan ke root.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-300 dark:text-slate-600 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 rounded-md transition cursor-pointer active:scale-90 text-xs" title="Hapus Folder">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Documents inside folder -->
                            <div class="pl-4 space-y-0.5 border-l border-slate-100 dark:border-slate-800 ml-3 mt-1">
                                @foreach ($folder->documents as $fdoc)
                                    <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $fdoc->slug]) }}" class="flex items-center gap-2 px-2 py-1 rounded-md text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                                        <svg class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span class="truncate">{{ $fdoc->title }}</span>
                                    </a>
                                @endforeach

                                <!-- Subfolders -->
                                @foreach ($folder->children as $subfolder)
                                    <div class="pt-1">
                                        <div class="flex items-center justify-between px-2 py-1 font-medium text-slate-700 dark:text-slate-300">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                                <span class="truncate">{{ $subfolder->name }}</span>
                                            </div>
                                            <div class="flex items-center gap-0.5">
                                                <a href="{{ route('documents.create', ['project_id' => $project->id, 'folder_id' => $subfolder->id]) }}" title="Tambah dokumen" class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition cursor-pointer active:scale-90 text-xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                </a>
                                                <form action="{{ route('projects.folders.destroy', ['project' => $project->slug, 'folder' => $subfolder->id]) }}" method="POST" onsubmit="return confirm('Hapus folder \'{{ $subfolder->name }}\'?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-slate-300 dark:text-slate-600 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 rounded-md transition cursor-pointer active:scale-90 text-xs" title="Hapus Folder">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="pl-3 space-y-0.5 border-l border-slate-100 dark:border-slate-800 ml-2 mt-0.5">
                                            @foreach ($subfolder->documents as $sdoc)
                                                <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $sdoc->slug]) }}" class="flex items-center gap-2 px-2 py-1 rounded-md text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                                                    <svg class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    <span class="truncate">{{ $sdoc->title }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Content: Document List -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Seluruh Dokumen Proyek</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $documents->count() }} dokumen terdaftar pada proyek ini</p>
                    </div>
                </div>

                @if ($documents->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Belum ada dokumen</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">Tambahkan Technical Specification atau API documentation baru.</p>
                        <a href="{{ route('documents.create', ['project_id' => $project->id]) }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl transition">
                            + Tulis Dokumen Pertama
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($documents as $doc)
                            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $doc->slug]) }}" class="text-sm font-semibold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate">
                                            {{ $doc->title }}
                                        </a>
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $doc->status === 'published' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30' }}">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                                        @if ($doc->folder)
                                            <span class="flex items-center gap-1 text-slate-600 dark:text-slate-300">
                                                <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                                {{ $doc->folder->name }}
                                            </span>
                                            <span>&bull;</span>
                                        @endif
                                        <span>Penulis: {{ $doc->author->name }}</span>
                                        <span>&bull;</span>
                                        <span>Revisi v{{ $doc->version }}</span>
                                        <span>&bull;</span>
                                        <span>Diperbarui {{ $doc->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $doc->slug]) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/20 hover:text-indigo-600 dark:hover:text-indigo-300 rounded-lg transition">
                                        Buka Dokumen
                                    </a>
                                    <button type="button"
                                        onclick="openMoveDocModal('{{ $doc->slug }}', {{ $doc->folder_id ?? 'null' }})"
                                        class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/15 rounded-lg transition cursor-pointer active:scale-95" title="Pindah Folder">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    </button>
                                    <a href="{{ route('documents.edit', ['project' => $project->slug, 'document' => $doc->slug]) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('documents.destroy', ['project' => $project->slug, 'document' => $doc->slug]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen \'{{ $doc->title }}\'?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 rounded-lg transition cursor-pointer" title="Hapus Dokumen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Modal: Buat Folder --}}
    <div id="modal-add-folder" class="hidden fixed inset-0 z-50 bg-slate-900/40 dark:bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Buat Folder Baru</h3>
                <button onclick="document.getElementById('modal-add-folder').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
            </div>

            <form action="{{ route('projects.folders.store', $project->slug) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="folder-name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Folder *</label>
                    <input type="text" name="name" id="folder-name" required placeholder="Contoh: Architecture, API Documentation, Database"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none placeholder:text-slate-400 dark:placeholder:text-slate-500">
                </div>

                <div>
                    <label for="parent-folder" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Folder Induk (Opsional)</label>
                    <select name="parent_id" id="parent-folder" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
                        <option value="">(Root Folder)</option>
                        @foreach ($project->rootFolders as $rf)
                            <option value="{{ $rf->id }}">{{ $rf->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="document.getElementById('modal-add-folder').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm transition">
                        Buat Folder
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Pindah Dokumen --}}
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

            <form id="move-doc-form" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PATCH">

                <div>
                    <label for="move-folder-select" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pindahkan ke Folder</label>
                    <select name="folder_id" id="move-folder-select"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">
                        <option value="">— Root Proyek (Tanpa Folder) —</option>
                        @foreach ($project->folders as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
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

</div>

@push('scripts')
<script>
    /**
     * Open the move-document modal and configure the form action + selected folder.
     *
     * @param {string} docSlug
     * @param {number|null} currentFolderId
     */
    function openMoveDocModal(docSlug, currentFolderId) {
        const form = document.getElementById('move-doc-form');
        form.action = `/projects/{{ $project->slug }}/docs/${docSlug}/move`;

        const select = document.getElementById('move-folder-select');
        select.value = currentFolderId ?? '';

        document.getElementById('modal-move-doc').classList.remove('hidden');
    }
</script>
@endpush
@endsection