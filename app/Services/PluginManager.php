<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;
use Exception;

class PluginManager
{
    protected string $pluginPath;
    protected PermissionRegistry $permissionRegistry;

    public function __construct(PermissionRegistry $permissionRegistry)
    {
        $this->pluginPath = base_path('plugins');
        $this->permissionRegistry = $permissionRegistry;

        if (!File::exists($this->pluginPath)) {
            File::makeDirectory($this->pluginPath, 0755, true);
        }
    }

    /**
     * Install a plugin from a zip file.
     */
    public function install(string $zipPath): Plugin
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception("The PHP Zip extension is not enabled. Please enable it in your php.ini configuration.");
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new Exception("Failed to open zip file.");
        }

        // Extract to temporary location to read manifest
        $tempPath = storage_path('app/temp/plugins/' . uniqid());
        $zip->extractTo($tempPath);
        $zip->close();

        // Find plugin.json
        $files = File::allFiles($tempPath);
        $manifestFile = null;
        foreach ($files as $file) {
            if ($file->getFilename() === 'plugin.json') {
                $manifestFile = $file;
                break;
            }
        }

        if (!$manifestFile) {
            File::deleteDirectory($tempPath);
            throw new Exception("plugin.json not found in the zip file.");
        }

        $manifest = json_decode(file_get_contents($manifestFile->getPathname()), true);
        if (!$manifest || !isset($manifest['name'], $manifest['slug'], $manifest['provider'])) {
            File::deleteDirectory($tempPath);
            throw new Exception("Invalid plugin.json manifest.");
        }

        $slug = $manifest['slug'];
        $targetPath = $this->pluginPath . '/' . $slug;

        if (File::exists($targetPath)) {
            File::deleteDirectory($tempPath);
            throw new Exception("Plugin with slug '{$slug}' already exists.");
        }

        // Move from temp to target
        // We need to move the directory containing plugin.json to targetPath
        // If plugin.json is in root of zip, move tempPath to targetPath
        // If it's in a subdirectory, move that subdirectory.
        
        $sourceDir = $manifestFile->getPath();
        File::moveDirectory($sourceDir, $targetPath);
        File::deleteDirectory($tempPath);

        // Create DB record
        return Plugin::create([
            'name' => $manifest['name'],
            'slug' => $slug,
            'version' => $manifest['version'] ?? '1.0.0',
            'description' => $manifest['description'] ?? null,
            'author' => $manifest['author'] ?? null,
            'provider' => $manifest['provider'],
            'installed_at' => now(),
        ]);
    }

    /**
     * Activate a plugin.
     */
    public function activate(Plugin $plugin): void
    {
        if ($plugin->is_active) {
            return;
        }

        $manifestPath = $this->pluginPath . '/' . $plugin->slug . '/plugin.json';
        if (!File::exists($manifestPath)) {
            throw new Exception("Plugin files not found.");
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        // Validate dependencies (requires php, cms) - PRD Section 10
        if (isset($manifest['requires'])) {
            $this->validateDependencies($manifest['requires'], $plugin->name);
        }

        // Register Provider temporarily to run migrations
        // Note: In a real request, we might need to register it in the current lifecycle 
        // to run migrations if they are loaded by the provider's boot method.
        // However, usually migrations are in database/migrations and we can run them directly if we know the path.
        // But standard Laravel plugins load migrations in boot().
        // For now, let's assume we can just run the migrations if we know the path.
        
        $migrationPath = $this->pluginPath . '/' . $plugin->slug . '/database/migrations';
        if (File::exists($migrationPath)) {
            Artisan::call('migrate', [
                '--path' => "plugins/{$plugin->slug}/database/migrations",
                '--force' => true,
            ]);
        }

        // Sync Permissions
        if (isset($manifest['permissions'])) {
            $this->permissionRegistry->syncFromPlugin($plugin, $manifest['permissions']);
        }

        $plugin->update([
            'is_active' => true,
            'activated_at' => now(),
        ]);
        
        // Show permissions
        $this->permissionRegistry->showByPlugin($plugin->slug);
    }

    /**
     * Validate plugin dependencies.
     * 
     * @param array $requires The requires configuration from plugin.json
     * @param string $pluginName Plugin name for error messages
     * @throws Exception If any dependency is not met
     */
    protected function validateDependencies(array $requires, string $pluginName): void
    {
        // Check PHP version requirement
        if (isset($requires['php'])) {
            $requiredPhp = $requires['php'];
            $currentPhp = PHP_VERSION;
            
            if (!$this->versionSatisfies($currentPhp, $requiredPhp)) {
                throw new Exception(
                    "Plugin '{$pluginName}' requires PHP {$requiredPhp}, but current version is {$currentPhp}."
                );
            }
        }

        // Check CMS version requirement
        if (isset($requires['cms'])) {
            $requiredCms = $requires['cms'];
            $currentCms = config('cms.version', '1.0.0');
            
            if (!$this->versionSatisfies($currentCms, $requiredCms)) {
                throw new Exception(
                    "Plugin '{$pluginName}' requires CMS {$requiredCms}, but current version is {$currentCms}."
                );
            }
        }

        // Check plugin dependencies
        if (isset($requires['plugins']) && is_array($requires['plugins'])) {
            foreach ($requires['plugins'] as $requiredPlugin) {
                $dependency = Plugin::where('slug', $requiredPlugin)->where('is_active', true)->first();
                if (!$dependency) {
                    throw new Exception(
                        "Plugin '{$pluginName}' requires plugin '{$requiredPlugin}' to be active."
                    );
                }
            }
        }
    }

    /**
     * Check if a version satisfies a version constraint.
     * 
     * Supports: >=X.Y.Z, >X.Y.Z, <=X.Y.Z, <X.Y.Z, X.Y.Z (exact), ^X.Y.Z (compatible)
     */
    protected function versionSatisfies(string $current, string $constraint): bool
    {
        // Remove any leading 'v' from versions
        $current = ltrim($current, 'v');
        $constraint = trim($constraint);

        // Handle comparison operators
        if (preg_match('/^(>=|>|<=|<|\^)?(.+)$/', $constraint, $matches)) {
            $operator = $matches[1] ?: '>=';
            $version = ltrim($matches[2], 'v');

            switch ($operator) {
                case '>=':
                    return version_compare($current, $version, '>=');
                case '>':
                    return version_compare($current, $version, '>');
                case '<=':
                    return version_compare($current, $version, '<=');
                case '<':
                    return version_compare($current, $version, '<');
                case '^':
                    // ^X.Y.Z means >=X.Y.Z and <(X+1).0.0
                    $parts = explode('.', $version);
                    $major = (int) ($parts[0] ?? 0);
                    $nextMajor = ($major + 1) . '.0.0';
                    return version_compare($current, $version, '>=') 
                        && version_compare($current, $nextMajor, '<');
                default:
                    return version_compare($current, $version, '>=');
            }
        }

        // Exact match fallback
        return version_compare($current, $constraint, '>=');
    }

    /**
     * Deactivate a plugin.
     */
    public function deactivate(Plugin $plugin): void
    {
        if (!$plugin->is_active) {
            return;
        }

        $plugin->update(['is_active' => false]);
        
        // Hide permissions
        $this->permissionRegistry->hideByPlugin($plugin->slug);
    }

    /**
     * Uninstall a plugin.
     * 
     * @param Plugin $plugin The plugin to uninstall
     * @param bool $deleteData If true, delete all plugin data and permissions. If false, keep permissions (hidden).
     */
    public function uninstall(Plugin $plugin, bool $deleteData = false): void
    {
        // Deactivate first if active
        if ($plugin->is_active) {
            $this->deactivate($plugin);
        }

        // Rollback migrations if data is being deleted
        if ($deleteData) {
            $migrationPath = $this->pluginPath . '/' . $plugin->slug . '/database/migrations';
            if (File::exists($migrationPath)) {
                try {
                    Artisan::call('migrate:rollback', [
                        '--path' => "plugins/{$plugin->slug}/database/migrations",
                        '--force' => true,
                    ]);
                } catch (\Exception $e) {
                    // Log error but continue uninstall
                     \Illuminate\Support\Facades\Log::error("Failed to rollback migrations for plugin {$plugin->slug}: " . $e->getMessage());
                }
            }

            // Delete all plugin permissions
            $this->permissionRegistry->deleteByPlugin($plugin->slug, true);
        }
        // If not deleting data, permissions remain hidden (is_active = false)

        // Delete files
        // We delete files AFTER rollback because migration files are needed for rollback
        $path = $this->pluginPath . '/' . $plugin->slug;
        if (File::exists($path)) {
            File::deleteDirectory($path);
        }

        // Delete DB record
        $plugin->delete();
    }
}
