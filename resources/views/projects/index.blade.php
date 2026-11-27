@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Daftar Proyek</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelompokkan dokumentasi berdasarkan sistem atau repository software.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl transition shadow-sm shadow-indigo-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            + Proyek Baru
        </a>
    </div>

    @if ($projects->isEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Belum ada proyek</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">Mulai dengan membuat proyek dokumentasi pertama Anda.</p>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-lg transition">
                + Buat Proyek Baru
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($projects as $project)
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-500/60 hover:shadow-md dark:hover:shadow-indigo-950/30 transition duration-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $project->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                                @php $roleLabel = $project->roleFor(Auth::user()); @endphp
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 capitalize">
                                    {{ $roleLabel }}
                                </span>
                            </div>
                            @if ($project->canManage(Auth::user()))
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('projects.edit', $project->slug) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition" title="Edit Proyek">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <button type="button" 
                                        onclick="openDeleteProjectModal('{{ $project->slug }}', '{{ addslashes($project->name) }}')"
                                        class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 rounded-lg transition cursor-pointer" 
                                        title="Hapus Proyek">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <a href="{{ route('projects.show', $project->slug) }}" class="block group">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $project->name }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 line-clamp-3 leading-relaxed">
                                {{ $project->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                        </a>
                    </div>

                    <div class="pt-5 mt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400 dark:text-slate-500">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $project->documents_count }} dokumen</span>
                            <span>&bull;</span>
                            <span>{{ $project->folders_count }} folder</span>
                        </div>
                        <a href="{{ route('projects.show', $project->slug) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition">
                            Buka &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    @endif

</div>
@endsection