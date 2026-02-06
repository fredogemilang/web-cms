<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Plugin;
use Illuminate\Support\Collection;

class PermissionRegistry
{
    /**
     * Register permissions from an array.
     */
    public function register(array $permissions, string $source = 'core', ?string $pluginSlug = null): void
    {
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'module' => $permission['module'],
                    'action' => $permission['action'],
                    'description' => $permission['description'] ?? null,
                    'source' => $source,
                    'plugin_slug' => $pluginSlug,
                    'is_active' => true,
                    'icon' => $permission['icon'] ?? null,
                    'sort_order' => $permission['sort_order'] ?? 0,
                ]
            );
        }
    }

    /**
     * Sync permissions from a plugin's manifest.
     */
    public function syncFromPlugin(Plugin $plugin, array $permissionConfig): int
    {
        $count = 0;
        $source = "plugin:{$plugin->slug}";

        if (isset($permissionConfig['resources'])) {
            foreach ($permissionConfig['resources'] as $resource) {
                $module = $resource['module'];
                $icon = $resource['icon'] ?? null;
                $description = $resource['description'] ?? null;

                foreach ($resource['actions'] as $action) {
                    // Format: {module}.{action} - module name from plugin.json is already unique
                    $permissionName = "{$module}.{$action}";
                    
                    Permission::updateOrCreate(
                        ['name' => $permissionName],
                        [
                            'module' => $module,       // Use actual module name from plugin.json
                            'resource' => $module,     // Original resource name
                            'action' => $action,
                            'description' => $description,
                            'source' => $source,
                            'plugin_slug' => $plugin->slug,
                            'is_active' => true,
                            'icon' => $icon,
                        ]
                    );
                    $count++;
                }
            }
        }

        // Update plugin record
        $plugin->update([
            'permissions_registered' => true,
            'permission_count' => $count,
        ]);

        return $count;
    }

    /**
     * Hide all permissions for a plugin (when deactivated).
     */
    public function hideByPlugin(string $pluginSlug): int
    {
        return Permission::where('plugin_slug', $pluginSlug)
            ->update(['is_active' => false]);
    }

    /**
     * Show all permissions for a plugin (when reactivated).
     */
    public function showByPlugin(string $pluginSlug): int
    {
        return Permission::where('plugin_slug', $pluginSlug)
            ->update(['is_active' => true]);
    }

    /**
     * Delete all permissions for a plugin.
     */
    public function deleteByPlugin(string $pluginSlug, bool $force = false): int
    {
        $query = Permission::where('plugin_slug', $pluginSlug);
        
        if (!$force) {
            // Check if any roles have these permissions assigned
            $permissionIds = $query->pluck('id');
            // Note: We still delete, but this could be extended to warn the user
        }

        return $query->delete();
    }

    /**
     * Get all active permissions grouped by source.
     */
    public function getGroupedBySource(): array
    {
        $permissions = Permission::active()
            ->orderBy('sort_order')
            ->orderBy('module')
            ->get();

        $corePermissions = $permissions->where('source', 'core');
        $pluginPermissions = $permissions->filter(fn($p) => str_starts_with($p->source, 'plugin:'));

        return [
            'core' => [
                'modules' => $corePermissions->groupBy('module'),
                'count' => $corePermissions->count(),
            ],
            'plugins' => $pluginPermissions
                ->groupBy(fn($p) => str_replace('plugin:', '', $p->source))
                ->map(fn($group) => [
                    'modules' => $group->groupBy('module'),
                    'count' => $group->count(),
                ])
                ->toArray(),
        ];
    }

    /**
     * Get all permissions for a specific role, grouped for matrix display.
     */
    public function getMatrixForRole($roleId = null): Collection
    {
        return Permission::active()
            ->orderBy('sort_order')
            ->orderBy('module')
            ->get()
            ->groupBy('source');
    }

    /**
     * Get unique actions from all permissions (for matrix columns).
     */
    public function getUniqueActions(): array
    {
        return Permission::active()
            ->select('action')
            ->distinct()
            ->pluck('action')
            ->toArray();
    }
}
