<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class ThemeLoader
{
    protected ?Theme $activeTheme = null;

    public function boot(): void
    {
        // Check if themes table exists to avoid errors during initial migration
        if (!Schema::hasTable('themes')) {
            return;
        }

        try {
            $this->activeTheme = Theme::active()->first();

            if (!$this->activeTheme) {
                Log::warning("No active theme found.");
                return;
            }

            // Register theme view paths
            $this->registerViewPaths($this->activeTheme);

            // Share theme data with all views
            $this->shareThemeData($this->activeTheme);

        } catch (\Exception $e) {
            // Log error but don't crash the app if something goes wrong with theme loading
            Log::error("Failed to load theme: " . $e->getMessage());
        }
    }

    /**
     * Register theme view paths with priority.
     */
    protected function registerViewPaths(Theme $theme): void
    {
        $themePath = base_path("themes/{$theme->slug}/views");

        if (!is_dir($themePath)) {
            Log::warning("Theme views directory not found: {$themePath}");
            return;
        }

        // Add theme views path with highest priority
        // Laravel view finder will check theme views first, then fall back to default paths
        $finder = View::getFinder();
        $paths = $finder->getPaths();

        // Prepend theme path to the beginning
        array_unshift($paths, $themePath);
        $finder->setPaths($paths);

        // Register theme namespace for direct access (e.g., themes::default.layouts.main)
        View::addNamespace('themes', base_path('themes'));

        Log::info("Theme views registered: {$themePath}");
    }

    /**
     * Share theme data with all views.
     */
    protected function shareThemeData(Theme $theme): void
    {
        // Share active theme object
        View::share('activeTheme', $theme);

        // Share theme configuration if exists
        $configPath = base_path("themes/{$theme->slug}/config");
        if (is_dir($configPath)) {
            $config = $this->loadThemeConfig($theme->slug);
            View::share('themeConfig', $config);
        }
    }

    /**
     * Load theme configuration files.
     */
    protected function loadThemeConfig(string $slug): array
    {
        $config = [];
        $configPath = base_path("themes/{$slug}/config");

        if (!is_dir($configPath)) {
            return $config;
        }

        $files = glob($configPath . '/*.php');

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $config[$key] = require $file;
        }

        return $config;
    }

    /**
     * Get the currently active theme.
     */
    public function getActiveTheme(): ?Theme
    {
        return $this->activeTheme;
    }

    /**
     * Get theme asset path.
     */
    public function getAssetPath(string $path): string
    {
        if (!$this->activeTheme) {
            return '';
        }

        return "themes/{$this->activeTheme->slug}/assets/{$path}";
    }
}
