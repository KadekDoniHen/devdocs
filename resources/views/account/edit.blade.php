@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 w-full space-y-6">

    <!-- Page Header -->
    <div>
        <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition inline-flex items-center gap-1 mb-2">
            &larr; Kembali ke Dashboard
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Akun</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola identitas profil dan keamanan password akun DevDocs Anda.</p>
    </div>

    <!-- Profile Summary Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white font-bold text-lg flex items-center justify-center shrink-0 shadow-md shadow-indigo-500/20">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white truncate">{{ $user->name }}</h2>
                    <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $user->email }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 shrink-0">
            <div class="text-center">
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->projects_count }}</div>
                <div class="text-[11px]">Proyek</div>
            </div>
            <div class="w-px h-7 bg-slate-200 dark:bg-slate-800"></div>
            <div class="text-center">
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->documents_count }}</div>
                <div class="text-[11px]">Dokumen</div>
            </div>
            <div class="w-px h-7 bg-slate-200 dark:bg-slate-800"></div>
            <div class="text-center">
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->created_at->format('d M Y') }}</div>
                <div class="text-[11px]">Bergabung</div>
            </div>
        </div>
    </div>

    <!-- Card 1: Informasi Profil -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xs">
        <div class="pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Informasi Profil</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbarui nama tampilan dan alamat email untuk masuk ke aplikasi.</p>
        </div>

        <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap *</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('name') ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-slate-700' }} focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">
                @error('name')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Email *</label>
                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('email') ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-slate-700' }} focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">
                @error('email')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Role Akun</label>
                <input type="text" value="{{ ucfirst($user->role) }}" disabled
                    class="w-full max-w-xs px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm shadow-indigo-500/20 transition cursor-pointer">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

    <!-- Card 2: Keamanan & Password -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xs">
        <div class="pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Keamanan &amp; Password</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Gunakan password minimal 8 karakter untuk menjaga keamanan akun Anda.</p>
        </div>

        <form action="{{ route('account.password.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password Saat Ini *</label>
                <input type="password" name="current_password" id="current_password" required autocomplete="current-password"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('current_password') ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-slate-700' }} focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">
                @error('current_password')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password Baru *</label>
                    <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('password') ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-slate-700' }} focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">
                    @error('password')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Konfirmasi Password Baru *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-xl shadow-sm transition cursor-pointer">
                    Perbarui Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
