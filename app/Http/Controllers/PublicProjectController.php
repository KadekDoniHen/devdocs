<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use Illuminate\View\View;

class PublicProjectController extends Controller
{
    /**
     * Display a publicly shared project overview (read-only).
     */
    public function show(string $token): View
    {
        $project = $this->findPublicProject($token);

        $this->loadPublicNavigation($project);

        $documents = $project->documents()
            ->where('status', 'published')
            ->with(['folder', 'author'])
            ->latest('updated_at')
            ->get();

        return view('public.project', compact('project', 'documents'));
    }

    /**
     * Display a single published document inside a publicly shared project (read-only).
     */
    public function showDocument(string $token, string $documentSlug): View
    {
        $project = $this->findPublicProject($token);

        $document = $project->documents()
            ->where('slug', $documentSlug)
            ->where('status', 'published')
            ->with(['author', 'lastEditor', 'folder', 'apiEndpoints'])
            ->firstOrFail();

        $this->loadPublicNavigation($project);

        return view('public.document', compact('project', 'document'));
    }

    /**
     * Resolve a publicly shared project by its share token.
     */
    protected function findPublicProject(string $token): Project
    {
        return Project::query()
            ->where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();
    }

    /**
     * Load folder tree and published documents for public navigation.
     */
    protected function loadPublicNavigation(Project $project): void
    {
        $publishedFilter = function ($query): void {
            $query->where('status', 'published')->orderBy('title');
        };

        $project->load([
            'rootDocuments' => $publishedFilter,
            'rootFolders.documents' => $publishedFilter,
            'rootFolders.children.documents' => $publishedFilter,
        ]);
    }
}
