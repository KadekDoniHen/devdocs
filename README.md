# DevDocs

Aplikasi web dokumentasi teknis pribadi untuk mengelola dokumentasi proyek, arsitektur, API, dan diagram dalam satu tempat.

## Fitur Utama

- Manajemen proyek dan folder bertingkat
- Editor dokumen (Markdown & visual editor) dengan autosave dan syntax highlighting
- Dukungan diagram Mermaid
- Riwayat revisi (revision history) dan restore versi lama
- Pencarian lintas proyek dan dokumen
- Export dokumen
- AI Assistant opsional (Gemini) untuk membantu membuat dan merapikan dokumen
- Autentikasi dan pengaturan akun

## Tech Stack

- **Backend:** PHP 8.3+, Laravel 13, Blade
- **Frontend:** Tailwind CSS 4, Vite, Tiptap, Marked, Highlight.js, Mermaid
- **Database:** SQLite (default), PostgreSQL / MySQL

## Cara Menjalankan

1. Setup dependency, `.env`, migrasi, dan build aset:
   ```bash
   composer run setup
   ```
2. (Opsional) Isi data demo & akun default:
   ```bash
   php artisan db:seed
   ```
   Akun default: `admin@devdocs.local` / `password`
3. Jalankan server development:
   ```bash
   composer run dev
   ```

> **Opsional (AI Assistant):** Tambahkan `GEMINI_API_KEY` di file `.env` untuk mengaktifkan fitur AI Assistant.
