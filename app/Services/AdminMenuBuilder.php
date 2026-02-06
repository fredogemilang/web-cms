<?php

namespace App\Services;

use App\Events\RenderAdminMenu;
use App\Models\Plugin;

/**
 * Service for building the admin sidebar menu.
 * Dispatches RenderAdminMenu event to collect menu items from plugins.
 */
class AdminMenuBuilder
{
    /**
     * Build the complete admin menu.
     * 
     * @return array Menu items including core and plugin items
     */
    public function build(): array
    {
        // Start with core menu items from database
        $coreItems = $this->getCoreMenuItems();
        
        // Create event with core items
        $event = new RenderAdminMenu();
        $event->addMenuItems($coreItems);
        
        // Dispatch event to allow plugins to add their items
        event($event);
        
        // Filter items based on user permissions
        return $this->filterByPermissions($event->getMenuItems());
    }

    /**
     * Get core menu items from the menu_items table.
     */
    protected function getCoreMenuItems(): array
    {
        $menuItems = \App\Models\MenuItem::whereNull('parent_id')
            ->orderBy('order')
            ->with('children')
            ->get();

        return $this->formatMenuItems($menuItems);
    }

    /**
     * Format menu items from Eloquent models to array format.
     */
    protected function formatMenuItems($items): array
    {
        return $items->map(function ($item) {
            return [
                'title' => $item->title,
                'route' => $item->route,
                'url' => $item->url,
                'icon' => $item->icon,
                'permission' => $item->permission,
                'is_active' => $item->is_active,
                'source' => 'core',
                'children' => $item->children->isNotEmpty() 
                    ? $this->formatMenuItems($item->children) 
                    : [],
            ];
        })->toArray();
    }

    /**
     * Filter menu items based on current user's permissions.
     */
    protected function filterByPermissions(array $items): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [];
        }

        // Super admin sees everything
        if ($user->isSuperAdmin()) {
            return array_filter($items, fn($item) => $item['is_active'] ?? true);
        }

        return array_filter($items, function ($item) use ($user) {
            // Skip inactive items
            if (!($item['is_active'] ?? true)) {
                return false;
            }

            // No permission required
            if (empty($item['permission'])) {
                return true;
            }

            // Check permission
            return $user->hasPermission($item['permission']);
        });
    }

    /**
     * Get menu items for a specific plugin.
     */
    public function getPluginMenuItems(string $pluginSlug): array
    {
        return collect($this->build())
            ->filter(fn($item) => ($item['source'] ?? 'core') === "plugin:{$pluginSlug}")
            ->values()
            ->toArray();
    }
}
