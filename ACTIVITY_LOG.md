# DevDocs — Codebase Activity Log

File ini mencatat riwayat perubahan kode, penambahan fitur, perbaikan bug, dan hasil pengujian pada project DevDocs agar setiap pembaruan terdokumentasi dan ikut tersimpan di repository Git.

---

## [2026-11-27] Team Collaboration, Admin User Management & Permission System

### Fitur Baru
1. **Manajemen User Khusus Admin (`/users`):**
   - Menambahkan middleware `EnsureUserIsAdmin` (`admin`) di `app/Http/Middleware/EnsureUserIsAdmin.php` dan registrasi alias di `bootstrap/app.php`.
   - Menambahkan `UserManagementController` (`app/Http/Controllers/UserManagementController.php`) dan view `resources/views/users/index.blade.php` untuk membuat user baru, mengubah role (`admin` / `member`), mereset password, dan menghapus user (dengan proteksi admin tidak bisa menghapus/menurunkan akunnya sendiri).
   - Menu **Manajemen Tim** di sidebar (`resources/views/layouts/app.blade.php`) hanya tampil untuk user dengan role `admin`.

2. **Berbagi Proyek ke Anggota Tim & Sistem Permission (`project_members`):**
   - Menambahkan tabel `project_members` melalui migrasi `database/migrations/2026_11_27_000001_create_team_collaboration_tables.php` dan model `ProjectMember`.
   - Menambahkan 3 level izin proyek:
     - `viewer` (**Lihat Saja**): membaca dokumen, riwayat revisi, ekspor, dan berkomentar.
     - `editor` (**Bisa Edit**): seluruh izin `viewer` + membuat/mengedit/memindahkan/mempublikasikan/menghapus dokumen & folder.
     - `manager` (**Kelola Proyek**): seluruh izin `editor` + mengelola anggota tim, mengatur share publik, edit pengaturan proyek, dan hapus proyek.
   - Menambahkan `ProjectMemberController` (`app/Http/Controllers/ProjectMemberController.php`) untuk menambah, mengubah permission, dan mencabut akses anggota.
   - Menambahkan isolasi & pengecekan akses menyeluruh di `Project::scopeAccessibleBy()`, `Project::roleFor()`, `canView()`, `canEdit()`, `canManage()` serta penegakan di `AppServiceProvider`, `DashboardController`, `SearchController`, `ProjectController`, dan `DocumentController`.
   - Memperbarui modal **Bagikan** di `resources/views/projects/show.blade.php` untuk mengelola anggota tim sekaligus link publik.

3. **Fitur Kolaborasi Tim (Komentar Dokumen, Aktivitas Tim, Indikator Peran):**
   - Menambahkan tabel & model `document_comments` (`DocumentComment`, `DocumentCommentController`) untuk diskusi tim langsung di halaman baca dokumen (`resources/views/documents/show.blade.php`).
   - Menambahkan tabel & model `activity_logs` (`ActivityLog`) untuk mencatat aktivitas proyek/dokumen/anggota/user secara real-time dan menampilkannya di `resources/views/dashboard/index.blade.php`.
   - Menambahkan badge peran dan avatar anggota tim pada daftar & detail proyek (`resources/views/projects/index.blade.php`, `resources/views/projects/show.blade.php`).
   - Membuat operator pencarian di `SearchController.php` adaptif (`ILIKE` untuk PostgreSQL, `LIKE` untuk SQLite/lainnya).

### Pengujian Otomatis
- Menambahkan `tests/Feature/TeamCollaborationTest.php` untuk menguji akses admin vs member pada manajemen user, penegakan izin `viewer`/`editor`/`manager`, isolasi proyek non-anggota, komentar dokumen, dan pencatatan aktivitas tim.

### Konfigurasi Production & `.gitignore`
- Memperbarui `.env.production.example` dan membuat `.env.production` lokal (ter-ignore Git) sesuai endpoint PostgreSQL Wasmer (`psql.fr-roub1.bengt.wasmernet.com:20184`, database `db_68ee9e74`, user `user_f6d5a816`, `APP_URL=https://devdocs.wasmer.app`, password dikosongkan).
- Memperbarui fallback `config/database.php` (`pgsql.database`) agar membaca `DB_DATABASE` atau `DB_NAME`.
- Memperketat `.gitignore` agar seluruh `.env` lokal, file SQLite lokal, cache/vendor/build, dan konfigurasi editor/AI lokal (`/.claude`, `/.gemini`, `/.agent`, `/.agents`, `/.mcp.json`, `/boost.json`) tidak ikut ter-push.

---

## [2026-11-26] Public Project Sharing, Login Security & PaaS Deployment

### Fitur & Perubahan
1. **Konfigurasi Production & PaaS (`Wasmer` / `Nixpacks`):**
   - Menambahkan `trustProxies(at: '*')` di `bootstrap/app.php` dan `URL::forceScheme('https')` untuk production di `AppServiceProvider.php`.
   - Menambahkan `.env.production.example`, `nixpacks.toml`, serta script switch `env:dev` dan `env:prod` di `composer.json`.
2. **Keamanan Halaman Login (`resources/views/auth/login.blade.php`):**
   - Menghapus pre-fill kredensial admin default dan kotak hint demo dari form login.
3. **Share Proyek ke Publik (Read-Only tanpa Login):**
   - Menambahkan migrasi `2026_11_26_000001_add_public_sharing_to_projects_table.php` (`is_public`, `share_token`).
   - Menambahkan `PublicProjectController`, route `/share/{token}` & `/share/{token}/docs/{documentSlug}`, serta layout publik (`resources/views/layouts/public.blade.php`, `resources/views/public/project.blade.php`, `resources/views/public/document.blade.php`).
   - Menambahkan `tests/Feature/PublicProjectShareTest.php`.
4. **Perbaikan Bug Tampilan Share:**
   - Memperbaiki area putih di bagian bawah layout publik saat tema gelap (`bg-slate-50` pada `<html>` dipindahkan ke container penuh).
   - Memperbaiki scroll konten utama (`#doc-reader-main`) pada tampilan dokumen publik agar dapat di-scroll independen dari sidebar kiri & TOC kanan.
