<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'title' => 'Dashboard',
                'icon' => 'home',
                'route' => 'admin.dashboard',
                'permission' => 'dashboard.view',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Pages',
                'icon' => 'description',
                'route' => null,
                'permission' => 'pages.view',
                'order' => 2,
                'is_active' => true,
                'children' => [
                    [
                        'title' => 'All Pages',
                        'icon' => 'article',
                        'route' => 'admin.pages.index',
                        'permission' => 'pages.view',
                        'order' => 1,
                        'is_active' => true,
                    ],
                    [
                        'title' => 'Add New',
                        'icon' => 'add',
                        'route' => 'admin.pages.create',
                        'permission' => 'pages.create',
                        'order' => 2,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'title' => 'User Management',
                'icon' => 'users',
                'route' => null,
                'permission' => null,
                'order' => 3,
                'is_active' => true,
                'children' => [
                    [
                        'title' => 'Users',
                        'icon' => 'user',
                        'route' => 'admin.users.index',
                        'permission' => 'users.view',
                        'order' => 1,
                        'is_active' => true,
                    ],
                    [
                        'title' => 'Roles',
                        'icon' => 'shield',
                        'route' => 'admin.roles.index',
                        'permission' => 'roles.view',
                        'order' => 2,
                        'is_active' => true,
                    ],
                    [
                        'title' => 'Permissions',
                        'icon' => 'lock',
                        'route' => 'admin.permissions.index',
                        'permission' => 'permissions.view',
                        'order' => 3,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'title' => 'Menu Management',
                'icon' => 'menu',
                'route' => 'admin.menus.index',
                'permission' => 'menus.view',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Appearance',
                'icon' => 'palette',
                'route' => null,
                'permission' => null,
                'order' => 5,
                'is_active' => true,
                'children' => [
                    [
                        'title' => 'Themes',
                        'icon' => 'palette',
                        'route' => 'admin.themes.index',
                        'permission' => 'themes.view',
                        'order' => 1,
                        'is_active' => true,
                    ],
                ],
            ],
        ];

        foreach ($menus as $menuData) {
            $children = $menuData['children'] ?? [];
            unset($menuData['children']);

            $menu = MenuItem::create($menuData);

            if (!empty($children)) {
                foreach ($children as $childData) {
                    $childData['parent_id'] = $menu->id;
                    MenuItem::create($childData);
                }
            }
        }
    }
}
