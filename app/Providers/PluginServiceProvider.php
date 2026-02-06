<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PluginLoader;
use App\Services\PluginManager;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PluginLoader::class, function ($app) {
            return new PluginLoader();
        });

        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager($app->make(\App\Services\PermissionRegistry::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(PluginLoader $loader): void
    {
        // Load active plugins
        $loader->boot();

        // Register catch-all route for pages AFTER ALL service providers have booted
        // This ensures plugin routes like /events or /posts take precedence
        $this->app->booted(function () {
            Route::middleware('web')->group(function () {
                Route::get('/{slug}', [PageController::class, 'show'])
                    ->where('slug', '(?!' . preg_quote(config('admin.path', 'admin'), '/') . ')[a-zA-Z0-9\-]+')
                    ->name('pages.show');
            });
        });
    }
}
