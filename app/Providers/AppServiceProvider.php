<?php

namespace App\Providers;

use App\Http\Controllers\Auth\AdminLogoutController;
use Filament\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Filament's own logout controller redirects to /admin/login by default.
        // We swap it for our own so logging out of the admin panel lands on our
        // single shared /login page instead.
        $this->app->bind(LogoutController::class, AdminLogoutController::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
