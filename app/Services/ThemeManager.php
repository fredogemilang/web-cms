<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use ZipArchive;
use Exception;

class ThemeManager
{
    protected string $themePath;

    public function __construct()
    {
        $this->themePath = base_path('themes');

        if (!File::exists($this->themePath)) {
            File::makeDirectory($this->themePath, 0755, true);
        }
    }

    /**
     * Install a theme from a zip file.
     */
    public function install(string $zipPath): Theme
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception("The PHP Zip extension is not enabled. Please enable it in your php.ini configuration.");
        }

        // Validate file size (10MB max)
        if (filesize($zipPath) > 10 * 1024 * 1024) {
            throw new Exception("Theme file is too large. Maximum size is 10MB.");
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new Exception("Failed to open zip file.");
        }

        // Validate zip structure and scan for malicious code
        $this->validateThemeZip($zip);

        // Extract to temporary location to read manifest
        $tempPath = storage_path('app/temp/themes/' . uniqid());
        $zip->extractTo($tempPath);
        $zip->close();

        // Find theme.json
        $files = File::allFiles($tempPath);
        $manifestFile = null;
        foreach ($files as $file) {
            if ($file->getFilename() === 'theme.json') {
                $manifestFile = $file;
                break;
            }
        }

        if (!$manifestFile) {
            File::deleteDirectory($tempPath);
            throw new Exception("theme.json not found in the zip file.");
        }

        $manifest = json_decode(file_get_contents($manifestFile->getPathname()), true);
        if (!$manifest) {
            File::deleteDirectory($tempPath);
            throw new Exception("Invalid theme.json manifest.");
        }

        // Validate manifest
        $this->validateManifest($manifest);

        $slug = $manifest['slug'];
        $targetPath = $this->themePath . '/' . $slug;

        if (File::exists($targetPath)) {
            File::deleteDirectory($tempPath);
            throw new Exception("Theme with slug '{$slug}' already exists.");
        }

        // Move from temp to target
        $sourceDir = $manifestFile->getPath();
        File::moveDirectory($sourceDir, $targetPath);
        File::deleteDirectory($tempPath);

        // Create DB record
        return Theme::create([
            'name' => $manifest['name'],
            'slug' => $slug,
            'version' => $manifest['version'] ?? '1.0.0',
            'description' => $manifest['description'] ?? null,
            'author' => $manifest['author'] ?? null,
            'author_url' => $manifest['author_url'] ?? null,
            'screenshot' => $manifest['screenshot'] ?? null,
            'supports' => $manifest['supports'] ?? [],
            'installed_at' => now(),
        ]);
    }

    /**
     * Validate theme ZIP file.
     */
    protected function validateThemeZip(ZipArchive $zip): void
    {
        // Check for malicious code patterns
        $dangerousPatterns = [
            'eval(',
            'exec(',
            'shell_exec',
            'system(',
            'passthru(',
            'proc_open',
            'popen(',
            // '`', // Allow backticks for JS template literals
            'base64_decode(',
        ];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $content = $zip->getFromIndex($i);

            // Only check text files (PHP, JS, JSON)
            if (!preg_match('/\.(php|js|json)$/i', $filename)) {
                continue;
            }

            foreach ($dangerousPatterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    throw new Exception("Potentially dangerous code detected in {$filename}. Theme rejected for security reasons.");
                }
            }
        }
    }

    /**
     * Validate theme manifest.
     */
    protected function validateManifest(array $manifest): void
    {
        $required = ['name', 'slug', 'version'];

        foreach ($required as $field) {
            if (!isset($manifest[$field])) {
                throw new Exception("Missing required field in theme.json: {$field}");
            }
        }

        // Validate slug format (alphanumeric and dashes only)
        if (!preg_match('/^[a-z0-9-]+$/', $manifest['slug'])) {
            throw new Exception("Theme slug must contain only lowercase letters, numbers, and dashes.");
        }

        // Validate name length
        if (strlen($manifest['name']) < 1 || strlen($manifest['name']) > 100) {
            throw new Exception("Theme name must be between 1 and 100 characters.");
        }

        // Validate version format (simple semver check)
        if (!preg_match('/^\d+\.\d+\.\d+$/', $manifest['version'])) {
            throw new Exception("Theme version must follow semantic versioning (e.g., 1.0.0).");
        }
    }

    /**
     * Activate a theme.
     */
    public function activate(Theme|string $theme): void
    {
        if (is_string($theme)) {
            $theme = Theme::where('slug', $theme)->firstOrFail();
        }

        if ($theme->is_active) {
            return;
        }

        $manifestPath = $this->themePath . '/' . $theme->slug . '/theme.json';
        if (!File::exists($manifestPath)) {
            throw new Exception("Theme files not found.");
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        // Validate dependencies
        if (isset($manifest['requires'])) {
            $this->validateDependencies($manifest['requires'], $theme->name);
        }

        // Deactivate all other themes
        Theme::where('is_active', true)->update(['is_active' => false]);

        // Activate this theme
        $theme->update([
            'is_active' => true,
            'activated_at' => now(),
        ]);

        // Publish assets
        $this->publishAssets($theme);

        // Clear caches
        $this->clearCaches();
    }

    public function publishAssets(Theme $theme): void
    {
        $sourcePath = $this->themePath . '/' . $theme->slug . '/assets';
        $destPath = public_path('themes/' . $theme->slug . '/assets');

        if (File::exists($sourcePath)) {
            // Create destination directory if it doesn't exist
            if (!File::exists(dirname($destPath))) {
                File::makeDirectory(dirname($destPath), 0755, true);
            }

            // Copy directory
            // We use copyDirectory instead of symlink for better portability across environments
            if (File::copyDirectory($sourcePath, $destPath)) {
                // Delete source directory after successful copy as requested
                File::deleteDirectory($sourcePath);
            }
        }

        // Handle screenshot (Move to public for Admin Panel)
        // Read theme.json to get the correct screenshot filename
        $manifestPath = $this->themePath . '/' . $theme->slug . '/theme.json';
        
        if (File::exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $screenshotFile = $manifest['screenshot'] ?? null;

            if ($screenshotFile) {
                $sourceScreenshot = $this->themePath . '/' . $theme->slug . '/' . $screenshotFile;
                $destScreenshot = public_path('themes/' . $theme->slug . '/' . $screenshotFile);

                if (File::exists($sourceScreenshot)) {
                    // Create destination directory if it doesn't exist
                    if (!File::exists(dirname($destScreenshot))) {
                        File::makeDirectory(dirname($destScreenshot), 0755, true);
                    }

                    // Copy and delete original as requested
                    if (File::copy($sourceScreenshot, $destScreenshot)) {
                        File::delete($sourceScreenshot);
                    }
                }
            }
        }
    }

    /**
     * Validate theme dependencies.
     */
    protected function validateDependencies(array $requires, string $themeName): void
    {
        // Check PHP version requirement
        if (isset($requires['php'])) {
            $requiredPhp = $requires['php'];
            $currentPhp = PHP_VERSION;

            if (!$this->versionSatisfies($currentPhp, $requiredPhp)) {
                throw new Exception(
                    "Theme '{$themeName}' requires PHP {$requiredPhp}, but current version is {$currentPhp}."
                );
            }
        }

        // Check CMS version requirement
        if (isset($requires['cms'])) {
            $requiredCms = $requires['cms'];
            $currentCms = config('cms.version', '1.0.0');

            if (!$this->versionSatisfies($currentCms, $requiredCms)) {
                throw new Exception(
                    "Theme '{$themeName}' requires CMS {$requiredCms}, but current version is {$currentCms}."
                );
            }
        }
    }

    /**
     * Check if a version satisfies a version constraint.
     */
    protected function versionSatisfies(string $current, string $constraint): bool
    {
        $current = ltrim($current, 'v');
        $constraint = trim($constraint);

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
                    $parts = explode('.', $version);
                    $major = (int) ($parts[0] ?? 0);
                    $nextMajor = ($major + 1) . '.0.0';
                    return version_compare($current, $version, '>=')
                        && version_compare($current, $nextMajor, '<');
                default:
                    return version_compare($current, $version, '>=');
            }
        }

        return version_compare($current, $constraint, '>=');
    }

    /**
     * Delete a theme.
     */
    public function delete(Theme|string $theme): void
    {
        if (is_string($theme)) {
            $theme = Theme::where('slug', $theme)->firstOrFail();
        }

        // Prevent deleting active theme
        if ($theme->is_active) {
            throw new Exception("Cannot delete the active theme. Please activate a different theme first.");
        }

        // Delete files
        $path = $this->themePath . '/' . $theme->slug;
        if (File::exists($path)) {
            File::deleteDirectory($path);
        }

        // Delete public assets
        $publicAssetsPath = public_path('themes/' . $theme->slug);
        if (File::exists($publicAssetsPath)) {
            File::deleteDirectory($publicAssetsPath);
        }

        // Delete DB record
        $theme->delete();

        // Clear theme cache
        Cache::forget('theme.' . $theme->slug);
    }

    /**
     * Get the currently active theme.
     */
    public function getActive(): ?Theme
    {
        return Cache::remember('theme.active', now()->addDay(), function () {
            return Theme::active()->first();
        });
    }

    /**
     * Discover themes from filesystem.
     */
    public function discover(): array
    {
        $discovered = [];
        $directories = File::directories($this->themePath);

        foreach ($directories as $directory) {
            $manifestPath = $directory . '/theme.json';
            if (File::exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if ($manifest && isset($manifest['slug'])) {
                    $discovered[] = [
                        'path' => $directory,
                        'manifest' => $manifest,
                    ];
                }
            }
        }

        return $discovered;
    }

    /**
     * Clear all theme-related caches.
     */
    protected function clearCaches(): void
    {
        // Clear view cache
        Artisan::call('view:clear');

        // Clear route cache
        Artisan::call('route:clear');

        // Clear application cache
        Cache::flush();
    }
}
