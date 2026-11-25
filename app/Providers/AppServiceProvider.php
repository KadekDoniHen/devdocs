<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $sidebarProjects = collect();
            if (Auth::check() && Schema::hasTable('projects')) {
                $sidebarProjects = Project::query()
                    ->select(['id', 'name', 'slug', 'status'])
                    ->withCount('documents')
                    ->orderBy('name')
                    ->take(25)
                    ->get();
            }
            $view->with('sidebarProjects', $sidebarProjects);
        });
    }
}
