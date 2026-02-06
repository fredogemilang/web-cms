<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class PluginLoader
{
    public function boot(): void
    {
        // Check if plugins table exists to avoid errors during initial migration
        if (!Schema::hasTable('plugins')) {
            return;
        }

        try {
            $plugins = Plugin::active()->get();
            $loader = require base_path('vendor/autoload.php');

            foreach ($plugins as $plugin) {
                // Register Plugin Namespace
                // Assuming namespace is Plugins\{Slug}\ and path is plugins/{slug}/src
                // We can also read this from plugin.json if we want to be more flexible
                
                $pluginPath = base_path("plugins/{$plugin->slug}");
                // Convert slug to PascalCase for namespace (e.g., article-submission -> ArticleSubmission)
                $namespaceSlug = str_replace(' ', '', ucwords(str_replace('-', ' ', $plugin->slug)));
                $namespace = "Plugins\\" . $namespaceSlug . "\\";
                
                // Check if src exists, otherwise map to root
                $sourcePath = file_exists($pluginPath . '/src') ? $pluginPath . '/src' : $pluginPath;
                
                Log::info("Registering plugin namespace: {$namespace} -> {$sourcePath}");
                $loader->addPsr4($namespace, $sourcePath);

                // Register Provider
                if (class_exists($plugin->provider)) {
                    app()->register($plugin->provider);
                    
                    // Validate plugin routes after registration
                    $this->validatePluginRoutes($plugin);
                } else {
                    Log::warning("Plugin provider not found: {$plugin->provider} for plugin {$plugin->slug}");
                }
            }
        } catch (\Exception $e) {
            // Log error but don't crash the app if something goes wrong with plugins
            Log::error("Failed to load plugins: " . $e->getMessage());
        }
    }

    /**
     * Validate that plugin routes include 'web' middleware
     * 
     * @param \App\Models\Plugin $plugin
     * @return void
     */
    protected function validatePluginRoutes($plugin): void
    {
        try {
            $routes = \Illuminate\Support\Facades\Route::getRoutes();
            $adminPath = config('admin.path', 'admin');
            $pluginPrefix = $plugin->slug;
            
            // Find routes that match this plugin
            $pluginRoutes = collect($routes)->filter(function ($route) use ($adminPath, $pluginPrefix) {
                $uri = $route->uri();
                // Check if route URI matches admin path + plugin prefix
                return str_starts_with($uri, "{$adminPath}/{$pluginPrefix}");
            });
            
            if ($pluginRoutes->isEmpty()) {
                return; // No routes to validate
            }
            
            // Check if any route is missing 'web' middleware
            $routesWithoutWeb = $pluginRoutes->filter(function ($route) {
                $middleware = $route->middleware();
                return !in_array('web', $middleware);
            });
            
            if ($routesWithoutWeb->isNotEmpty()) {
                Log::warning("Plugin '{$plugin->name}' ({$plugin->slug}) has routes without 'web' middleware!", [
                    'plugin' => $plugin->slug,
                    'routes_count' => $routesWithoutWeb->count(),
                    'sample_routes' => $routesWithoutWeb->take(3)->map(fn($r) => $r->uri())->values()->toArray(),
                    'recommendation' => "Add 'web' middleware to plugin routes to prevent 302 redirects. See docs/plugin-development.md for details.",
                ]);
            }
        } catch (\Exception $e) {
            // Don't crash if validation fails
            Log::debug("Failed to validate routes for plugin {$plugin->slug}: " . $e->getMessage());
        }
    }
}
