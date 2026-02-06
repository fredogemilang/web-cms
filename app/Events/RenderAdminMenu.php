<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatched when rendering the admin sidebar menu.
 * Plugins can listen to this event to inject their menu items.
 */
class RenderAdminMenu
{
    use Dispatchable;

    /**
     * Menu items to be rendered.
     * 
     * @var array
     */
    public array $menuItems = [];

    /**
     * Add a menu item to the sidebar.
     * 
     * @param array $item Menu item with keys: title, route, icon, permission, children, badge
     * @param string|null $after Insert after this menu item's route name (null = append to end)
     */
    public function addMenuItem(array $item, ?string $after = null): self
    {
        if ($after !== null) {
            // Find position and insert after
            $position = array_search($after, array_column($this->menuItems, 'route'));
            if ($position !== false) {
                array_splice($this->menuItems, $position + 1, 0, [$item]);
                return $this;
            }
        }
        
        $this->menuItems[] = $item;
        return $this;
    }

    /**
     * Add multiple menu items.
     */
    public function addMenuItems(array $items): self
    {
        foreach ($items as $item) {
            $this->addMenuItem($item);
        }
        return $this;
    }

    /**
     * Get all menu items.
     */
    public function getMenuItems(): array
    {
        return $this->menuItems;
    }
}
