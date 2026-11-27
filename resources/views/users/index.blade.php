@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Tim & User</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola akun anggota tim yang dapat login dan berkolaborasi di DevDocs (khusus Admin).</p>
        </div>

        <button type="button" onclick="document.getElementById('modal-create-user').classList.remove('hidden')"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm shadow-indigo-500/20 transition cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>+ Tambah User Baru</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Anggota Tim</h2>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $users->count() }} akun terdaftar</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-5 py-3">Anggota</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Role Sistem</th>
                        <th class="px-5 py-3">Kontribusi</th>
                        <th class="px-5 py-3">Bergabung</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach ($users as $teamUser)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($teamUser->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $teamUser->name }}</span>
                                        @if ($teamUser->id === Auth::id())
                                            <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-600 dark:text-slate-300">{{ $teamUser->email }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $teamUser->role === 'admin' ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                                    {{ $teamUser->role === 'admin' ? 'Admin' : 'Member' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                                {{ $teamUser->projects_count }} proyek &bull; {{ $teamUser->documents_count }} dokumen
                            </td>
                            <td class="px-5 py-3.5 text-slate-400 dark:text-slate-500">
                                {{ $teamUser->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button"
                                        onclick="openEditUserModal({{ $teamUser->id }}, '{{ addslashes($teamUser->name) }}', '{{ addslashes($teamUser->email) }}', '{{ $teamUser->role }}')"
                                        class="px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/20 hover:text-indigo-600 dark:hover:text-indigo-300 rounded-lg transition cursor-pointer">
                                        Edit
                                    </button>

                                    @if ($teamUser->id !== Auth::id())
                                        <form action="{{ route('users.destroy', $teamUser) }}" method="POST" onsubmit="return confirm('Hapus user {{ addslashes($teamUser->name) }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/15 hover:bg-red-100 dark:hover:bg-red-500/25 rounded-lg transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Modal: Create User --}}
<div id="modal-create-user" class="hidden fixed inset-0 z-50 bg-slate-900/40 dark:bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah User Baru</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Buat akun baru untuk anggota tim Anda.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-create-user').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                ✕
            </button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="create-user-name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                <input type="text" name="name" id="create-user-name" required placeholder="Contoh: Budi Santoso"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
            </div>

            <div>
                <label for="create-user-email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Email *</label>
                <input type="email" name="email" id="create-user-email" required placeholder="budi@tim.local"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
            </div>

            <div>
                <label for="create-user-password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Password Awal * (Min. 6 karakter)</label>
                <input type="password" name="password" id="create-user-password" required minlength="6" placeholder="••••••••"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
            </div>

            <div>
                <label for="create-user-role" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Role Sistem</label>
                <select name="role" id="create-user-role" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
                    <option value="member" selected>Member (User Biasa — Akses Sesuai Proyek yang Dibagikan)</option>
                    <option value="admin">Admin (Kelola Semua Proyek & Manajemen User)</option>
                </select>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modal-create-user').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm transition cursor-pointer">
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit User --}}
<div id="modal-edit-user" class="hidden fixed inset-0 z-50 bg-slate-900/40 dark:bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Data User</h3>
            <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                ✕
            </button>
        </div>

        <form id="edit-user-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit-user-name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                <input type="text" name="name" id="edit-user-name" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
            </div>

            <div>
                <label for="edit-user-email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Email *</label>
                <input type="email" name="email" id="edit-user-email" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
            </div>

            <div>
                <label for="edit-user-role" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Role Sistem</label>
                <select name="role" id="edit-user-role" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
                    <option value="member">Member (User Biasa)</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div>
                <label for="edit-user-password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Reset Password Baru (Opsional)</label>
                <input type="password" name="password" id="edit-user-password" minlength="6" placeholder="Kosongkan jika tidak ingin mengganti password"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditUserModal(id, name, email, role) {
        const form = document.getElementById('edit-user-form');
        form.action = `/users/${id}`;
        document.getElementById('edit-user-name').value = name;
        document.getElementById('edit-user-email').value = email;
        document.getElementById('edit-user-role').value = role === 'admin' ? 'admin' : 'member';
        document.getElementById('edit-user-password').value = '';
        document.getElementById('modal-edit-user').classList.remove('hidden');
    }
</script>
@endpush
@endsection
