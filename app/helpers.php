<?php

use App\Models\Setting;
use App\Models\Theme;
use App\Services\ActivityLogger;
use App\Services\ThemeLoader;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;

if (! function_exists('activity')) {
    /**
     * Get the central audit log writer.
     *
     * Usage:
     *   activity()->log('page.created', $page, "Created page '{$page->title}'");
     */
    function activity(): ActivityLogger
    {
        return app(ActivityLogger::class);
    }
}

if (! function_exists('translate')) {
    /**
     * Get a translated field value from a model for the current locale.
     * Falls back to the model's default-locale value if the translation is missing.
     */
    function translate(Model $model, string $field, ?string $locale = null): mixed
    {
        if (method_exists($model, 'getTranslation')) {
            return $model->getTranslation($field, $locale);
        }

        return $model->getAttribute($field);
    }
}

if (! function_exists('available_locales')) {
    /**
     * Return the list of locale codes the site supports.
     *
     * @return array<int, string>
     */
    function available_locales(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) Setting::get('available_locales', 'id,en')))));
    }
}

if (! function_exists('setting')) {
    /**
     * Get a CMS setting value (cache-backed).
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('admin_path')) {
    /**
     * Get the admin path from config.
     */
    function admin_path(?string $path = null): string
    {
        $adminPath = config('admin.path', 'admin');

        if ($path) {
            return '/'.trim($adminPath, '/').'/'.ltrim($path, '/');
        }

        return '/'.trim($adminPath, '/');
    }
}

if (! function_exists('admin_url')) {
    /**
     * Generate a URL to an admin path.
     *
     * @param  mixed  $parameters
     */
    function admin_url(?string $path = null, $parameters = [], ?bool $secure = null): string
    {
        return url(admin_path($path), $parameters, $secure);
    }
}

if (! function_exists('active_theme')) {
    /**
     * Get the currently active theme.
     */
    function active_theme(): ?Theme
    {
        return app(ThemeLoader::class)->getActiveTheme();
    }
}

if (! function_exists('theme_asset')) {
    /**
     * Get the URL to a theme asset.
     *
     * @param  string  $path  Path relative to theme assets directory
     */
    function theme_asset(string $path): string
    {
        $theme = active_theme();

        if (! $theme) {
            return '';
        }

        // Use Vite::asset if available, otherwise use asset()
        if (class_exists(Vite::class)) {
            try {
                return Vite::asset("themes/{$theme->slug}/assets/{$path}");
            } catch (Exception $e) {
                // Fall back to regular asset if Vite manifest not found
                return asset("themes/{$theme->slug}/assets/{$path}");
            }
        }

        return asset("themes/{$theme->slug}/assets/{$path}");
    }
}

if (! function_exists('theme_view')) {
    /**
     * Render a theme view.
     *
     * @param  string  $view  View name
     * @param  array  $data  Data to pass to view
     */
    function theme_view(string $view, array $data = []): View
    {
        $theme = active_theme();

        if (! $theme) {
            return view($view, $data);
        }

        $themeView = "themes::{$theme->slug}.{$view}";

        if (view()->exists($themeView)) {
            return view($themeView, $data);
        }

        return view($view, $data);
    }
}

if (! function_exists('theme_path')) {
    /**
     * Get the full path to the active theme directory.
     *
     * @param  string|null  $path  Optional path within theme directory
     */
    function theme_path(?string $path = null): string
    {
        $theme = active_theme();

        if (! $theme) {
            return '';
        }

        $basePath = base_path("themes/{$theme->slug}");

        if ($path) {
            return $basePath.'/'.ltrim($path, '/');
        }

        return $basePath;
    }
}
