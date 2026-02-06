<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PagesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'pages.view',
                'module' => 'pages',
                'action' => 'view',
                'description' => 'View pages list',
                'source' => 'core',
            ],
            [
                'name' => 'pages.create',
                'module' => 'pages',
                'action' => 'create',
                'description' => 'Create new pages',
                'source' => 'core',
            ],
            [
                'name' => 'pages.edit',
                'module' => 'pages',
                'action' => 'edit',
                'description' => 'Edit existing pages',
                'source' => 'core',
            ],
            [
                'name' => 'pages.delete',
                'module' => 'pages',
                'action' => 'delete',
                'description' => 'Delete pages',
                'source' => 'core',
            ],
            [
                'name' => 'pages.publish',
                'module' => 'pages',
                'action' => 'publish',
                'description' => 'Publish pages',
                'source' => 'core',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign all pages permissions to Administrator role
        $adminRole = Role::where('name', 'Administrator')->first();
        if ($adminRole) {
            $pagePermissions = Permission::where('module', 'pages')->get();
            $adminRole->permissions()->syncWithoutDetaching($pagePermissions->pluck('id'));
        }

        $this->command->info('Pages permissions created successfully!');
    }
}
