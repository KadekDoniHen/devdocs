<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

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
    Route::delete('/projects/{project:slug}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project:slug}/folders', [ProjectController::class, 'storeFolder'])->name('projects.folders.store');

    // Documents
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');

    // Project-scoped nested resources (prevents cross-project slug/ID access)
    Route::scopeBindings()->group(function () {
        Route::delete('/projects/{project:slug}/folders/{folder}', [ProjectController::class, 'destroyFolder'])->name('projects.folders.destroy');

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
});
