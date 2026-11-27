<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim($request->input('q', ''));
        $user = $request->user();

        $projects = collect();
        $documents = collect();

        if ($query !== '') {
            $likeOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

            $projects = Project::accessibleBy($user)
                ->where('status', 'active')
                ->where(function ($q) use ($query, $likeOperator): void {
                    $q->where('name', $likeOperator, "%{$query}%")
                        ->orWhere('description', $likeOperator, "%{$query}%");
                })
                ->withCount('documents')
                ->take(5)
                ->get();

            $documents = Document::query()
                ->whereHas('project', fn ($q) => $q->accessibleBy($user))
                ->where(function ($q) use ($query, $likeOperator): void {
                    $q->where('title', $likeOperator, "%{$query}%")
                        ->orWhere('summary', $likeOperator, "%{$query}%")
                        ->orWhere('content_markdown', $likeOperator, "%{$query}%");
                })
                ->with(['project', 'author', 'folder'])
                ->latest('updated_at')
                ->take(20)
                ->get();
        }

        return view('search.index', compact('query', 'projects', 'documents'));
    }
}
