<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::createAssetPathsUsing(
            static fn (string $path): string => request()->getBasePath().'/'.$path
        );
        Paginator::useBootstrapFive();
        Gate::define('isAdmin', static fn (User $user): bool => $user->isAdmin());
    }
}
