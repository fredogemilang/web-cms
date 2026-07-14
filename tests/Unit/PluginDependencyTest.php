<?php

namespace Tests\Unit;

use App\Exceptions\PluginDependencyException;
use App\Models\Plugin;
use App\Services\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PluginDependencyTest extends TestCase
{
    use RefreshDatabase;

    protected PluginManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(PluginManager::class);

        // Ensure plugins table exists (RefreshDatabase handles migrations)
        $this->artisan('migrate');
    }

    #[Test]
    public function deactivation_blocked_when_dependents_exist(): void
    {
        // Plugin B is depended upon
        $pluginB = Plugin::create([
            'name' => 'Plugin B',
            'slug' => 'plugin-b',
            'version' => '1.0.0',
            'provider' => 'Plugins\PluginB\Providers\PluginBServiceProvider',
            'is_active' => true,
        ]);

        // Plugin A depends on Plugin B
        $pluginA = Plugin::create([
            'name' => 'Plugin A',
            'slug' => 'plugin-a',
            'version' => '1.0.0',
            'provider' => 'Plugins\PluginA\Providers\PluginAServiceProvider',
            'is_active' => true,
        ]);

        // Plugin A's plugin.json declares dependency on plugin-b
        $this->createPluginJson('plugin-a', [
            'requires' => ['plugins' => ['plugin-b']],
        ]);
        $this->createPluginJson('plugin-b', []);

        $this->expectException(PluginDependencyException::class);
        $this->expectExceptionMessage("Cannot deactivate 'Plugin B'");

        $this->manager->deactivate($pluginB);
    }

    #[Test]
    public function deactivation_succeeds_when_no_dependents(): void
    {
        $plugin = Plugin::create([
            'name' => 'Standalone',
            'slug' => 'standalone',
            'version' => '1.0.0',
            'provider' => 'Plugins\Standalone\Providers\StandaloneServiceProvider',
            'is_active' => true,
        ]);

        $this->createPluginJson('standalone', []);

        // Should not throw
        $this->manager->deactivate($plugin);

        $this->assertFalse($plugin->fresh()->is_active);
    }

    #[Test]
    public function get_dependent_plugins_returns_dependents(): void
    {
        $pluginB = Plugin::create([
            'name' => 'Plugin B',
            'slug' => 'plugin-b',
            'version' => '1.0.0',
            'provider' => 'Plugins\PluginB\Providers\PluginBServiceProvider',
            'is_active' => true,
        ]);

        Plugin::create([
            'name' => 'Plugin A',
            'slug' => 'plugin-a',
            'version' => '1.0.0',
            'provider' => 'Plugins\PluginA\Providers\PluginAServiceProvider',
            'is_active' => true,
        ]);

        $this->createPluginJson('plugin-a', [
            'requires' => ['plugins' => ['plugin-b']],
        ]);

        $dependents = $this->manager->getDependentPlugins($pluginB);

        $this->assertCount(1, $dependents);
        $this->assertEquals('plugin-a', $dependents[0]['slug']);
    }

    #[Test]
    public function inactive_plugins_not_counted_as_dependents(): void
    {
        $pluginB = Plugin::create([
            'name' => 'Plugin B',
            'slug' => 'plugin-b',
            'version' => '1.0.0',
            'provider' => 'Plugins\PluginB\Providers\PluginBServiceProvider',
            'is_active' => true,
        ]);

        // Plugin A exists but is INACTIVE — should not block deactivation of B
        Plugin::create([
            'name' => 'Plugin A',
            'slug' => 'plugin-a',
            'version' => '1.0.0',
            'provider' => 'Plugins\PluginA\Providers\PluginAServiceProvider',
            'is_active' => false,
        ]);

        $this->createPluginJson('plugin-a', [
            'requires' => ['plugins' => ['plugin-b']],
        ]);

        $dependents = $this->manager->getDependentPlugins($pluginB);
        $this->assertCount(0, $dependents);

        // Deactivation should succeed since the dependent plugin is inactive
        $this->manager->deactivate($pluginB);
        $this->assertFalse($pluginB->fresh()->is_active);
    }

    protected function createPluginJson(string $slug, array $extra = []): void
    {
        $path = base_path("plugins/{$slug}");
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $manifest = array_merge([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'version' => '1.0.0',
            'provider' => 'Plugins\\'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))).'\\Providers\\'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))).'ServiceProvider',
        ], $extra);

        file_put_contents("{$path}/plugin.json", json_encode($manifest, JSON_PRETTY_PRINT));
    }

    protected function tearDown(): void
    {
        // Clean up test plugin directories
        foreach (['plugin-a', 'plugin-b', 'standalone'] as $slug) {
            $path = base_path("plugins/{$slug}");
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            }
        }
        parent::tearDown();
    }

    protected function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
