@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 w-full">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $document->slug]) }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center gap-1 mb-2">
                &larr; Kembali ke Dokumen
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Riwayat Revisi Dokumen</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar snapshot versi untuk: <strong class="text-slate-700 dark:text-slate-200">{{ $document->title }}</strong></p>
        </div>
        <div class="text-xs text-slate-500 dark:text-slate-400">
            Versi Aktif: <strong class="text-indigo-600 dark:text-indigo-400 text-sm">v{{ $document->version }}</strong>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-2xs">
        @if ($revisions->isEmpty())
            <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                Belum ada riwayat snapshot revisi tersimpan.
            </div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($revisions as $rev)
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <div>
                            <div class="flex items-center gap-2.5 mb-1.5">
                                <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-md bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                                    Revisi #{{ $rev->revision_number }}
                                </span>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $rev->title }}</h3>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-1.5">{{ $rev->change_summary ?? 'Penyimpanan berkala' }}</p>
                            <div class="flex items-center gap-3 text-xs text-slate-400 dark:text-slate-500">
                                <span>Editor: {{ $rev->editor?->name ?? 'Sistem' }}</span>
                                <span>&bull;</span>
                                <span>{{ $rev->created_at->format('d M Y, H:i:s') }} ({{ $rev->created_at->diffForHumans() }})</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($rev->revision_number !== $document->version)
                                <form action="{{ route('documents.restore', ['project' => $project->slug, 'document' => $document->slug, 'revision' => $rev->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan dokumen ke versi Revisi #{{ $rev->revision_number }}? Snapshot saat ini akan disimpan sebagai revisi baru.')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/15 hover:bg-amber-100 dark:hover:bg-amber-500/25 border border-amber-200 dark:border-amber-500/30 rounded-xl transition">
                                        Pulihkan Versi Ini
                                    </button>
                                </form>
                            @else
                                <span class="px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 rounded-xl border border-emerald-200 dark:border-emerald-500/30">
                                    Sedang Aktif
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection