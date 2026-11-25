@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/editor.js'])
@endpush

@section('content')
<div class="flex-1 flex flex-col h-full min-h-0 overflow-hidden bg-slate-100 dark:bg-slate-950">

    <form id="document-form" action="{{ route('documents.update', ['project' => $project->slug, 'document' => $document->slug]) }}" method="POST" class="flex-1 flex flex-col h-full min-h-0 overflow-hidden">
        @csrf
        @method('PUT')

        <!-- Hidden Inputs for Autosave & Version Control -->
        <input type="hidden" id="autosave-url" value="{{ route('documents.autosave', ['project' => $project->slug, 'document' => $document->slug]) }}">
        <input type="hidden" name="version" id="document-version" value="{{ $document->version }}">

        <!-- Top Editor Navigation Bar -->
        <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 shrink-0 shadow-2xs">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <a href="{{ route('documents.show', ['project' => $project->slug, 'document' => $document->slug]) }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Batal & Kembali">
                    &larr; Keluar
                </a>

                <div class="min-w-0 flex-1 max-w-xl">
                    <input type="text" name="title" id="document-title" required value="{{ old('title', $document->title) }}" placeholder="Judul Dokumen..."
                        class="w-full text-base font-bold text-slate-900 dark:text-white bg-transparent border-0 focus:ring-0 p-0 outline-none placeholder:text-slate-400 dark:placeholder:text-slate-500">
                </div>

                <!-- Autosave Badge Indicator -->
                <div id="autosave-status" class="text-xs flex items-center gap-1.5 text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full border border-slate-200 dark:border-slate-700 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Siap
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Folder selector -->
                <select name="folder_id" class="text-xs px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none text-slate-700 dark:text-slate-200">
                    <option value="">(Root Dokumen)</option>
                    @foreach ($folders as $f)
                        <option value="{{ $f->id }}" {{ $document->folder_id === $f->id ? 'selected' : '' }}>📁 {{ $f->name }}</option>
                    @endforeach
                </select>

                <!-- Status selector -->
                <select name="status" class="text-xs px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none font-medium {{ $document->status === 'published' ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">
                    <option value="draft" {{ $document->status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ $document->status === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="archived" {{ $document->status === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>

                <!-- Save Button (Submits form & creates formal revision snapshot) -->
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-sm shadow-indigo-500/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Revisi
                </button>
            </div>
        </div>

        <!-- Formatting Toolbar -->
        <div class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800 px-4 py-1.5 flex items-center justify-between gap-2 overflow-x-auto shrink-0 text-xs text-slate-700 dark:text-slate-300">
            <div class="flex items-center gap-1">
                <!-- Undo & Redo buttons -->
                <button type="button" id="btn-undo" class="px-2 py-1 flex items-center gap-1 font-medium hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition opacity-50 cursor-pointer" title="Undo (Ctrl + Z)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"></path></svg>
                    Undo
                </button>
                <button type="button" id="btn-redo" class="px-2 py-1 flex items-center gap-1 font-medium hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition opacity-50 cursor-pointer" title="Redo (Ctrl + Y)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4"></path></svg>
                    Redo
                </button>
                <span class="text-slate-300 dark:text-slate-700">|</span>
                <button type="button" id="btn-bold" class="px-2 py-1 font-bold hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition" title="Bold">B</button>
                <button type="button" id="btn-italic" class="px-2 py-1 italic hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition" title="Italic">I</button>
                <span class="text-slate-300 dark:text-slate-700">|</span>
                <button type="button" id="btn-h2" class="px-2 py-1 font-semibold hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition" title="Heading 2">H2</button>
                <button type="button" id="btn-h3" class="px-2 py-1 font-medium hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition" title="Heading 3">H3</button>
                <span class="text-slate-300 dark:text-slate-700">|</span>
                <button type="button" id="btn-quote" class="px-2 py-1 hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition" title="Blockquote">” Kutipan</button>
                <button type="button" id="btn-code" class="px-2 py-1 font-mono hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition" title="Inline Code">&lt;/&gt;</button>
                <button type="button" id="btn-codeblock" class="px-2 py-1 font-mono hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition" title="Code Block">{ }</button>
                <button type="button" id="btn-table" class="px-2 py-1 hover:bg-white dark:hover:bg-slate-800 hover:shadow-2xs rounded transition" title="Tabel">▦ Tabel</button>
                <span class="text-slate-300 dark:text-slate-700">|</span>
                <!-- Mermaid Template buttons -->
                <button type="button" id="btn-mermaid-seq" class="px-2.5 py-1 text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 rounded-lg font-semibold transition" title="Sisipkan Sequence Diagram">
                    ⚡ Sequence Diagram
                </button>
                <button type="button" id="btn-mermaid-flow" class="px-2.5 py-1 text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 rounded-lg font-semibold transition" title="Sisipkan Flowchart">
                    ⚡ Flowchart
                </button>
                <span class="text-slate-300 dark:text-slate-700">|</span>
                <!-- Gemini AI Assistant Button -->
                <button type="button" id="btn-ai-modal" class="px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:text-indigo-200 bg-gradient-to-r from-indigo-100 via-purple-100 to-indigo-100 dark:from-indigo-500/25 dark:via-purple-500/25 dark:to-indigo-500/25 hover:from-indigo-200 hover:to-purple-200 dark:hover:from-indigo-500/35 dark:hover:to-purple-500/35 rounded-lg transition flex items-center gap-1.5 shadow-2xs border border-indigo-200 dark:border-indigo-500/30 cursor-pointer" title="AI Assistant (Google Gemini)">
                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>✨ AI Assistant (Gemini)</span>
                </button>
            </div>

            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-mono hidden md:flex items-center gap-2">
                <span><span class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-1 py-0.5 rounded">Ctrl + Z</span> Undo</span>
                <span>&bull;</span>
                <span><span class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-1 py-0.5 rounded">Ctrl + Y</span> Redo</span>
                <span>&bull;</span>
                <span><span class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-1 py-0.5 rounded">Ctrl + S</span> Simpan</span>
            </div>
        </div>

        <!-- Split Pane: Left Editor, Right Live Preview -->
        <div id="split-container" class="flex-1 flex overflow-hidden relative w-full min-h-0">
            <!-- Left Pane: Editor -->
            <div id="editor-pane" class="w-full md:w-1/2 flex flex-col bg-white dark:bg-slate-950 overflow-hidden shrink-0 h-full min-h-0" style="min-width: 260px;">
                <textarea name="content_markdown" id="markdown-editor" spellcheck="false"
                    class="w-full flex-1 p-5 font-mono text-sm leading-relaxed text-slate-800 dark:text-slate-200 resize-none border-0 focus:ring-0 outline-none bg-transparent overflow-y-auto overscroll-contain h-full placeholder:text-slate-400 dark:placeholder:text-slate-600"
                    placeholder="Tuliskan dokumentasi Markdown atau diagram Mermaid di sini...">{{ old('content_markdown', $document->content_markdown) }}</textarea>
            </div>

            <!-- Draggable Resizer Gutter (Divider) -->
            <div id="split-resizer" role="separator" aria-orientation="vertical" tabindex="0"
                aria-label="Pengatur lebar panel editor dan preview"
                class="hidden md:flex w-2.5 bg-slate-100 dark:bg-slate-900 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 active:bg-indigo-200 dark:active:bg-indigo-500/30 cursor-col-resize items-center justify-center shrink-0 border-x border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-colors select-none group z-10"
                title="Geser ke kiri / kanan untuk menyesuaikan ukuran panel (Klik 2x untuk reset 50:50)">
                <!-- Grab Handle Pill with dots -->
                <div class="w-1 h-9 rounded-full bg-slate-300 dark:bg-slate-700 group-hover:bg-indigo-500 dark:group-hover:bg-indigo-400 group-active:bg-indigo-600 transition-colors flex flex-col justify-between py-1.5 items-center pointer-events-none">
                    <span class="w-0.5 h-0.5 bg-white dark:bg-slate-900 rounded-full"></span>
                    <span class="w-0.5 h-0.5 bg-white dark:bg-slate-900 rounded-full"></span>
                    <span class="w-0.5 h-0.5 bg-white dark:bg-slate-900 rounded-full"></span>
                </div>
            </div>

            <!-- Right Pane: Live Preview -->
            <div id="preview-pane" class="flex-1 bg-white dark:bg-slate-950 p-6 overflow-y-auto overscroll-contain hidden md:block min-w-0 h-full" style="min-width: 260px;">
                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3 pb-1 border-b border-slate-100 dark:border-slate-800 shrink-0">
                    <span>Live Preview &bull; Mermaid & Markdown</span>
                    <button type="button" id="btn-reset-split" class="hidden text-[10px] font-semibold lowercase text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 hover:underline cursor-pointer transition flex items-center gap-1" title="Kembalikan ukuran 50:50">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>reset 50:50</span>
                    </button>
                </div>
                <div id="editor-preview" class="markdown-body">
                    <!-- Dynamic preview loaded by editor.js -->
                </div>
            </div>
        </div>

    </form>

    <!-- Modal AI Assistant (Gemini Pro) -->
    <div id="modal-ai-assistant" class="hidden fixed inset-0 z-50 bg-slate-900/50 dark:bg-black/75 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col max-h-[90vh] overflow-hidden">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">DevDocs AI Assistant</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Didukung oleh Google Gemini Pro &bull; Otomatisasi & Pemolesan TSD</p>
                    </div>
                </div>
                <button type="button" id="btn-close-ai-modal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">✕</button>
            </div>

            <!-- Tabs & Model Selector Bar -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-4 pb-2 border-b border-slate-100 dark:border-slate-800 text-xs">
                <div class="flex items-center gap-2 font-semibold">
                    <button type="button" id="tab-ai-refine" class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 transition">
                        ✨ Rapikan Dokumen Ini
                    </button>
                    <button type="button" id="tab-ai-generate" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        📝 Generate TSD Baru
                    </button>
                </div>

                <!-- Model Selector Dropdown -->
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-xl">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Model:</span>
                    <select id="ai-model-select" class="bg-transparent text-xs text-indigo-700 dark:text-indigo-300 font-bold focus:outline-none cursor-pointer">
                        <option value="gemini-3.5-flash">Gemini 3.5 Flash (Stabil & Cepat)</option>
                        <option value="gemini-3.7-flash">Gemini 3.7 Flash (Terbaru)</option>
                        <option value="gemini-3.6-flash">Gemini 3.6 Flash</option>
                        <option value="gemini-3.1-flash-lite">Gemini 3.1 Flash-Lite</option>
                        <option value="gemini-3.1-pro-preview">Gemini 3.1 Pro Preview</option>
                    </select>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto py-4 space-y-4 text-xs dd-scroller">
                
                <!-- Status Banner if API Key missing -->
                <div id="ai-apikey-warning" class="hidden p-3 bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 rounded-xl text-amber-800 dark:text-amber-300">
                    <strong>⚠️ API Key Belum Terpasang:</strong> Tambahkan <code>GEMINI_API_KEY=kunci_anda</code> pada file <code>.env</code> aplikasi untuk mengaktifkan AI.
                </div>

                <!-- Mode 1: Refine Instructions -->
                <div id="ai-refine-panel">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Instruksi Khusus (Opsional):</label>
                    <input type="text" id="ai-refine-instructions" placeholder="Misal: 'Buatkan sequence diagram alurnya' atau 'Format jadi tabel parameter'..."
                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-xs text-slate-900 dark:text-slate-100 outline-none placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Gemini akan membaca isi editor saat ini, memperbaiki hierarki heading, membuat diagram Mermaid jika ada alur proses, dan merapikan bahasanya.</p>
                </div>

                <!-- Mode 2: Generate Prompt -->
                <div id="ai-generate-panel" class="hidden">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan / Deskripsi Fitur yang Ingin Dibuat:</label>
                    <textarea id="ai-generate-prompt" rows="3" placeholder="Contoh: Buatkan TSD untuk integrasi QRIS Static Payment dengan webhook callback, validasi signature HMAC-SHA256, dan update status transaksi di PostgreSQL..."
                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 rounded-xl text-xs text-slate-900 dark:text-slate-100 outline-none placeholder:text-slate-400 dark:placeholder:text-slate-500"></textarea>
                </div>

                <!-- Action Button -->
                <div class="flex items-center justify-between pt-1">
                    <button type="button" id="btn-trigger-ai" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-xl shadow-sm transition cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span>Mulai Proses dengan Gemini Pro</span>
                    </button>
                    <span id="ai-loading-indicator" class="hidden text-indigo-600 dark:text-indigo-400 font-medium flex items-center gap-1.5 animate-pulse">
                        <svg class="animate-spin w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Gemini sedang memproses...
                    </span>
                </div>

                <!-- AI Output Preview -->
                <div id="ai-output-container" class="hidden pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-bold text-slate-800 dark:text-slate-200">Hasil dari Gemini:</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">✓ Selesai diproses</span>
                    </div>
                    <textarea id="ai-result-text" rows="10"
                        class="w-full p-3 bg-slate-900 dark:bg-slate-950 text-slate-100 font-mono text-xs rounded-xl border border-slate-800 outline-none leading-relaxed"></textarea>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" id="btn-cancel-ai" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg">
                    Tutup
                </button>
                <button type="button" id="btn-apply-ai" disabled class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl transition shadow-sm">
                    Terapkan ke Editor &rarr;
                </button>
            </div>

        </div>
    </div>

</div>
@endsection