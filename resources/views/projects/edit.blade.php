@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8 w-full">

    <div class="mb-6">
        <a href="{{ route('projects.show', $project->slug) }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center gap-1 mb-2">
            &larr; Kembali ke Proyek
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Proyek: {{ $project->name }}</h1>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xs">
        <form action="{{ route('projects.update', $project->slug) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Proyek *</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $project->name) }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi Singkat</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">{{ old('description', $project->description) }}</textarea>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Proyek</label>
                <select name="status" id="status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
                    <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="archived" {{ $project->status === 'archived' ? 'selected' : '' }}>Diarsipkan (Archived)</option>
                </select>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('projects.show', $project->slug) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm shadow-indigo-500/20 transition">
                    Perbarui Proyek &rarr;
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone: Delete Project -->
    <div class="mt-8 bg-white dark:bg-slate-900 border border-red-200 dark:border-red-500/30 rounded-2xl p-6 shadow-2xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-400 mb-1">Zona Bahaya</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Tindakan berikut bersifat destruktif dan akan menghapus proyek beserta seluruh dokumen dan foldernya.</p>
        
        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
            <div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Hapus Proyek Ini</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500">Seluruh folder, dokumen, dan endpoint akan dihapus permanen.</p>
            </div>
            <form action="{{ route('projects.destroy', $project->slug) }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? Seluruh proyek \'{{ $project->name }}\' beserta seluruh dokumen di dalamnya akan dihapus.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition cursor-pointer shadow-sm shadow-red-500/20">
                    Hapus Proyek
                </button>
            </form>
        </div>
    </div>

</div>
@endsection