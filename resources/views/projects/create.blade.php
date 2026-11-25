@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8 w-full">

    <div class="mb-6">
        <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center gap-1 mb-2">
            &larr; Kembali ke Daftar Proyek
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Buat Proyek Baru</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Proyek berfungsi sebagai wadah modul dokumentasi sistem Anda.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xs">
        <form action="{{ route('projects.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Proyek *</label>
                <input type="text" name="name" id="name" required placeholder="Contoh: Payment Center, Kawitan Core, Auth Service" value="{{ old('name') }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500">
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Slug URL akan dibuat otomatis berdasarkan nama proyek.</p>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi Singkat</label>
                <textarea name="description" id="description" rows="3" placeholder="Tuliskan tujuan, lingkup arsitektur, atau fungsionalitas utama proyek ini..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500">{{ old('description') }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('projects.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm shadow-indigo-500/20 transition">
                    Simpan Proyek &rarr;
                </button>
            </div>
        </form>
    </div>

</div>
@endsection