<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\ThemeLoader;
use App\Services\ThemeManager;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ThemeLoader::class, function ($app) {
            return new ThemeLoader();
        });

        $this->app->singleton(ThemeManager::class, function ($app) {
            return new ThemeManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(ThemeLoader $loader): void
    {
        // Load active theme
        $loader->boot();

        // Register Blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Register custom Blade directives for theme system.
     */
    protected function registerBladeDirectives(): void
    {
        // @themeAsset('css/style.css') - Get theme asset path
        Blade::directive('themeAsset', function ($expression) {
            return "<?php echo theme_asset({$expression}); ?>";
        });

        // @activeTheme - Access active theme object (already shared via View::share)
        // Usage: @activeTheme->name
    }
}
