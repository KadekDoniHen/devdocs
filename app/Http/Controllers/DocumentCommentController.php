<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentCommentController extends Controller
{
    /**
     * Store a new team discussion comment on a document.
     */
    public function store(Request $request, Project $project, Document $document): RedirectResponse
    {
        if (! $project->canView($request->user())) {
            abort(403, 'Anda tidak memiliki akses untuk mengomentari dokumen ini.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        DocumentComment::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'comment.added',
            Auth::user()->name." mengomentari dokumen '{$document->title}'"
        );

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    /**
     * Delete a comment (author or project manager/owner/admin).
     */
    public function destroy(Request $request, Project $project, Document $document, DocumentComment $comment): RedirectResponse
    {
        if ($comment->document_id !== $document->id) {
            abort(404);
        }

        if ($comment->user_id !== $request->user()->id && ! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus komentar ini.');
        }

        $comment->delete();

        return back()->with('success', 'Komentar berhasil dihapus.');
    }
}
