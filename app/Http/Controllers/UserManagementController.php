<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display the team user management page (Admin only).
     */
    public function index(): View
    {
        $users = User::query()
            ->withCount(['projects', 'documents'])
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    /**
     * Create a new team user account (Admin only).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'editor', 'member'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        ActivityLog::record(null, Auth::id(), 'user.created', "Admin menambahkan user {$user->name} ({$user->role})");

        return back()->with('success', "User '{$user->name}' berhasil ditambahkan.");
    }

    /**
     * Update an existing user's details, role, or password (Admin only).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'editor', 'member'])],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Anda tidak dapat menurunkan role akun Anda sendiri.']);
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        ActivityLog::record(null, Auth::id(), 'user.updated', "Admin memperbarui akun {$user->name} ({$user->role})");

        return back()->with('success', "Data user '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Delete a team user account (Admin only).
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record(null, Auth::id(), 'user.deleted', "Admin menghapus akun {$name}");

        return back()->with('success', "User '{$name}' berhasil dihapus.");
    }
}
