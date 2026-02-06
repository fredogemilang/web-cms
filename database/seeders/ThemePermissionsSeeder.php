<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class ThemePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'themes.view',
                'module' => 'themes',
                'action' => 'view',
                'description' => 'View installed themes',
                'source' => 'core',
            ],
            [
                'name' => 'themes.manage',
                'module' => 'themes',
                'action' => 'manage',
                'description' => 'Install, activate, and delete themes',
                'source' => 'core',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Theme permissions created successfully!');
    }
}
