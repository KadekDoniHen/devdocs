<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::withCount(['documents', 'folders'])
            ->latest('updated_at')
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
        ]);

        $slug = Str::slug($validated['name']);
        // Ensure slug uniqueness
        $originalSlug = $slug;
        $counter = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $project = Project::create([
            'owner_id' => Auth::id(),
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'folder',
            'status' => 'active',
        ]);

        return redirect()->route('projects.show', $project->slug)
            ->with('success', "Proyek '{$project->name}' berhasil dibuat!");
    }

    public function show(Project $project): View
    {
        $project->load([
            'rootFolders.children.documents',
            'rootFolders.documents',
            'rootDocuments',
            'folders',
        ]);

        $documents = $project->documents()
            ->with(['folder', 'author', 'lastEditor'])
            ->latest('updated_at')
            ->get();

        return view('projects.show', compact('project', 'documents'));
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,archived'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project->slug)
            ->with('success', 'Detail proyek berhasil diperbarui.');
    }

    /**
     * Enable or disable public read-only sharing for a project.
     */
    public function toggleShare(Project $project): RedirectResponse
    {
        $willBePublic = ! $project->is_public;

        $updateData = [
            'is_public' => $willBePublic,
        ];

        if ($willBePublic && empty($project->share_token)) {
            $updateData['share_token'] = Project::generateShareToken();
        }

        $project->update($updateData);

        $message = $willBePublic
            ? 'Akses publik (hanya lihat) berhasil diaktifkan.'
            : 'Akses publik berhasil dinonaktifkan. Proyek kini kembali privat.';

        return back()->with('success', $message);
    }

    /**
     * Regenerate the public share token to revoke any previously shared link.
     */
    public function regenerateShareToken(Project $project): RedirectResponse
    {
        $project->update([
            'is_public' => true,
            'share_token' => Project::generateShareToken(),
        ]);

        return back()->with('success', 'Link publik baru berhasil dibuat. Link lama tidak lagi berlaku.');
    }

    public function storeFolder(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'parent_id' => ['nullable', 'exists:folders,id'],
        ]);

        // Position at bottom
        $maxPos = Folder::where('project_id', $project->id)
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->max('position') ?? 0;

        Folder::create([
            'project_id' => $project->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'position' => $maxPos + 1,
        ]);

        return redirect()->route('projects.show', $project->slug)
            ->with('success', "Folder '{$validated['name']}' berhasil dibuat.");
    }

    public function destroyFolder(Project $project, Folder $folder): RedirectResponse
    {
        if ($folder->project_id !== $project->id) {
            abort(404);
        }

        $name = $folder->name;
        $folder->delete();

        return redirect()->route('projects.show', $project->slug)
            ->with('success', "Folder '{$name}' berhasil dihapus.");
    }

    public function destroy(Project $project): RedirectResponse
    {
        $name = $project->name;

        DB::transaction(function () use ($project) {
            foreach ($project->documents()->withTrashed()->get() as $doc) {
                foreach ($doc->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment->storage_path)) {
                        Storage::disk('public')->delete($attachment->storage_path);
                    }
                }
            }
            $project->delete();
        });

        return redirect()->route('projects.index')
            ->with('success', "Proyek '{$name}' berhasil dihapus.");
    }
}
