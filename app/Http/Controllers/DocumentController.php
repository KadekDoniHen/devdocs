<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentRevision;
use App\Models\Folder;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function create(Request $request): View
    {
        $user = $request->user();

        $projects = Project::accessibleBy($user)
            ->where('status', 'active')
            ->with(['folders', 'members'])
            ->orderBy('name')
            ->get()
            ->filter(fn (Project $p) => $p->canEdit($user))
            ->values();

        $selectedProject = null;

        if ($request->has('project_id')) {
            $selectedProject = $projects->firstWhere('id', (int) $request->project_id);
        } elseif ($projects->isNotEmpty()) {
            $selectedProject = $projects->first();
        }

        $folderId = $request->query('folder_id');

        $projectFoldersMap = $projects->mapWithKeys(fn (Project $p) => [
            $p->id => $p->folders->map(fn (Folder $f) => [
                'id' => $f->id,
                'name' => $f->name,
            ])->values()->all(),
        ])->all();

        return view('documents.create', compact('projects', 'selectedProject', 'folderId', 'projectFoldersMap'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'folder_id' => [
                'nullable',
                Rule::exists('folders', 'id')->where(function ($query) use ($request) {
                    $query->where('project_id', $request->input('project_id'));
                }),
            ],
            'title' => ['required', 'string', 'max:250'],
            'summary' => ['nullable', 'string'],
            'content_markdown' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
        ], [
            'folder_id.exists' => 'Folder yang dipilih tidak termasuk dalam proyek tersebut.',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk menulis dokumen di proyek ini.');
        }

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (Document::where('project_id', $project->id)->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $document = Document::create([
            'project_id' => $project->id,
            'folder_id' => $validated['folder_id'] ?? null,
            'author_id' => Auth::id(),
            'last_editor_id' => Auth::id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'summary' => $validated['summary'] ?? null,
            'content_markdown' => $validated['content_markdown'] ?? "# {$validated['title']}\n\nTuliskan dokumentasi teknis di sini...",
            'status' => $validated['status'],
            'version' => 1,
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        // Create initial revision snapshot
        DocumentRevision::create([
            'document_id' => $document->id,
            'editor_id' => Auth::id(),
            'revision_number' => 1,
            'title' => $document->title,
            'content_markdown' => $document->content_markdown,
            'change_summary' => 'Inisialisasi dokumen baru',
            'created_at' => now(),
        ]);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'document.created',
            Auth::user()->name." membuat dokumen '{$document->title}'"
        );

        return redirect()->route('documents.edit', ['project' => $project->slug, 'document' => $document->slug])
            ->with('success', "Dokumen '{$document->title}' berhasil dibuat! Silakan lanjutkan mengedit.");
    }

    public function show(Request $request, Project $project, Document $document): View
    {
        if (! $project->canView($request->user())) {
            abort(403, 'Anda tidak memiliki akses untuk membaca dokumen pada proyek ini.');
        }

        $document->load(['author', 'lastEditor', 'apiEndpoints', 'folder', 'revisions.editor', 'comments.user']);

        // Load project folder tree for sidebar + all folders for move modal
        $project->load([
            'members',
            'rootFolders.children.documents',
            'rootFolders.documents',
            'rootDocuments',
            'folders',
        ]);

        return view('documents.show', compact('project', 'document'));
    }

    /**
     * Move document to a different folder (or root) within the same project.
     *
     * @param  array{folder_id: int|null}  $validated
     */
    public function move(Request $request, Project $project, Document $document): RedirectResponse
    {
        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk memindahkan dokumen pada proyek ini.');
        }
        $validated = $request->validate([
            'folder_id' => ['nullable', 'exists:folders,id'],
        ]);

        // Ensure the target folder belongs to this project (if provided)
        if ($validated['folder_id'] !== null) {
            $folderBelongsToProject = Folder::where('id', $validated['folder_id'])
                ->where('project_id', $project->id)
                ->exists();

            if (! $folderBelongsToProject) {
                return back()->withErrors(['folder_id' => 'Folder tidak ditemukan dalam proyek ini.']);
            }
        }

        $document->update(['folder_id' => $validated['folder_id'] ?? null]);

        $folderName = $validated['folder_id']
            ? Folder::find($validated['folder_id'])?->name
            : 'Root Proyek';

        return redirect()->route('documents.show', ['project' => $project->slug, 'document' => $document->slug])
            ->with('success', "Dokumen berhasil dipindahkan ke \"{$folderName}\".");
    }

    public function edit(Request $request, Project $project, Document $document): View
    {
        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit dokumen pada proyek ini.');
        }

        $document->load(['revisions.editor', 'apiEndpoints']);
        $folders = Folder::where('project_id', $project->id)->orderBy('name')->get();

        return view('documents.edit', compact('project', 'document', 'folders'));
    }

    public function update(Request $request, Project $project, Document $document): RedirectResponse
    {
        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah dokumen pada proyek ini.');
        }
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:250'],
            'summary' => ['nullable', 'string'],
            'folder_id' => [
                'nullable',
                Rule::exists('folders', 'id')->where(function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                }),
            ],
            'content_markdown' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'version' => ['required', 'integer'],
            'change_summary' => ['nullable', 'string', 'max:255'],
        ]);

        // Optimistic locking check (PRD Section 8.3)
        if ($document->version !== (int) $validated['version']) {
            return back()->withErrors([
                'version' => 'Konflik Perubahan: Dokumen telah diubah oleh pengguna/sesi lain (Versi database: '.$document->version.', Versi form: '.$validated['version'].'). Silakan muat ulang atau salin perubahan Anda.',
            ])->withInput();
        }

        DB::transaction(function () use ($document, $validated) {
            $newVersion = $document->version + 1;

            $document->update([
                'title' => $validated['title'],
                'summary' => $validated['summary'] ?? null,
                'folder_id' => $validated['folder_id'] ?? null,
                'content_markdown' => $validated['content_markdown'] ?? '',
                'status' => $validated['status'],
                'version' => $newVersion,
                'last_editor_id' => Auth::id(),
                'published_at' => ($validated['status'] === 'published' && ! $document->published_at) ? now() : $document->published_at,
            ]);

            // Create revision snapshot
            $maxRev = DocumentRevision::where('document_id', $document->id)->max('revision_number') ?? 0;
            DocumentRevision::create([
                'document_id' => $document->id,
                'editor_id' => Auth::id(),
                'revision_number' => $maxRev + 1,
                'title' => $document->title,
                'content_markdown' => $document->content_markdown,
                'change_summary' => $validated['change_summary'] ?? 'Pembaruan dokumen',
                'created_at' => now(),
            ]);
        });

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'document.updated',
            Auth::user()->name." memperbarui dokumen '{$document->title}' (v{$document->version})"
        );

        return redirect()->route('documents.show', ['project' => $project->slug, 'document' => $document->slug])
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    /**
     * Autosave endpoint with version check & optimistic locking (PRD Section 8.4)
     */
    public function autosave(Request $request, Project $project, Document $document): JsonResponse
    {
        if (! $project->canEdit($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit dokumen pada proyek ini.',
            ], 403);
        }
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:250'],
            'content_markdown' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],
            'version' => ['required', 'integer'],
        ]);

        if ($document->version !== (int) $validated['version']) {
            return response()->json([
                'success' => false,
                'conflict' => true,
                'message' => 'Konflik versi: Versi server ('.$document->version.') berbeda dari versi editor ('.$validated['version'].').',
                'current_version' => $document->version,
            ], 409);
        }

        $newVersion = $document->version + 1;

        $document->update([
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? $document->summary,
            'content_markdown' => $validated['content_markdown'] ?? '',
            'version' => $newVersion,
            'last_editor_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'conflict' => false,
            'version' => $newVersion,
            'updated_at' => $document->updated_at->toIso8601String(),
            'message' => 'Tersimpan otomatis',
        ]);
    }

    public function publish(Request $request, Project $project, Document $document): RedirectResponse
    {
        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah status publikasi dokumen ini.');
        }

        $newStatus = $document->status === 'published' ? 'draft' : 'published';
        $document->update([
            'status' => $newStatus,
            'published_at' => $newStatus === 'published' ? now() : $document->published_at,
            'last_editor_id' => Auth::id(),
        ]);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            $newStatus === 'published' ? 'document.published' : 'document.unpublished',
            Auth::user()->name.($newStatus === 'published' ? " mempublikasikan '{$document->title}'" : " mengembalikan '{$document->title}' ke Draft")
        );

        $msg = $newStatus === 'published' ? 'Dokumen berhasil dipublikasikan.' : 'Dokumen dikembalikan ke status Draft.';

        return back()->with('success', $msg);
    }

    public function revisions(Request $request, Project $project, Document $document): View
    {
        if (! $project->canView($request->user())) {
            abort(403, 'Anda tidak memiliki akses untuk melihat riwayat revisi dokumen ini.');
        }

        $revisions = $document->revisions()->with('editor')->get();

        return view('documents.revisions', compact('project', 'document', 'revisions'));
    }

    public function restoreRevision(Request $request, Project $project, Document $document, DocumentRevision $revision): RedirectResponse
    {
        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk memulihkan revisi dokumen ini.');
        }
        if ($revision->document_id !== $document->id) {
            abort(404);
        }

        DB::transaction(function () use ($document, $revision) {
            $newVersion = $document->version + 1;

            $document->update([
                'title' => $revision->title,
                'content_markdown' => $revision->content_markdown,
                'version' => $newVersion,
                'last_editor_id' => Auth::id(),
            ]);

            $maxRev = DocumentRevision::where('document_id', $document->id)->max('revision_number') ?? 0;
            DocumentRevision::create([
                'document_id' => $document->id,
                'editor_id' => Auth::id(),
                'revision_number' => $maxRev + 1,
                'title' => $document->title,
                'content_markdown' => $document->content_markdown,
                'change_summary' => "Dipulihkan dari Revisi #{$revision->revision_number}",
                'created_at' => now(),
            ]);
        });

        return redirect()->route('documents.show', ['project' => $project->slug, 'document' => $document->slug])
            ->with('success', "Dokumen berhasil dipulihkan ke Revisi #{$revision->revision_number}!");
    }

    public function export(Request $request, Project $project, Document $document, string $format = 'html'): View
    {
        if (! $project->canView($request->user())) {
            abort(403, 'Anda tidak memiliki akses untuk mengekspor dokumen ini.');
        }

        $document->load(['author', 'apiEndpoints', 'folder']);

        return view('documents.export', compact('project', 'document', 'format'));
    }

    public function destroy(Request $request, Project $project, Document $document): RedirectResponse
    {
        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus dokumen pada proyek ini.');
        }

        $title = $document->title;
        $document->delete(); // Soft delete

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'document.deleted',
            Auth::user()->name." menghapus dokumen '{$title}'"
        );

        return redirect()->route('projects.show', $project->slug)
            ->with('success', "Dokumen '{$title}' berhasil dihapus.");
    }
}
