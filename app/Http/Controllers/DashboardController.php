<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $projects = Project::accessibleBy($user)
            ->where('status', 'active')
            ->with(['owner', 'members'])
            ->withCount(['documents', 'folders'])
            ->latest('updated_at')
            ->get();

        $recentDocuments = Document::query()
            ->whereHas('project', fn ($q) => $q->accessibleBy($user))
            ->with(['project.members', 'author'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        $accessibleDocsQuery = Document::query()
            ->whereHas('project', fn ($q) => $q->accessibleBy($user));

        $stats = [
            'total_projects' => Project::accessibleBy($user)->count(),
            'total_documents' => (clone $accessibleDocsQuery)->count(),
            'published_documents' => (clone $accessibleDocsQuery)->where('status', 'published')->count(),
            'draft_documents' => (clone $accessibleDocsQuery)->where('status', 'draft')->count(),
        ];

        $recentActivities = ActivityLog::query()
            ->with(['user', 'project'])
            ->where(function ($q) use ($user): void {
                $q->whereHas('project', fn ($p) => $p->accessibleBy($user));
                if ($user->isAdmin()) {
                    $q->orWhereNull('project_id');
                }
            })
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.index', compact('projects', 'recentDocuments', 'stats', 'recentActivities'));
    }
}
