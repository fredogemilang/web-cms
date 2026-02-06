<?php

if (!function_exists('admin_path')) {
    /**
     * Get the admin path from config.
     *
     * @param string|null $path
     * @return string
     */
    function admin_path(?string $path = null): string
    {
        $adminPath = config('admin.path', 'admin');
        
        if ($path) {
            return '/' . trim($adminPath, '/') . '/' . ltrim($path, '/');
        }
        
        return '/' . trim($adminPath, '/');
    }
}

if (!function_exists('admin_url')) {
    /**
     * Generate a URL to an admin path.
     *
     * @param string|null $path
     * @param mixed $parameters
     * @param bool|null $secure
     * @return string
     */
    function admin_url(?string $path = null, $parameters = [], ?bool $secure = null): string
    {
        return url(admin_path($path), $parameters, $secure);
    }
}

if (!function_exists('active_theme')) {
    /**
     * Get the currently active theme.
     *
     * @return \App\Models\Theme|null
     */
    function active_theme(): ?\App\Models\Theme
    {
        return app(\App\Services\ThemeLoader::class)->getActiveTheme();
    }
}

if (!function_exists('theme_asset')) {
    /**
     * Get the URL to a theme asset.
     *
     * @param string $path Path relative to theme assets directory
     * @return string
     */
    function theme_asset(string $path): string
    {
        $theme = active_theme();

        if (!$theme) {
            return '';
        }

        // Use Vite::asset if available, otherwise use asset()
        if (class_exists(\Illuminate\Support\Facades\Vite::class)) {
            try {
                return \Illuminate\Support\Facades\Vite::asset("themes/{$theme->slug}/assets/{$path}");
            } catch (\Exception $e) {
                // Fall back to regular asset if Vite manifest not found
                return asset("themes/{$theme->slug}/assets/{$path}");
            }
        }

        return asset("themes/{$theme->slug}/assets/{$path}");
    }
}

if (!function_exists('theme_view')) {
    /**
     * Render a theme view.
     *
     * @param string $view View name
     * @param array $data Data to pass to view
     * @return \Illuminate\Contracts\View\View
     */
    function theme_view(string $view, array $data = []): \Illuminate\Contracts\View\View
    {
        $theme = active_theme();

        if (!$theme) {
            return view($view, $data);
        }

        $themeView = "themes::{$theme->slug}.{$view}";

        if (view()->exists($themeView)) {
            return view($themeView, $data);
        }

        return view($view, $data);
    }
}

if (!function_exists('theme_path')) {
    /**
     * Get the full path to the active theme directory.
     *
     * @param string|null $path Optional path within theme directory
     * @return string
     */
    function theme_path(?string $path = null): string
    {
        $theme = active_theme();

        if (!$theme) {
            return '';
        }

        $basePath = base_path("themes/{$theme->slug}");

        if ($path) {
            return $basePath . '/' . ltrim($path, '/');
        }

        return $basePath;
    }
}
