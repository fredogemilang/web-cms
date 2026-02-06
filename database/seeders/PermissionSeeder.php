<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'module' => 'dashboard', 'action' => 'view', 'description' => 'View dashboard', 'source' => 'core', 'icon' => 'dashboard', 'sort_order' => 1],

            // Users
            ['name' => 'users.view', 'module' => 'users', 'action' => 'view', 'description' => 'View users list', 'source' => 'core', 'icon' => 'group', 'sort_order' => 10],
            ['name' => 'users.create', 'module' => 'users', 'action' => 'create', 'description' => 'Create new user', 'source' => 'core', 'icon' => 'group', 'sort_order' => 10],
            ['name' => 'users.edit', 'module' => 'users', 'action' => 'edit', 'description' => 'Edit user', 'source' => 'core', 'icon' => 'group', 'sort_order' => 10],
            ['name' => 'users.delete', 'module' => 'users', 'action' => 'delete', 'description' => 'Delete user', 'source' => 'core', 'icon' => 'group', 'sort_order' => 10],

            // Roles
            ['name' => 'roles.view', 'module' => 'roles', 'action' => 'view', 'description' => 'View roles list', 'source' => 'core', 'icon' => 'shield', 'sort_order' => 20],
            ['name' => 'roles.create', 'module' => 'roles', 'action' => 'create', 'description' => 'Create new role', 'source' => 'core', 'icon' => 'shield', 'sort_order' => 20],
            ['name' => 'roles.edit', 'module' => 'roles', 'action' => 'edit', 'description' => 'Edit role', 'source' => 'core', 'icon' => 'shield', 'sort_order' => 20],
            ['name' => 'roles.delete', 'module' => 'roles', 'action' => 'delete', 'description' => 'Delete role', 'source' => 'core', 'icon' => 'shield', 'sort_order' => 20],
            ['name' => 'roles.assign-permissions', 'module' => 'roles', 'action' => 'assign-permissions', 'description' => 'Assign permissions to role', 'source' => 'core', 'icon' => 'shield', 'sort_order' => 20],

            // Menus
            ['name' => 'menus.view', 'module' => 'menus', 'action' => 'view', 'description' => 'View menu items', 'source' => 'core', 'icon' => 'menu', 'sort_order' => 30],
            ['name' => 'menus.create', 'module' => 'menus', 'action' => 'create', 'description' => 'Create new menu item', 'source' => 'core', 'icon' => 'menu', 'sort_order' => 30],
            ['name' => 'menus.edit', 'module' => 'menus', 'action' => 'edit', 'description' => 'Edit menu item', 'source' => 'core', 'icon' => 'menu', 'sort_order' => 30],
            ['name' => 'menus.delete', 'module' => 'menus', 'action' => 'delete', 'description' => 'Delete menu item', 'source' => 'core', 'icon' => 'menu', 'sort_order' => 30],

            // Plugins
            ['name' => 'plugins.view', 'module' => 'plugins', 'action' => 'view', 'description' => 'View plugins list', 'source' => 'core', 'icon' => 'extension', 'sort_order' => 40],
            ['name' => 'plugins.install', 'module' => 'plugins', 'action' => 'install', 'description' => 'Install new plugin', 'source' => 'core', 'icon' => 'extension', 'sort_order' => 40],
            ['name' => 'plugins.activate', 'module' => 'plugins', 'action' => 'activate', 'description' => 'Activate plugin', 'source' => 'core', 'icon' => 'extension', 'sort_order' => 40],
            ['name' => 'plugins.deactivate', 'module' => 'plugins', 'action' => 'deactivate', 'description' => 'Deactivate plugin', 'source' => 'core', 'icon' => 'extension', 'sort_order' => 40],
            ['name' => 'plugins.delete', 'module' => 'plugins', 'action' => 'delete', 'description' => 'Delete plugin', 'source' => 'core', 'icon' => 'extension', 'sort_order' => 40],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
