<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentCommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\PublicProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// Public Read-Only Shared Project Routes (No Login Required)
Route::prefix('share/{token}')->name('public.projects.')->group(function () {
    Route::get('/', [PublicProjectController::class, 'show'])->name('show');
    Route::get('/docs/{documentSlug}', [PublicProjectController::class, 'showDocument'])->name('document');
});

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project:slug}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project:slug}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{project:slug}/share/toggle', [ProjectController::class, 'toggleShare'])->name('projects.share.toggle');
    Route::post('/projects/{project:slug}/share/regenerate', [ProjectController::class, 'regenerateShareToken'])->name('projects.share.regenerate');
    Route::post('/projects/{project:slug}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::put('/projects/{project:slug}/members/{user}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
    Route::delete('/projects/{project:slug}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');
    Route::delete('/projects/{project:slug}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project:slug}/folders', [ProjectController::class, 'storeFolder'])->name('projects.folders.store');
    Route::get('/projects/{project:slug}/folders/{folder}', function (\App\Models\Project $project) {
        return redirect()->route('projects.show', $project->slug);
    });
    Route::delete('/projects/{project:slug}/folders/{folder}', [ProjectController::class, 'destroyFolder'])->name('projects.folders.destroy');

    // Documents
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');

    // Project-scoped nested resources (prevents cross-project slug/ID access)
    Route::scopeBindings()->group(function () {
        Route::get('/projects/{project:slug}/docs/{document:slug}', [DocumentController::class, 'show'])->name('documents.show');
        Route::get('/projects/{project:slug}/docs/{document:slug}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
        Route::put('/projects/{project:slug}/docs/{document:slug}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/projects/{project:slug}/docs/{document:slug}', [DocumentController::class, 'destroy'])->name('documents.destroy');
        Route::patch('/projects/{project:slug}/docs/{document:slug}/move', [DocumentController::class, 'move'])->name('documents.move');
        Route::post('/projects/{project:slug}/docs/{document:slug}/autosave', [DocumentController::class, 'autosave'])->name('documents.autosave');
        Route::post('/projects/{project:slug}/docs/{document:slug}/publish', [DocumentController::class, 'publish'])->name('documents.publish');
        Route::get('/projects/{project:slug}/docs/{document:slug}/revisions', [DocumentController::class, 'revisions'])->name('documents.revisions');
        Route::post('/projects/{project:slug}/docs/{document:slug}/restore/{revision}', [DocumentController::class, 'restoreRevision'])->name('documents.restore');
        Route::get('/projects/{project:slug}/docs/{document:slug}/export/{format?}', [DocumentController::class, 'export'])->name('documents.export');
        Route::post('/projects/{project:slug}/docs/{document:slug}/comments', [DocumentCommentController::class, 'store'])->name('documents.comments.store');
        Route::delete('/projects/{project:slug}/docs/{document:slug}/comments/{comment}', [DocumentCommentController::class, 'destroy'])->name('documents.comments.destroy');
    });

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // AI Assistant (Gemini)
    Route::get('/ai/status', [AiAssistantController::class, 'status'])->name('ai.status');
    Route::post('/ai/refine', [AiAssistantController::class, 'refine'])->name('ai.refine');
    Route::post('/ai/generate', [AiAssistantController::class, 'generate'])->name('ai.generate');

    // Account Settings
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    // Admin-only Team User Management
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});
