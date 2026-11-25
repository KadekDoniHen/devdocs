<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $projects = Project::where('status', 'active')
            ->withCount(['documents', 'folders'])
            ->latest('updated_at')
            ->get();

        $recentDocuments = Document::with(['project', 'author'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        $stats = [
            'total_projects' => Project::count(),
            'total_documents' => Document::count(),
            'published_documents' => Document::where('status', 'published')->count(),
            'draft_documents' => Document::where('status', 'draft')->count(),
        ];

        return view('dashboard.index', compact('projects', 'recentDocuments', 'stats'));
    }
}
