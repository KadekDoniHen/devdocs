@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 w-full">

    <!-- Search Input -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mb-4">Pencarian Global</h1>
        
        <form action="{{ route('search') }}" method="GET" class="relative">
            <input type="text" name="q" value="{{ $query }}" placeholder="Cari nama proyek, judul dokumen, atau kata kunci..." autofocus
                class="w-full pl-11 pr-24 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-2xl text-sm text-slate-900 dark:text-slate-100 outline-none shadow-xs transition placeholder:text-slate-400 dark:placeholder:text-slate-500">
            <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 absolute left-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <button type="submit" class="absolute right-2.5 top-2 px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl transition">
                Cari
            </button>
        </form>
    </div>

    @if ($query !== '')
        <div class="mb-4 text-xs text-slate-500 dark:text-slate-400">
            Hasil pencarian untuk "<strong class="text-slate-800 dark:text-slate-200">{{ $query }}</strong>":
        </div>

        <!-- Matched Projects -->
        @if ($projects->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">Proyek Terkait</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($projects as $p)
                        <a href="{{ route('projects.show', $p->slug) }}" class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:shadow-2xs transition block">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">{{ $p->name }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">{{ $p->description }}</p>
                            <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium mt-2 inline-block">{{ $p->documents_count }} dokumen</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Matched Documents -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">Dokumen Terkait</h3>
            
            @if ($documents->isEmpty())
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                    Tidak ditemukan dokumen yang cocok dengan kata kunci tersebut.
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl divide-y divide-slate-100 dark:divide-slate-800 shadow-2xs overflow-hidden">
                    @foreach ($documents as $doc)
                        <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $doc->project->name }}</span>
                                @if ($doc->folder)
                                    <span class="text-xs text-slate-400 dark:text-slate-500">/ {{ $doc->folder->name }}</span>
                                @endif
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $doc->status === 'published' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300' }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </div>

                            <a href="{{ route('documents.show', ['project' => $doc->project->slug, 'document' => $doc->slug]) }}" class="text-sm font-bold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                {{ $doc->title }}
                            </a>

                            @if ($doc->summary)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $doc->summary }}</p>
                            @endif

                            <div class="flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                                <span>Penulis: {{ $doc->author->name }}</span>
                                <span>&bull;</span>
                                <span>Revisi #{{ $doc->version }}</span>
                                <span>&bull;</span>
                                <span>Diperbarui {{ $doc->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-xs text-slate-400 dark:text-slate-500">
            Ketik kata kunci untuk mulai mencari dokumen teknis, API endpoint, atau proyek.
        </div>
    @endif

</div>
@endsection