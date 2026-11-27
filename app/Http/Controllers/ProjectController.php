<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::accessibleBy($request->user())
            ->with(['owner', 'members'])
            ->withCount(['documents', 'folders'])
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

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'project.created',
            Auth::user()->name." membuat proyek '{$project->name}'"
        );

        return redirect()->route('projects.show', $project->slug)
            ->with('success', "Proyek '{$project->name}' berhasil dibuat!");
    }

    public function show(Request $request, Project $project): View
    {
        if (! $project->canView($request->user())) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }

        $project->load([
            'owner',
            'members',
            'rootFolders.children.documents',
            'rootFolders.documents',
            'rootDocuments',
            'folders',
        ]);

        $documents = $project->documents()
            ->with(['folder', 'author', 'lastEditor'])
            ->latest('updated_at')
            ->get();

        $availableUsers = User::query()
            ->where('id', '!=', $project->owner_id)
            ->whereNotIn('id', $project->members->pluck('id'))
            ->orderBy('name')
            ->get();

        $activityLogs = $project->activityLogs()
            ->with('user')
            ->take(8)
            ->get();

        return view('projects.show', compact('project', 'documents', 'availableUsers', 'activityLogs'));
    }

    public function edit(Request $request, Project $project): View
    {
        if (! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah pengaturan proyek ini.');
        }

        $project->load(['owner', 'members']);

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        if (! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk memperbarui proyek ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,archived'],
        ]);

        $project->update($validated);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'project.updated',
            Auth::user()->name." memperbarui detail proyek '{$project->name}'"
        );

        return redirect()->route('projects.show', $project->slug)
            ->with('success', 'Detail proyek berhasil diperbarui.');
    }

    /**
     * Enable or disable public read-only sharing for a project.
     */
    public function toggleShare(Request $request, Project $project): RedirectResponse
    {
        if (! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mengatur akses publik proyek ini.');
        }

        $willBePublic = ! $project->is_public;

        $updateData = [
            'is_public' => $willBePublic,
        ];

        if ($willBePublic && empty($project->share_token)) {
            $updateData['share_token'] = Project::generateShareToken();
        }

        $project->update($updateData);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            $willBePublic ? 'project.public_enabled' : 'project.public_disabled',
            Auth::user()->name.($willBePublic ? ' mengaktifkan akses publik proyek' : ' menonaktifkan akses publik proyek')
        );

        $message = $willBePublic
            ? 'Akses publik (hanya lihat) berhasil diaktifkan.'
            : 'Akses publik berhasil dinonaktifkan. Proyek kini kembali privat.';

        return back()->with('success', $message);
    }

    /**
     * Regenerate the public share token to revoke any previously shared link.
     */
    public function regenerateShareToken(Request $request, Project $project): RedirectResponse
    {
        if (! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mereset link publik proyek ini.');
        }

        $project->update([
            'is_public' => true,
            'share_token' => Project::generateShareToken(),
        ]);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'project.public_regenerated',
            Auth::user()->name.' mereset link publik proyek'
        );

        return back()->with('success', 'Link publik baru berhasil dibuat. Link lama tidak lagi berlaku.');
    }

    public function storeFolder(Request $request, Project $project): RedirectResponse
    {
        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk menambah folder pada proyek ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'parent_id' => ['nullable', 'exists:folders,id'],
        ]);

        $maxPos = Folder::where('project_id', $project->id)
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->max('position') ?? 0;

        Folder::create([
            'project_id' => $project->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'position' => $maxPos + 1,
        ]);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'folder.created',
            Auth::user()->name." menambahkan folder '{$validated['name']}'"
        );

        return redirect()->route('projects.show', $project->slug)
            ->with('success', "Folder '{$validated['name']}' berhasil dibuat.");
    }

    public function destroyFolder(Request $request, Project $project, int $folder): RedirectResponse
    {
        if (! $project->canEdit($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus folder pada proyek ini.');
        }

        $folderModel = Folder::where('project_id', $project->id)->find($folder);

        if (! $folderModel) {
            return redirect()->route('projects.show', $project->slug)
                ->with('success', 'Folder sudah dihapus.');
        }

        $name = $folderModel->name;

        DB::transaction(function () use ($project, $folderModel): void {
            $childFolderIds = Folder::where('project_id', $project->id)
                ->where('parent_id', $folderModel->id)
                ->pluck('id')
                ->all();

            $affectedFolderIds = array_merge([$folderModel->id], $childFolderIds);

            Document::where('project_id', $project->id)
                ->whereIn('folder_id', $affectedFolderIds)
                ->update(['folder_id' => null]);

            if (! empty($childFolderIds)) {
                Folder::whereIn('id', $childFolderIds)->delete();
            }

            $folderModel->delete();
        });

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'folder.deleted',
            Auth::user()->name." menghapus folder '{$name}'"
        );

        return redirect()->route('projects.show', $project->slug)
            ->with('success', "Folder '{$name}' berhasil dihapus.");
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        if (! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus proyek ini.');
        }

        $name = $project->name;

        DB::transaction(function () use ($project): void {
            foreach ($project->documents()->withTrashed()->get() as $doc) {
                foreach ($doc->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment->storage_path)) {
                        Storage::disk('public')->delete($attachment->storage_path);
                    }
                }
            }
            $project->delete();
        });

        ActivityLog::record(null, Auth::id(), 'project.deleted', Auth::user()->name." menghapus proyek '{$name}'");

        return redirect()->route('projects.index')
            ->with('success', "Proyek '{$name}' berhasil dihapus.");
    }
}
