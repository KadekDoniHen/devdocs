<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProjectMemberController extends Controller
{
    /**
     * Share a project with another team user with a specific permission.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        if (! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola anggota proyek ini.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permission' => ['required', Rule::in(['viewer', 'editor', 'manager'])],
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        if ($targetUser->id === $project->owner_id) {
            return back()->withErrors(['user_id' => 'Pemilik proyek sudah memiliki akses penuh secara otomatis.']);
        }

        $project->members()->syncWithoutDetaching([
            $targetUser->id => ['permission' => $validated['permission']],
        ]);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'member.added',
            Auth::user()->name." membagikan proyek ke {$targetUser->name} (".strtoupper($validated['permission']).')'
        );

        return back()->with('success', "Proyek berhasil dibagikan ke {$targetUser->name}.");
    }

    /**
     * Update an existing member's permission on a project.
     */
    public function update(Request $request, Project $project, User $user): RedirectResponse
    {
        if (! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah akses anggota proyek ini.');
        }

        $validated = $request->validate([
            'permission' => ['required', Rule::in(['viewer', 'editor', 'manager'])],
        ]);

        $project->members()->updateExistingPivot($user->id, [
            'permission' => $validated['permission'],
        ]);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'member.updated',
            Auth::user()->name." mengubah izin {$user->name} menjadi ".strtoupper($validated['permission'])
        );

        return back()->with('success', "Izin akses {$user->name} berhasil diperbarui.");
    }

    /**
     * Revoke a team member's access from a project.
     */
    public function destroy(Request $request, Project $project, User $user): RedirectResponse
    {
        if (! $project->canManage($request->user())) {
            abort(403, 'Anda tidak memiliki izin untuk mencabut akses anggota proyek ini.');
        }

        $project->members()->detach($user->id);

        ActivityLog::record(
            $project->id,
            Auth::id(),
            'member.removed',
            Auth::user()->name." mencabut akses {$user->name} dari proyek"
        );

        return back()->with('success', "Akses {$user->name} dari proyek berhasil dicabut.");
    }
}
