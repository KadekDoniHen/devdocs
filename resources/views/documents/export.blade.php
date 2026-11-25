<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $document->title }} — DevDocs Export</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/reader.js'])

    <style>
        /* CSS @page with zero margin completely strips browser default header (date/title) and footer (URL/pages) */
        @page {
            size: auto;
            margin: 0;
        }

        @media print {
            .no-print,
            .code-copy-btn,
            button.code-copy-btn,
            #export-loading-overlay {
                display: none !important;
                visibility: hidden !important;
            }

            body {
                background: #ffffff !important;
                color: #0f172a !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .export-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 16mm 18mm !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased p-4 md:p-8">

    <!-- Top Action Bar (Hidden on print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-sm font-bold text-slate-900">Mode Ekspor Dokumen</h3>
                
                <!-- Status Badge -->
                <span id="export-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <svg class="w-3 h-3 animate-spin text-amber-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses dokumen...</span>
                </span>
            </div>
            <p id="export-status-desc" class="text-xs text-slate-500">Sedang merender Markdown, diagram teknis, dan pewarnaan kode.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" onclick="window.close()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition cursor-pointer">
                Tutup
            </button>
            <button type="button" id="btn-print-pdf" onclick="triggerPrint()" disabled
                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-sm shadow-indigo-200 transition-all duration-150 cursor-pointer">
                <svg id="btn-print-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span id="btn-print-text">Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Exportable Document Container -->
    <div class="export-container relative max-w-4xl mx-auto bg-white p-8 md:p-14 rounded-2xl border border-slate-200 shadow-sm">
        
        <!-- Loading Progress Overlay (Hidden once ready) -->
        <div id="export-loading-overlay" class="no-print absolute inset-0 z-20 bg-white/90 backdrop-blur-xs rounded-2xl flex flex-col items-center justify-center p-8 transition-opacity duration-300">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center mb-4 text-indigo-600 shadow-xs">
                <svg class="w-7 h-7 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h4 class="text-base font-bold text-slate-900 mb-1">Sedang Menyiapkan Dokumen PDF...</h4>
            <p class="text-xs text-slate-500 max-w-md text-center leading-relaxed">
                Memproses struktur teks, pewarnaan sintaks baris kode, dan merender diagram alur sistem. Mohon tunggu beberapa saat.
            </p>
            <div class="w-48 h-1.5 bg-slate-100 rounded-full overflow-hidden mt-4">
                <div class="w-full h-full bg-indigo-600 rounded-full animate-pulse"></div>
            </div>
        </div>

        <!-- Header -->
        <div class="border-b border-slate-200 pb-6 mb-8">
            <div class="text-xs uppercase tracking-wider font-bold text-indigo-600 mb-1">
                {{ $project->name }} &bull; Technical Specification
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">{{ $document->title }}</h1>
            
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 mt-3">
                <span>Versi: <strong>#{{ $document->version }}</strong></span>
                <span>&bull;</span>
                <span>Penulis: <strong>{{ $document->author->name }}</strong></span>
                <span>&bull;</span>
                <span>Status: <strong>{{ ucfirst($document->status) }}</strong></span>
                <span>&bull;</span>
                <span>Tanggal: <strong>{{ $document->updated_at->format('d F Y') }}</strong></span>
            </div>

            @if ($document->summary)
                <div class="mt-4 p-3 bg-slate-50 border-l-4 border-indigo-600 text-xs text-slate-700 italic">
                    {{ $document->summary }}
                </div>
            @endif
        </div>

        <!-- Raw Markdown for reader.js -->
        <textarea id="raw-markdown" class="hidden">{{ $document->content_markdown }}</textarea>

        <!-- Target -->
        <article id="doc-render-target" class="markdown-body">
            <p class="text-xs text-slate-400">Merender dokumen...</p>
        </article>

        <!-- API Endpoints if any -->
        @if ($document->apiEndpoints->isNotEmpty())
            <div class="mt-12 pt-8 border-t border-slate-200">
                <h2 class="text-xl font-bold text-slate-900 mb-4">Spesifikasi Endpoint API</h2>
                @foreach ($document->apiEndpoints as $api)
                    <div class="api-endpoint-card border border-slate-200 rounded-xl p-4 mb-4 text-xs">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded font-bold uppercase bg-slate-800 text-white">{{ $api->method }}</span>
                            <code class="font-mono font-semibold">{{ $api->path }}</code>
                        </div>
                        <p class="text-slate-600 mb-2">{{ $api->description }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Footer note (hidden on print so the bottom of every page stays clean) -->
        <div class="no-print mt-14 pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
            Dokumen diekspor dari DevDocs Hub pada {{ now()->format('d M Y, H:i') }}
        </div>
    </div>

    <script>
        /**
         * Manage loading state and print triggers
         */
        function onDocumentReady() {
            const overlay = document.getElementById('export-loading-overlay');
            if (overlay) {
                overlay.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => overlay.remove(), 300);
            }

            const badge = document.getElementById('export-status-badge');
            if (badge) {
                badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                badge.innerHTML = `
                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Dokumen Siap Dicetak</span>
                `;
            }

            const desc = document.getElementById('export-status-desc');
            if (desc) {
                desc.textContent = 'Semua diagram dan baris kode selesai dirender dengan format tajam.';
            }

            const btn = document.getElementById('btn-print-pdf');
            if (btn) {
                btn.removeAttribute('disabled');
            }
        }

        function triggerPrint() {
            const btn = document.getElementById('btn-print-pdf');
            const text = document.getElementById('btn-print-text');
            const icon = document.getElementById('btn-print-icon');

            if (btn && text) {
                btn.setAttribute('disabled', 'disabled');
                text.textContent = 'Membuka dialog cetak...';
                if (icon) {
                    icon.classList.add('animate-spin');
                }
            }

            // Short timeout to let the DOM update the button status before print dialog blocks execution
            setTimeout(() => {
                window.print();
                setTimeout(() => {
                    if (btn && text) {
                        btn.removeAttribute('disabled');
                        text.textContent = 'Cetak / Simpan PDF';
                        if (icon) {
                            icon.classList.remove('animate-spin');
                        }
                    }
                }, 1000);
            }, 100);
        }

        // Listen for the custom devdocs:ready event from reader.js
        window.addEventListener('devdocs:ready', () => {
            onDocumentReady();
        });

        // Fallback in case window.__devdocs_rendered was already set
        if (window.__devdocs_rendered) {
            onDocumentReady();
        }

        // Safety fallback timer (max 4s) so user is never permanently stuck
        setTimeout(() => {
            onDocumentReady();
        }, 4000);
    </script>

</body>
</html>
