@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 w-full">

    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center gap-1 mb-2">
            &larr; Kembali ke Dashboard
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Tulis Dokumen Baru</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Buat Technical Specification Document (TSD), API Reference, atau panduan teknis.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xs">
        <form action="{{ route('documents.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="project_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Proyek *</label>
                    <select name="project_id" id="project_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" {{ (old('project_id', $selectedProject->id ?? null) == $p->id) ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="folder_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Folder (Opsional)</label>
                    <select name="folder_id" id="folder_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
                        <option value="">(Root Dokumen)</option>
                        @if ($selectedProject && $selectedProject->folders)
                            @foreach ($selectedProject->folders as $f)
                                <option value="{{ $f->id }}" {{ (old('folder_id', $folderId) == $f->id) ? 'selected' : '' }}>
                                    📁 {{ $f->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul Dokumen *</label>
                <input type="text" name="title" id="title" required placeholder="Contoh: QRIS Payment Webhook, Access Token Authentication" value="{{ old('title') }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-500/20 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500">
            </div>

            <div>
                <label for="summary" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Ringkasan Singkat</label>
                <textarea name="summary" id="summary" rows="2" placeholder="Jelaskan secara singkat kegunaan atau cakupan dokumen ini..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none placeholder:text-slate-400 dark:placeholder:text-slate-500">{{ old('summary') }}</textarea>
            </div>

            <div>
                <label for="content_markdown" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Isi Dokumen Awal (Markdown)</label>
                <textarea name="content_markdown" id="content_markdown" rows="8"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-sm font-mono text-slate-900 dark:text-slate-100 outline-none">{{ old('content_markdown', "# Overview\n\nTuliskan spesifikasi teknis di sini...\n\n```mermaid\nsequenceDiagram\n    Client->>Server: Request\n    Server-->>Client: Response\n```") }}</textarea>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Awal</label>
                <select name="status" id="status" class="w-full max-w-xs px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 outline-none">
                    <option value="draft">Draft (Belum Dipublikasikan)</option>
                    <option value="published">Published (Dapat Dibaca)</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm shadow-indigo-500/20 transition">
                    Buat & Mulai Mengedit &rarr;
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const projectFoldersMap = @json($projectFoldersMap ?? []);
        const projectSelect = document.getElementById('project_id');
        const folderSelect = document.getElementById('folder_id');

        if (!projectSelect || !folderSelect) return;

        function syncFolders(selectedFolderId = null) {
            const projectId = projectSelect.value;
            const folders = projectFoldersMap[projectId] || [];

            folderSelect.innerHTML = '<option value="">(Root Dokumen)</option>';
            folders.forEach((f) => {
                const option = document.createElement('option');
                option.value = f.id;
                option.textContent = `📁 ${f.name}`;
                if (selectedFolderId !== null && String(f.id) === String(selectedFolderId)) {
                    option.selected = true;
                }
                folderSelect.appendChild(option);
            });
        }

        projectSelect.addEventListener('change', () => syncFolders(null));

        // Ensure initial state matches old() / query param if user came back from validation error
        const initialFolderId = @json(old('folder_id', $folderId));
        if (projectSelect.value) {
            syncFolders(initialFolderId);
        }
    });
</script>
@endpush
@endsection