<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim($request->input('q', ''));

        $projects = collect();
        $documents = collect();

        if ($query !== '') {
            $projects = Project::where('status', 'active')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', "%{$query}%")
                      ->orWhere('description', 'ILIKE', "%{$query}%");
                })
                ->withCount('documents')
                ->take(5)
                ->get();

            $documents = Document::where(function ($q) use ($query) {
                $q->where('title', 'ILIKE', "%{$query}%")
                  ->orWhere('summary', 'ILIKE', "%{$query}%")
                  ->orWhere('content_markdown', 'ILIKE', "%{$query}%");
            })
            ->with(['project', 'author', 'folder'])
            ->latest('updated_at')
            ->take(20)
            ->get();
        }

        return view('search.index', compact('query', 'projects', 'documents'));
    }
}
